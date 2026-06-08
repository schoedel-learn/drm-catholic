#!/usr/bin/env python3
"""
USCCB Liturgical Calendar PDF → Markdown Converter
===================================================

Converts the official USCCB "Liturgical Calendar for the Dioceses of the
United States of America" PDF into a clean, well-structured Markdown file.

Designed for sharing with diocesan IT departments.  The script handles the
known text-extraction quirks of these PDFs:

  - En-dash (U+2013) and other non-ASCII separators in month headers
  - Vietnamese diacritics in saint names (e.g. Dũng-Lạc)
  - PyMuPDF replacement characters (U+FFFD)
  - Color strings bleeding into title text
  - Page numbers interleaved with calendar entries
  - "YEAR B" / "YEAR C" boundaries inside a single PDF
  - Spanish-language appendix at end of PDF

Requirements
------------
    pip install PyMuPDF       # provides the `fitz` module

Usage
-----
    python usccb_pdf_to_markdown.py  2026cal.pdf              # auto-detect year
    python usccb_pdf_to_markdown.py  2026cal.pdf  --year 2026
    python usccb_pdf_to_markdown.py  2026cal.pdf  -o calendar_2026.md

The output defaults to <input_basename>.md in the same directory.
"""

from __future__ import annotations

import argparse
import os
import re
import sys
import textwrap
from dataclasses import dataclass, field
from datetime import date as Date
from pathlib import Path

try:
    import fitz  # PyMuPDF
except ImportError:
    print("ERROR: PyMuPDF is required.  Install with:  pip install PyMuPDF")
    sys.exit(1)


# ═══════════════════════════════════════════════════════════════════════
#  Constants
# ═══════════════════════════════════════════════════════════════════════

MONTH_NAMES = {
    "JANUARY": 1, "FEBRUARY": 2, "MARCH": 3, "APRIL": 4,
    "MAY": 5, "JUNE": 6, "JULY": 7, "AUGUST": 8,
    "SEPTEMBER": 9, "OCTOBER": 10, "NOVEMBER": 11, "DECEMBER": 12,
}

MONTH_NUM_TO_NAME = {v: k.title() for k, v in MONTH_NAMES.items()}

DOW_ABBREVS = {"SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"}
DOW_PATTERN = re.compile(r"^(SUN|Mon|Tue|Wed|Thu|Fri|Sat)\b", re.IGNORECASE)

COLOR_PATTERN = re.compile(
    r"^(violet|white|red|green|rose)"
    r"(\s*/\s*(violet|white|red|green|rose))?"
    r"(\s+or\s+(violet|white|red|green|rose))?\s*$",
    re.IGNORECASE,
)

# Liturgical color display names and emoji indicators
COLOR_EMOJI = {
    "violet": "🟣", "white": "⚪", "red": "🔴",
    "green": "🟢", "rose":  "🌸",
}

RANK_EMOJI = {
    "solemnity":        "✨",
    "feast":            "⭐",
    "memorial":         "●",
    "optional_memorial": "○",
    "weekday":          "·",
}

RANK_LABEL = {
    "solemnity":        "Solemnity",
    "feast":            "Feast",
    "memorial":         "Memorial",
    "optional_memorial": "Optional Memorial",
    "weekday":          "Weekday",
}

# Spanish-section stop words (the PDFs include a Spanish appendix)
SPANISH_STOP_WORDS = frozenset([
    "CALENDARIO PROPIO", "FIESTAS PATRONALES",
    "Enero", "Febrero", "Marzo", "Abril",
    "Junio", "Julio", "Agosto",
])


# ═══════════════════════════════════════════════════════════════════════
#  Unicode / encoding helpers
# ═══════════════════════════════════════════════════════════════════════

# Known PDF-extraction character corruption patterns
_UNICODE_FIXES: list[tuple[str, str]] = [
    ("\ufffd",     "é"),    # Generic replacement char → most common in these PDFs
    ("Andr\ufffd", "André"),
    ("Jun\ufffdpero", "Junípero"),
    ("Br\ufffdb",  "Bréb"),
    ("D\u0169ng-L\u1ea1c",    "Dũng-Lạc"),     # Vietnamese – keep correct
    ("D?ng-L?c",              "Dũng-Lạc"),     # Fallback if fitz mangles
    ("Agust\u00edn",          "Agustín"),       # Spanish accent
    ("Agust?n",               "Agustín"),
    ("Ren\u00e9",             "René"),
    ("Ir\u00e9n",             "Irén"),
]


def fix_unicode(text: str) -> str:
    """Apply all known character-repair substitutions."""
    for bad, good in _UNICODE_FIXES:
        text = text.replace(bad, good)
    return text


def strip_trailing_color_bleed(title: str) -> str:
    """Remove liturgical color words that sometimes bleed into titles."""
    # Example: "Weekday green/red/white" → "Weekday"
    title = re.sub(
        r"\s+(green|violet|white|red|rose)(/\w+)*\s*$", "", title
    )
    # Also before "USA:" tag
    title = re.sub(
        r"\s+(green|violet|white|red|rose)(/\w+)*\s+USA:", " USA:", title
    )
    return title.strip()


def strip_year_header_bleed(title: str) -> str:
    """Remove 'YEAR B/C ...' text that sometimes gets appended to last entry."""
    if "YEAR " in title:
        title = title.split("YEAR")[0].strip()
    return title


# ═══════════════════════════════════════════════════════════════════════
#  Data structures
# ═══════════════════════════════════════════════════════════════════════

@dataclass
class CalendarEntry:
    """One day's liturgical entry, extracted from the PDF."""
    date:             Date
    day_of_week:      str = ""
    title:            str = ""
    rank:             str = "weekday"
    color:            str = ""
    color_alternate:  str = ""
    readings_raw:     str = ""
    notes:            list[str] = field(default_factory=list)
    optional_memorial: str = ""
    is_usa_proper:    bool = False

    # ── Markdown rendering ──────────────────────────────────────────

    def to_markdown_row(self) -> str:
        """Render this entry as one line in a Markdown table."""
        color_display = self.color.title() if self.color else ""
        if self.color_alternate:
            color_display += f" / {self.color_alternate.title()}"
        color_cell = f"{COLOR_EMOJI.get(self.color, '')} {color_display}".strip()

        rank_cell = f"{RANK_EMOJI.get(self.rank, '')} {RANK_LABEL.get(self.rank, self.rank.title())}".strip()

        title_cell = self.title
        if self.optional_memorial:
            title_cell += f"  *(or {self.optional_memorial})*"

        notes_parts = []
        if self.is_usa_proper:
            notes_parts.append("USA")
        for n in self.notes:
            notes_parts.append(n)

        notes_cell = "; ".join(notes_parts) if notes_parts else ""

        return (
            f"| {self.date.strftime('%a %b %d')} "
            f"| {title_cell} "
            f"| {rank_cell} "
            f"| {color_cell} "
            f"| {notes_cell} |"
        )

    def to_markdown_block(self) -> str:
        """Render this entry as a rich Markdown block (non-table format)."""
        parts = []

        # Date header
        date_str = self.date.strftime("%A, %B %d, %Y")
        parts.append(f"#### {date_str}")
        parts.append("")

        # Title with rank indicator
        rank_icon = RANK_EMOJI.get(self.rank, "")
        color_icon = COLOR_EMOJI.get(self.color, "")
        parts.append(f"**{rank_icon} {self.title}**")
        parts.append("")

        # Metadata line
        meta_parts = []
        meta_parts.append(f"**Rank:** {RANK_LABEL.get(self.rank, self.rank.title())}")
        color_display = self.color.title() if self.color else "—"
        if self.color_alternate:
            color_display += f" / {self.color_alternate.title()}"
        meta_parts.append(f"**Color:** {color_icon} {color_display}")
        parts.append(" · ".join(meta_parts))

        # Optional memorial
        if self.optional_memorial:
            parts.append(f"  *Optional Memorial: {self.optional_memorial}*")

        # Readings
        if self.readings_raw.strip():
            parts.append(f"**Readings:** {self.readings_raw}")

        # Notes
        if self.notes or self.is_usa_proper:
            note_items = []
            if self.is_usa_proper:
                note_items.append("USA Proper Calendar")
            note_items.extend(self.notes)
            parts.append(f"> {'; '.join(note_items)}")

        parts.append("")  # blank line separator
        return "\n".join(parts)


# ═══════════════════════════════════════════════════════════════════════
#  PDF extraction engine
# ═══════════════════════════════════════════════════════════════════════

def extract_text_from_pdf(pdf_path: str) -> list[str]:
    """Extract all text from a PDF and return as stripped lines."""
    doc = fitz.open(pdf_path)
    all_text = ""
    for page in doc:
        all_text += page.get_text() + "\n"
    doc.close()
    return [line.strip() for line in all_text.split("\n")]


def find_calendar_boundaries(lines: list[str]) -> tuple[int, int]:
    """
    Locate the start and end line indices of the English liturgical
    calendar section.  Looks for "YEAR A/B/C ... WEEKDAYS" headers.
    Returns (start_idx, stop_idx).
    """
    start_idx = None
    stop_idx = len(lines)

    for i, ln in enumerate(lines):
        if re.search(r"YEAR\s+[A-C].*WEEKDAYS", ln):
            if start_idx is None:
                start_idx = i
            else:
                stop_idx = i
                break

    if start_idx is None:
        raise ValueError(
            "Could not find 'YEAR X ... WEEKDAYS' calendar header in PDF. "
            "Is this a USCCB liturgical calendar PDF?"
        )

    return start_idx, stop_idx


def find_first_month_header(
    lines: list[str], start: int, stop: int
) -> tuple[int, int, int]:
    """
    Find the first month+year header line after `start`.
    Returns (line_index, month_number, year_number).
    """
    for i in range(start, stop):
        ln = lines[i]
        yr_match = re.search(r"(\d{4})", ln)
        if yr_match:
            for mname, mnum in MONTH_NAMES.items():
                if mname in ln:
                    return i, mnum, int(yr_match.group(1))

    raise ValueError("Could not find a month header after the YEAR header.")


def detect_year_from_pdf(pdf_path: str) -> int | None:
    """Try to guess the liturgical year from the PDF filename or text."""
    basename = os.path.basename(pdf_path)
    m = re.search(r"(\d{4})", basename)
    if m:
        return int(m.group(1))
    return None


def parse_entries(
    lines: list[str], cal_start: int, stop_idx: int,
    init_month: int, init_year: int,
) -> list[CalendarEntry]:
    """
    Walk through lines from `cal_start` to `stop_idx`, parsing each
    day-number-delimited entry into a CalendarEntry.
    """
    entries: list[CalendarEntry] = []
    current_month = init_month
    current_year = init_year
    prev_day = 0

    i = cal_start
    while i < stop_idx:
        ln = lines[i]

        # Skip blanks and page numbers
        if not ln:
            i += 1; continue
        if ln.isdigit() and int(ln) > 40:
            i += 1; continue

        # Stop at Spanish appendix
        if any(marker in ln for marker in SPANISH_STOP_WORDS):
            break

        # ── Month-year header ───────────────────────────────────────
        yr_match = re.search(r"(\d{4})", ln)
        if yr_match:
            for mname in MONTH_NAMES:
                if mname in ln:
                    current_month = MONTH_NAMES[mname]
                    current_year = int(yr_match.group(1))
                    prev_day = 0
                    break
            else:
                # No month name found, skip
                i += 1; continue
            i += 1; continue

        # Standalone month header (rare)
        if ln in MONTH_NAMES:
            current_month = MONTH_NAMES[ln]
            i += 1; continue

        # Skip footnote lines
        if re.match(r"^\d+\s+[A-Z][a-z]", ln):
            first_num = int(ln.split()[0])
            if first_num > 31:
                i += 1; continue

        # ── Day entry (bare number 1-31) ────────────────────────────
        if ln.isdigit() and 1 <= int(ln) <= 31 and current_month is not None:
            day = int(ln)

            # Month rollover detection
            if day < prev_day - 20:
                current_month += 1
                if current_month > 12:
                    current_month = 1
                    current_year += 1
            prev_day = day

            # Gather all lines for this entry
            entry_lines: list[str] = []
            i += 1
            while i < len(lines):
                nxt = lines[i]
                # Next day?
                if nxt.isdigit() and 1 <= int(nxt) <= 31:
                    break
                # Month header?
                if re.match(r"[A-Z]+\s+\d{4}$", nxt) or nxt in MONTH_NAMES:
                    break
                if re.match(r"[A-Z]+\s*[^A-Z0-9]*[A-Z]+\s+\d{4}", nxt):
                    break
                # Page number?
                if nxt.isdigit() and int(nxt) > 40:
                    i += 1; continue
                # Stop markers
                if any(m in nxt for m in ["CALENDARIO PROPIO", "FIESTAS PATRONALES"]):
                    break
                # Long footnotes
                if re.match(r"^\d+\s+[A-Z][a-z]", nxt) and len(nxt) > 60:
                    i += 1; continue
                if nxt:
                    entry_lines.append(nxt)
                i += 1

            if entry_lines:
                entry = build_entry(day, current_month, current_year, entry_lines)
                if entry:
                    entries.append(entry)
            continue

        i += 1

    return entries


def build_entry(
    day: int, month: int, year: int, lines: list[str]
) -> CalendarEntry | None:
    """Parse the collected lines for a single day into a CalendarEntry."""
    title_parts: list[str] = []
    color_str = ""
    rank_str = ""
    readings_parts: list[str] = []
    notes: list[str] = []
    optional_memorial = ""
    day_of_week = ""

    idx = 0

    # ── Extract day-of-week and initial title ───────────────────────
    first = lines[0]
    dow_m = DOW_PATTERN.match(first)
    if dow_m:
        day_of_week = dow_m.group(1)
        rest = first[dow_m.end():].strip()
        if rest:
            title_parts.append(rest)
        idx = 1
    elif first.upper() in DOW_ABBREVS:
        day_of_week = first
        idx = 1
    else:
        title_parts.append(first)
        idx = 1

    # ── Walk remaining lines ────────────────────────────────────────
    while idx < len(lines):
        ln = lines[idx]
        idx += 1

        # Color line
        if COLOR_PATTERN.match(ln):
            color_str = ln
            continue

        # Rank keyword
        if ln in ("Solemnity", "Feast", "Memorial"):
            rank_str = ln
            continue

        # Holy day notation
        if "[Holyday of Obligation]" in ln:
            notes.append("Holy Day of Obligation")
            continue

        # Optional memorial in brackets
        bracket = re.match(r"^\[(.+?)\]\d*$", ln)
        if bracket:
            optional_memorial = bracket.group(1)
            continue
        if ln.startswith("[") and "]" not in ln:
            optional_memorial = ln.lstrip("[")
            continue

        # Readings (scripture citations)
        if re.search(r"[A-Z][a-z]*\s+\d+:", ln) or re.search(r"\(\d+", ln):
            if not (ln.isupper() and len(ln) > 3 and ":" not in ln):
                readings_parts.append(ln)
                continue

        # Psalter reference
        if re.match(r"^Pss\s", ln):
            readings_parts.append(ln)
            continue

        # Title continuation (ALL CAPS)
        if ln.isupper() and len(ln) > 2:
            title_parts.append(ln)
            continue
        # Parenthetical title continuation
        if ln.startswith("(") and not re.match(r"^\(\d", ln):
            title_parts.append(ln)
            continue
        # Known title patterns
        if ln.startswith("The Octave") or ln.startswith("or "):
            title_parts.append(ln)
            continue
        # Short non-numeric lines → likely title
        if not re.search(r"\d", ln) and not COLOR_PATTERN.match(ln) and len(ln.split()) <= 10:
            title_parts.append(ln)
            continue

        # Scripture-like patterns with slashes
        if "/" in ln and re.search(r"[A-Z][a-z]", ln):
            readings_parts.append(ln)

    # ── Assemble title ──────────────────────────────────────────────
    title = " ".join(title_parts).strip()
    title = re.sub(r"\d+$", "", title).strip()          # trailing footnote numbers
    is_usa = "USA:" in title
    title = re.sub(r"^USA:\s*", "", title).strip()
    title = strip_trailing_color_bleed(title)
    title = strip_year_header_bleed(title)
    title = fix_unicode(title)

    if not title:
        return None

    # ── Determine rank ──────────────────────────────────────────────
    rank = "weekday"
    if rank_str:
        rank = rank_str.lower()
    elif title.isupper() and len(title) > 5:
        rank = "solemnity"
    elif optional_memorial and "Weekday" in title:
        rank = "optional_memorial"
    elif optional_memorial:
        rank = "optional_memorial"
    elif "Weekday" in title or "within the Octave" in title:
        rank = "weekday"

    # Override from explicit keywords in raw lines
    if any("Solemnity" in l for l in lines):
        rank = "solemnity"
    if any("Feast" in l for l in lines) and rank != "solemnity":
        rank = "feast"

    # ── Parse color ─────────────────────────────────────────────────
    color = ""
    color_alt = ""
    if color_str:
        parts = re.split(r"[/\s]+or\s+|\s*/\s*", color_str.strip().lower())
        parts = [p.strip() for p in parts if p.strip()]
        color = parts[0] if parts else ""
        color_alt = parts[1] if len(parts) > 1 else ""

    # ── Readings ────────────────────────────────────────────────────
    readings_raw = " ".join(readings_parts).strip()
    readings_raw = fix_unicode(readings_raw)
    # Clean up lectionary numbers for readability
    readings_raw = re.sub(r"\s*Pss\s+(?:I+V?|Prop)\s*\d*", "", readings_raw)

    # ── Notes ───────────────────────────────────────────────────────
    if is_usa:
        notes.insert(0, "USA Proper Calendar")

    try:
        entry_date = Date(year, month, day)
    except ValueError:
        return None

    return CalendarEntry(
        date=entry_date,
        day_of_week=day_of_week,
        title=title,
        rank=rank,
        color=color,
        color_alternate=color_alt,
        readings_raw=readings_raw,
        notes=notes,
        optional_memorial=fix_unicode(optional_memorial),
        is_usa_proper=is_usa,
    )


# ═══════════════════════════════════════════════════════════════════════
#  Markdown output
# ═══════════════════════════════════════════════════════════════════════

def entries_to_markdown(
    entries: list[CalendarEntry],
    liturgical_year: int,
    pdf_path: str,
    use_table: bool = True,
) -> str:
    """Convert a list of CalendarEntry objects into a full Markdown document."""
    md: list[str] = []

    # ── Document header ─────────────────────────────────────────────
    md.append(f"# USCCB Liturgical Calendar {liturgical_year}")
    md.append("")
    md.append(f"> Extracted from **{os.path.basename(pdf_path)}**")
    md.append(f"> Generated by `usccb_pdf_to_markdown.py`")
    md.append(f"> {len(entries)} daily entries")
    md.append("")

    # ── Legend ───────────────────────────────────────────────────────
    md.append("## Legend")
    md.append("")
    md.append("### Ranks")
    md.append("")
    for rk, emoji in RANK_EMOJI.items():
        md.append(f"- {emoji} **{RANK_LABEL[rk]}**")
    md.append("")
    md.append("### Liturgical Colors")
    md.append("")
    for clr, emoji in COLOR_EMOJI.items():
        md.append(f"- {emoji} {clr.title()}")
    md.append("")
    md.append("---")
    md.append("")

    # ── Group by month ──────────────────────────────────────────────
    current_month_key = None

    for entry in entries:
        month_key = (entry.date.year, entry.date.month)

        if month_key != current_month_key:
            current_month_key = month_key
            month_name = MONTH_NUM_TO_NAME.get(entry.date.month, "?")
            md.append(f"## {month_name} {entry.date.year}")
            md.append("")

            if use_table:
                md.append("| Date | Celebration | Rank | Color | Notes |")
                md.append("|------|-------------|------|-------|-------|")

        if use_table:
            md.append(entry.to_markdown_row())
        else:
            md.append(entry.to_markdown_block())

    if use_table:
        md.append("")  # trailing newline after last table

    # ── Footer ──────────────────────────────────────────────────────
    md.append("")
    md.append("---")
    md.append("")
    md.append(
        "*Source: Liturgical Calendar for the Dioceses of the United States "
        "of America, United States Conference of Catholic Bishops.*"
    )
    md.append("")

    return "\n".join(md)


# ═══════════════════════════════════════════════════════════════════════
#  Main
# ═══════════════════════════════════════════════════════════════════════

def main() -> None:
    parser = argparse.ArgumentParser(
        description="Convert a USCCB Liturgical Calendar PDF to Markdown.",
        epilog="Example:  python usccb_pdf_to_markdown.py 2026cal.pdf --year 2026",
    )
    parser.add_argument("pdf", help="Path to the USCCB liturgical calendar PDF")
    parser.add_argument(
        "--year", "-y", type=int, default=None,
        help="Liturgical year (auto-detected from filename if omitted)",
    )
    parser.add_argument(
        "--output", "-o", type=str, default=None,
        help="Output Markdown file path (default: <input>.md)",
    )
    parser.add_argument(
        "--format", "-f", choices=["table", "block"], default="table",
        help="Output format: 'table' (compact) or 'block' (detailed, one block per day)",
    )
    args = parser.parse_args()

    pdf_path = args.pdf
    if not os.path.exists(pdf_path):
        print(f"Error: PDF not found at {pdf_path}")
        sys.exit(1)

    # Detect year
    liturgical_year = args.year or detect_year_from_pdf(pdf_path)
    if liturgical_year is None:
        print("Error: Could not detect liturgical year. Use --year <YYYY>.")
        sys.exit(1)

    # Output path
    out_path = args.output
    if out_path is None:
        stem = Path(pdf_path).stem
        out_path = str(Path(pdf_path).parent / f"{stem}.md")

    print(f"Converting {pdf_path} (Year {liturgical_year})...")

    # ── Step 1: Extract text ────────────────────────────────────────
    lines = extract_text_from_pdf(pdf_path)
    print(f"  Extracted {len(lines)} text lines from PDF")

    # ── Step 2: Find calendar boundaries ────────────────────────────
    start_idx, stop_idx = find_calendar_boundaries(lines)
    print(f"  Calendar section: lines {start_idx}..{stop_idx}")

    # ── Step 3: Find first month header ─────────────────────────────
    cal_start, init_month, init_year = find_first_month_header(
        lines, start_idx, stop_idx
    )
    print(f"  First month: {MONTH_NUM_TO_NAME.get(init_month, '?')} {init_year}")

    # ── Step 4: Parse entries ───────────────────────────────────────
    entries = parse_entries(lines, cal_start, stop_idx, init_month, init_year)
    print(f"  Parsed {len(entries)} daily entries")

    if not entries:
        print("WARNING: No entries extracted. Check that this is a valid USCCB calendar PDF.")
        sys.exit(1)

    # ── Step 5: Render Markdown ─────────────────────────────────────
    use_table = args.format == "table"
    markdown = entries_to_markdown(entries, liturgical_year, pdf_path, use_table=use_table)

    # ── Step 6: Write output ────────────────────────────────────────
    with open(out_path, "w", encoding="utf-8") as f:
        f.write(markdown)

    print(f"  Written to {out_path}")
    print(f"\nDone! {len(entries)} entries converted to Markdown.")

    # Quick preview (ASCII-safe for Windows consoles)
    rank_ascii = {"solemnity": "[S]", "feast": "[F]", "memorial": "[M]",
                  "optional_memorial": "[O]", "weekday": "[ ]"}
    print(f"\n-- First 5 entries --")
    for e in entries[:5]:
        t = e.title[:60].encode("ascii", "replace").decode("ascii")
        print(f"  {e.date}  {rank_ascii.get(e.rank, '?')} {t}")
    print(f"  ...")
    for e in entries[-3:]:
        t = e.title[:60].encode("ascii", "replace").decode("ascii")
        print(f"  {e.date}  {rank_ascii.get(e.rank, '?')} {t}")


if __name__ == "__main__":
    main()
