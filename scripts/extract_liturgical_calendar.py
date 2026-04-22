#!/usr/bin/env python3
"""
Extract liturgical calendar data from USCCB PDF calendars.

Usage:
    python scripts/extract_liturgical_calendar.py docs/2026cal.pdf 2026
    python scripts/extract_liturgical_calendar.py docs/2027cal.pdf 2027

Outputs JSON to laravel/database/seeders/data/liturgical_{year}.json
"""

import sys
import os
import re
import json
import fitz  # PyMuPDF

# ── Color mapping ──────────────────────────────────────────────────────
COLOR_HEX = {
    'violet': '#6f42c1',
    'white':  '#f8f9fa',
    'red':    '#dc3545',
    'green':  '#198754',
    'rose':   '#e83e8c',
}

# ── LOTH Volume ranges ────────────────────────────────────────────────
LOTH_VOLUMES = {
    2026: [
        {'start': '2025-11-30', 'end': '2026-01-11', 'season': 'advent_christmas', 'loth_volume': 'I'},
        {'start': '2026-01-12', 'end': '2026-02-17', 'season': 'ordinary_time',    'loth_volume': 'III'},
        {'start': '2026-02-18', 'end': '2026-05-24', 'season': 'lent_easter',      'loth_volume': 'II'},
        {'start': '2026-05-25', 'end': '2026-08-01', 'season': 'ordinary_time',    'loth_volume': 'III'},
        {'start': '2026-08-02', 'end': '2026-11-28', 'season': 'ordinary_time',    'loth_volume': 'IV'},
        {'start': '2026-11-29', 'end': '2027-01-10', 'season': 'advent_christmas', 'loth_volume': 'I'},
    ],
    2027: [
        {'start': '2026-11-29', 'end': '2027-01-10', 'season': 'advent_christmas', 'loth_volume': 'I'},
        {'start': '2027-01-11', 'end': '2027-02-09', 'season': 'ordinary_time',    'loth_volume': 'III'},
        {'start': '2027-02-10', 'end': '2027-05-16', 'season': 'lent_easter',      'loth_volume': 'II'},
        {'start': '2027-05-17', 'end': '2027-07-31', 'season': 'ordinary_time',    'loth_volume': 'III'},
        {'start': '2027-08-01', 'end': '2027-11-27', 'season': 'ordinary_time',    'loth_volume': 'IV'},
        {'start': '2027-11-28', 'end': '2028-01-09', 'season': 'advent_christmas', 'loth_volume': 'I'},
    ],
}

DOW_ABBREVS = ['SUN', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
DOW_PATTERN = re.compile(r'^(SUN|Mon|Tue|Wed|Thu|Fri|Sat)\b', re.IGNORECASE)

MONTH_NAMES = {
    'JANUARY': 1, 'FEBRUARY': 2, 'MARCH': 3, 'APRIL': 4,
    'MAY': 5, 'JUNE': 6, 'JULY': 7, 'AUGUST': 8,
    'SEPTEMBER': 9, 'OCTOBER': 10, 'NOVEMBER': 11, 'DECEMBER': 12
}

COLOR_PATTERN = re.compile(
    r'^(violet|white|red|green|rose)'
    r'(\s*/\s*(violet|white|red|green|rose))?'
    r'(\s+or\s+(violet|white|red|green|rose))?\s*$',
    re.IGNORECASE
)


def get_season_for_date(date_str, year):
    """Determine the liturgical season for a given date."""
    from datetime import date as D
    d = D.fromisoformat(date_str)

    if year == 2026:
        advent_1 = D(2025, 11, 30); xmas = D(2025, 12, 25); baptism = D(2026, 1, 11)
        ash = D(2026, 2, 18); hthu = D(2026, 4, 2); easter = D(2026, 4, 5)
        pente = D(2026, 5, 24); advent_2 = D(2026, 11, 29)
    elif year == 2027:
        advent_1 = D(2026, 11, 29); xmas = D(2026, 12, 25); baptism = D(2027, 1, 10)
        ash = D(2027, 2, 10); hthu = D(2027, 3, 25); easter = D(2027, 3, 28)
        pente = D(2027, 5, 16); advent_2 = D(2027, 11, 28)
    else:
        return 'ordinary_time'

    if advent_1 <= d < xmas:          return 'advent'
    if xmas <= d <= baptism:          return 'christmas'
    if ash <= d < hthu:               return 'lent'
    if hthu <= d <= easter:           return 'triduum'
    if easter < d <= pente:           return 'easter'
    if d >= advent_2:                 return 'advent'
    return 'ordinary_time'


def get_loth_volume(date_str, year):
    from datetime import date as D
    d = D.fromisoformat(date_str)
    for r in LOTH_VOLUMES.get(year, []):
        if D.fromisoformat(r['start']) <= d <= D.fromisoformat(r['end']):
            return r['loth_volume']
    return None


def normalize_color(color_str):
    """Return (primary_color, hex) from a color string like 'violet/white'."""
    c = color_str.strip().lower()
    if 'rose' in c:
        return 'rose', COLOR_HEX['rose']
    primary = c.split('/')[0].strip()
    if primary in COLOR_HEX:
        return primary, COLOR_HEX[primary]
    return 'green', COLOR_HEX['green']


def parse_readings(text):
    """Extract citations and lectionary numbers."""
    citations = []
    lectionary = []
    for m in re.findall(r'\((\d+[A-Z]?)\)', text):
        try: lectionary.append(int(re.sub(r'[A-Z]', '', m)))
        except: pass
    clean = re.sub(r'\s*Pss\s+(?:I+V?|Prop)\s*\d*', '', text)
    clean = re.sub(r'\(\d+[A-Z]?\)', '', clean).strip()
    if clean:
        parts = [p.strip() for p in re.split(r'(?<!\d)\s*/\s*(?!\d)', clean) if p.strip()]
        citations = [p for p in parts if len(p) > 2]
    return {
        'citations': citations or None,
        'lectionary_numbers': lectionary or None,
    }


def extract_calendar(pdf_path, liturgical_year):
    """Main extraction: returns list of event dicts."""
    doc = fitz.open(pdf_path)
    all_text = ''
    for page in doc:
        all_text += page.get_text() + '\n'

    lines = [l.strip() for l in all_text.split('\n')]

    # --- Phase 1: find the calendar section ---
    # Look for "YEAR A/B ... WEEKDAYS" which is the actual calendar header
    # (not the preamble "YEAR A" / "YEAR B" in the cycles section)
    start_idx = None
    stop_idx = len(lines)  # default: parse to end
    for i, ln in enumerate(lines):
        if re.search(r'YEAR\s+[A-C].*WEEKDAYS', ln):
            if start_idx is None:
                start_idx = i
            else:
                # Second YEAR+WEEKDAYS header = start of next year's data
                stop_idx = i
                break
    if start_idx is None:
        print("ERROR: could not find 'YEAR X ... WEEKDAYS' header")
        return []

    # Find actual month-header that starts the daily entries
    cal_start = None
    for i in range(start_idx, stop_idx):
        ln = lines[i]
        # Match headers like "NOVEMBER...DECEMBER 2025" or "JANUARY 2026"
        if re.search(r'\d{4}', ln):
            for m in MONTH_NAMES:
                if m in ln:
                    cal_start = i
                    break
        if cal_start is not None:
            break
    if cal_start is None:
        print("ERROR: could not find month header after YEAR header")
        return []

    # --- Phase 2: parse entries ---
    events = []
    current_month = None
    current_year = None
    prev_day = 0

    i = cal_start
    while i < stop_idx:
        ln = lines[i]

        # Skip empty / page-number-only lines
        if not ln:
            i += 1; continue
        # Page numbers: bare numbers > 40 that appear alone
        if ln.isdigit() and int(ln) > 40:
            i += 1; continue

        # Stop at Spanish section or end markers
        if any(marker in ln for marker in ['CALENDARIO PROPIO', 'FIESTAS PATRONALES',
                                            'Enero', 'Febrero', 'Marzo', 'Abril',
                                            'Junio', 'Julio', 'Agosto']):
            break

        # --- Detect month-year header ---
        # Match "JANUARY 2026" or "NOVEMBER...DECEMBER 2025" (with any separator)
        yr_match = re.search(r'(\d{4})', ln)
        if yr_match:
            for mname in MONTH_NAMES:
                if mname in ln:
                    current_month = MONTH_NAMES[mname]
                    current_year = int(yr_match.group(1))
                    prev_day = 0
                    # For combined headers like "NOVEMBER-DECEMBER",
                    # pick the first month; rollover handles the rest
                    # Check if there's a second month AFTER the first
                    idx_m = ln.index(mname)
                    rest = ln[idx_m + len(mname):]
                    for mname2 in MONTH_NAMES:
                        if mname2 in rest and mname2 != mname:
                            # Use the first month, not the second
                            break
                    break
            i += 1; continue

        # Standalone month header like "JUNE" (rare but possible)
        if ln in MONTH_NAMES:
            current_month = MONTH_NAMES[ln]
            i += 1; continue

        # Skip footnotes (lines starting with digits followed by text like "3 Citations...")
        if re.match(r'^\d+\s+[A-Z][a-z]', ln) and int(ln.split()[0]) > 31:
            i += 1; continue

        # --- Detect a day entry: a bare number 1-31 ---
        if ln.isdigit() and 1 <= int(ln) <= 31 and current_month is not None:
            day = int(ln)

            # Handle month rollover
            if day < prev_day - 20:
                current_month += 1
                if current_month > 12:
                    current_month = 1
                    current_year += 1
            prev_day = day

            # Gather all lines belonging to this entry
            entry_lines = []
            i += 1
            while i < len(lines):
                nxt = lines[i]
                # Next day entry?
                if nxt.isdigit() and 1 <= int(nxt) <= 31:
                    break
                # Month header?
                if re.match(r'[A-Z]+\s+\d{4}$', nxt) or nxt in MONTH_NAMES:
                    break
                if re.match(r'[A-Z]+\s*[^A-Z0-9]*[A-Z]+\s+\d{4}', nxt):
                    break
                # Page number?
                if nxt.isdigit() and int(nxt) > 40:
                    i += 1; continue
                # End markers
                if any(m in nxt for m in ['CALENDARIO PROPIO', 'FIESTAS PATRONALES']):
                    break
                # Footnotes  
                if re.match(r'^\d+\s+[A-Z][a-z]', nxt) and len(nxt) > 60:
                    i += 1; continue
                if nxt:
                    entry_lines.append(nxt)
                i += 1

            if entry_lines:
                evt = build_event(day, current_month, current_year,
                                  entry_lines, liturgical_year)
                if evt:
                    events.append(evt)
            continue

        i += 1

    return events


def build_event(day, month, cal_year, lines, lit_year):
    """Build an event dict from collected lines for a single day."""
    # line 0 is typically DOW + title  or  just DOW
    title_parts = []
    color_str = None
    rank_str = None
    readings_lines = []
    notes = []
    optional_memorial = None

    idx = 0
    # --- Extract DOW and initial title ---
    first = lines[0]
    dow_m = DOW_PATTERN.match(first)
    if dow_m:
        rest = first[dow_m.end():].strip()
        if rest:
            title_parts.append(rest)
        idx = 1
    else:
        # DOW might be alone on line 0
        if first in DOW_ABBREVS or first.upper() in [d.upper() for d in DOW_ABBREVS]:
            idx = 1
        else:
            # No DOW found at all, treat as title
            title_parts.append(first)
            idx = 1

    # --- Walk remaining lines ---
    while idx < len(lines):
        ln = lines[idx]
        idx += 1

        # Color line?
        if COLOR_PATTERN.match(ln):
            color_str = ln
            continue

        # Rank keyword line?
        if ln in ('Solemnity', 'Feast', 'Memorial'):
            rank_str = ln
            continue

        # Holyday notation
        if '[Holyday of Obligation]' in ln:
            notes.append('Holy Day of Obligation')
            continue

        # Optional memorial in brackets
        bracket = re.match(r'^\[(.+?)\]\d*$', ln)
        if bracket:
            optional_memorial = bracket.group(1)
            continue
        # Partial bracket (multi-line optional memorial rarely)
        if ln.startswith('[') and ']' not in ln:
            # gather rest
            optional_memorial = ln.lstrip('[')
            continue

        # Readings line — contains scripture citations (book abbrev + chapter:verse)
        if re.search(r'[A-Z][a-z]*\s+\d+:', ln) or re.search(r'\(\d+', ln):
            # But not if it's a title continuation
            if not (ln.isupper() and len(ln) > 3 and ':' not in ln):
                readings_lines.append(ln)
                continue

        # Pss reference alone
        if re.match(r'^Pss\s', ln):
            readings_lines.append(ln)
            continue

        # Title continuation lines (ALL CAPS or starts with '(')  
        if ln.isupper() and len(ln) > 2:
            title_parts.append(ln)
            continue
        if ln.startswith('(') and not re.match(r'^\(\d', ln):
            title_parts.append(ln)
            continue
        if ln.startswith('The Octave') or ln.startswith('or '):
            title_parts.append(ln)
            continue
        # Lines like "Bishops and Doctors of the Church"
        if not re.search(r'\d', ln) and not COLOR_PATTERN.match(ln) and len(ln.split()) <= 10:
            title_parts.append(ln)
            continue

        # Anything else with scripture-like patterns
        if '/' in ln and re.search(r'[A-Z][a-z]', ln):
            readings_lines.append(ln)

    # --- Build title ---
    title = ' '.join(title_parts).strip()
    # Remove trailing footnote numbers
    title = re.sub(r'\d+$', '', title).strip()
    # Clean "USA: " prefix
    is_usa = 'USA:' in title
    title = re.sub(r'^USA:\s*', '', title).strip()

    if not title:
        return None

    # --- Determine rank ---
    if rank_str:
        rank = rank_str.lower()
        if rank == 'memorial':
            rank = 'memorial'
    elif title.isupper() and len(title) > 5:
        rank = 'solemnity'
    elif optional_memorial and 'Weekday' in title:
        rank = 'optional_memorial'
    elif optional_memorial and 'Day within' in title:
        rank = 'weekday'
    elif optional_memorial:
        rank = 'optional_memorial'
    elif 'Weekday' in title:
        rank = 'weekday'
    elif 'within the Octave' in title:
        rank = 'weekday'
    else:
        rank = 'weekday'

    # Fix: solemnities might have 'Solemnity' in notes lines
    if any('Solemnity' in l for l in lines):
        rank = 'solemnity'
    if any('Feast' in l for l in lines) and rank not in ('solemnity',):
        rank = 'feast'

    # --- Color ---
    if color_str:
        primary_color, hex_color = normalize_color(color_str)
    else:
        date_str = f"{cal_year}-{month:02d}-{day:02d}"
        season = get_season_for_date(date_str, lit_year)
        cmap = {'advent': 'violet', 'christmas': 'white', 'lent': 'violet',
                'triduum': 'white', 'easter': 'white', 'ordinary_time': 'green'}
        c = cmap.get(season, 'green')
        primary_color, hex_color = c, COLOR_HEX[c]

    # --- Readings ---
    readings_text = ' '.join(readings_lines)
    readings = parse_readings(readings_text) if readings_text.strip() else {
        'citations': None, 'lectionary_numbers': None}

    # --- Date ---
    date_str = f"{cal_year}-{month:02d}-{day:02d}"

    # --- Season & LOTH ---
    season = get_season_for_date(date_str, lit_year)
    loth = get_loth_volume(date_str, lit_year)

    # --- Notes ---
    if is_usa:
        notes.insert(0, 'USA Proper Calendar')

    # --- Metadata ---
    metadata = {
        'loth_volume': loth,
        'holy_day_of_obligation': 'Holy Day of Obligation' in notes,
        'us_proper_calendar': is_usa,
        'rank_priority': {'solemnity': 1, 'feast': 2, 'memorial': 3,
                         'optional_memorial': 4, 'weekday': 5}.get(rank, 5),
    }
    if optional_memorial:
        metadata['optional_memorial'] = optional_memorial

    return {
        'date': date_str,
        'title': title,
        'rank': rank,
        'color': primary_color,
        'color_hex': hex_color,
        'season': season,
        'year': lit_year,
        'readings': readings,
        'notes': notes or None,
        'metadata': metadata,
    }


def fix_unicode_in_json(data):
    """Post-process JSON to fix known Unicode extraction issues."""
    replacements = {
        'Andr\ufffd': 'André', 'Jun\ufffdpero': 'Junípero',
        'Br\ufffdb': 'Bréb', '\ufffd': 'é',
        'Ren\xe9': 'René', 'Ir\xe9n': 'Irén',
        'Curi\xe9': 'Curié',
    }
    txt = json.dumps(data, ensure_ascii=False)
    for bad, good in replacements.items():
        txt = txt.replace(bad, good)
    return json.loads(txt)


def main():
    if len(sys.argv) < 3:
        print("Usage: python extract_liturgical_calendar.py <pdf_path> <year>")
        sys.exit(1)

    pdf_path = sys.argv[1]
    lit_year = int(sys.argv[2])

    if not os.path.exists(pdf_path):
        print(f"Error: PDF not found at {pdf_path}")
        sys.exit(1)

    print(f"Extracting liturgical calendar for {lit_year} from {pdf_path}...")
    events = extract_calendar(pdf_path, lit_year)
    events = fix_unicode_in_json(events)

    print(f"Extracted {len(events)} events")

    # Output
    out_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)),
                           '..', 'laravel', 'database', 'seeders', 'data')
    os.makedirs(out_dir, exist_ok=True)

    out_path = os.path.join(out_dir, f'liturgical_{lit_year}.json')
    with open(out_path, 'w', encoding='utf-8') as f:
        json.dump(events, f, indent=2, ensure_ascii=False)
    print(f"Written to {out_path}")

    # LOTH volumes
    loth_path = os.path.join(out_dir, f'loth_volumes_{lit_year}.json')
    with open(loth_path, 'w', encoding='utf-8') as f:
        json.dump({'liturgical_year': lit_year, 'ranges': LOTH_VOLUMES.get(lit_year, [])},
                  f, indent=2, ensure_ascii=False)
    print(f"LOTH written to {loth_path}")

    # Sample output (ASCII-safe for Windows console)
    if events:
        print(f'\n-- Sample ({len(events)} total) --')
        for e in events[:8]:
            t = e['title'][:55].encode('ascii', 'replace').decode('ascii')
            print(f"  {e['date']} | {e['rank']:18s} | {e['color']:6s} | {e['season']:13s} | {t}")
        print('  ...')
        for e in events[-5:]:
            t = e['title'][:55].encode('ascii', 'replace').decode('ascii')
            print(f"  {e['date']} | {e['rank']:18s} | {e['color']:6s} | {e['season']:13s} | {t}")


if __name__ == '__main__':
    main()
