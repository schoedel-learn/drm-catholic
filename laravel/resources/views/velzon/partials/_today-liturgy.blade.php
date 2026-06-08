{{-- Today's Liturgy Card --}}
@php
    $todayEvent = $todayEvent ?? null;
    $lothRanges = $lothRanges ?? [];
    $seasonLabels = [
        'advent' => 'Advent',
        'christmas' => 'Christmas',
        'lent' => 'Lent',
        'triduum' => 'Triduum',
        'easter' => 'Easter',
        'ordinary_time' => 'Ordinary Time',
    ];
    $lothSeasonLabels = [
        'advent_christmas' => 'Advent / Christmas',
        'ordinary_time' => 'Ordinary Time',
        'lent_easter' => 'Lent / Easter',
    ];
    $rankLabels = [
        'solemnity' => 'Solemnity',
        'feast' => 'Feast',
        'memorial' => 'Memorial',
        'optional_memorial' => 'Optional Memorial',
        'weekday' => 'Weekday',
    ];
    $seasonColors = [
        'advent' => '#6f42c1',
        'christmas' => '#f8f9fa',
        'lent' => '#6f42c1',
        'triduum' => '#dc3545',
        'easter' => '#ffc107',
        'ordinary_time' => '#198754',
    ];
    // Find the active LOTH range and build tooltip for others
    $todayStr = now()->toDateString();
    $activeLoth = null;
    $otherLothLines = [];
    foreach ($lothRanges as $range) {
        $label = $lothSeasonLabels[$range['season']] ?? ucfirst(str_replace('_', ' ', $range['season']));
        $line = \Carbon\Carbon::parse($range['start'])->format('M j') . ' – ' . \Carbon\Carbon::parse($range['end'])->format('M j, Y') . ' · ' . $label . ' · Vol. ' . $range['loth_volume'];
        if ($todayStr >= $range['start'] && $todayStr <= $range['end']) {
            $activeLoth = $line;
        } else {
            $otherLothLines[] = $line;
        }
    }
    $lothTooltip = implode('&#10;', $otherLothLines);
@endphp

<div class="card" id="today-liturgy-card">
    {{-- Color band at top --}}
    <div
        style="height: 6px; background-color: {{ $todayEvent ? ($todayEvent['color_hex'] ?? '#198754') : '#198754' }}; border-radius: 0.25rem 0.25rem 0 0;">
    </div>

    <div class="card-header">
        <h4 class="card-title mb-0">
            <i class="ri-calendar-check-line align-middle me-1"></i>
            Today's Liturgy
        </h4>
    </div>

    <div class="card-body">
        {{-- Liturgical Day Label — always shown --}}
        @if($todayEvent && !empty($todayEvent['liturgical_day_label']))
            <div class="mb-3">
                <span class="fs-14 fw-semibold text-primary">
                    <i class="ri-time-line me-1"></i>{{ $todayEvent['liturgical_day_label'] }}
                </span>
            </div>
        @else
            <div class="mb-3">
                <span class="fs-14 fw-semibold text-muted">
                    <i class="ri-time-line me-1"></i>{{ now()->format('l') }}
                </span>
            </div>
        @endif

        @if($todayEvent)
            {{-- Title & Rank --}}
            <h5 class="mb-2">
                <span class="me-1">{{ $todayEvent['rank_icon'] ?? '' }}</span>
                {{ $todayEvent['title'] }}
            </h5>

            {{-- Badges row --}}
            <div class="d-flex flex-wrap gap-2 mb-3">
                {{-- Rank badge --}}
                <span class="badge bg-primary-subtle text-primary">
                    {{ $rankLabels[$todayEvent['rank']] ?? ucfirst($todayEvent['rank']) }}
                </span>

                {{-- Season badge --}}
                <span class="badge"
                    style="background-color: {{ $seasonColors[$todayEvent['season']] ?? '#198754' }}20; color: {{ $seasonColors[$todayEvent['season']] ?? '#198754' }};">
                    {{ $seasonLabels[$todayEvent['season']] ?? ucfirst(str_replace('_', ' ', $todayEvent['season'])) }}
                </span>

                {{-- Color badge --}}
                <span class="badge bg-light text-dark border">
                    <span class="rounded-circle d-inline-block me-1"
                        style="width: 10px; height: 10px; background-color: {{ $todayEvent['color_hex'] ?? '#198754' }};"></span>
                    {{ ucfirst($todayEvent['color'] ?? 'green') }}
                </span>
            </div>

            {{-- Lectionary Readings --}}
            @if(!empty($todayEvent['readings']))
                @php
                    // Build lectionary URL from the event date
                    $lectDate = \Carbon\Carbon::parse($todayEvent['date']);
                    $lectUrl = 'https://bible.usccb.org/bible/readings/' . $lectDate->format('mdy') . '.cfm';
                    $lectNums = $todayEvent['readings']['lectionary_numbers'] ?? [];

                    // Reading type metadata
                    $typeConfig = [
                        'reading_1' => ['label' => 'First Reading',      'icon' => 'ri-book-2-line',     'color' => 'primary'],
                        'psalm'     => ['label' => 'Responsorial Psalm', 'icon' => 'ri-music-2-line',    'color' => 'success'],
                        'reading_2' => ['label' => 'Second Reading',     'icon' => 'ri-book-2-line',     'color' => 'info'],
                        'alleluia'  => ['label' => 'Alleluia',           'icon' => 'ri-volume-up-line',  'color' => 'warning'],
                        'gospel'    => ['label' => 'Gospel',             'icon' => 'ri-book-open-line',  'color' => 'danger'],
                    ];

                    // Prefer structured data from enrichment command
                    $structured = $todayEvent['readings']['structured'] ?? null;

                    if ($structured) {
                        // Use structured USCCB data directly
                        $labeled = [];
                        foreach ($structured as $reading) {
                            $cfg = $typeConfig[$reading['type']] ?? ['label' => ucfirst($reading['type']), 'icon' => 'ri-book-2-line', 'color' => 'secondary'];
                            $labeled[] = [
                                'label'    => $cfg['label'],
                                'icon'     => $cfg['icon'],
                                'color'    => $cfg['color'],
                                'citation' => $reading['citation'],
                                'url'      => trim($reading['bible_url'] ?? ''),
                                'refrain'  => $reading['refrain'] ?? null,
                                'type'     => $reading['type'],
                            ];
                        }
                    } else {
                        // Fallback: parse citations manually (for un-enriched events)
                        $bookSlugs = [
                            'gn'=>'genesis','gen'=>'genesis','ex'=>'exodus','exod'=>'exodus',
                            'lv'=>'leviticus','lev'=>'leviticus','nm'=>'numbers','num'=>'numbers',
                            'dt'=>'deuteronomy','deut'=>'deuteronomy','jos'=>'joshua','josh'=>'joshua',
                            'jgs'=>'judges','judg'=>'judges','ru'=>'ruth','ruth'=>'ruth',
                            '1 sm'=>'1samuel','1 sam'=>'1samuel','2 sm'=>'2samuel','2 sam'=>'2samuel',
                            '1 kgs'=>'1kings','1 kings'=>'1kings','2 kgs'=>'2kings','2 kings'=>'2kings',
                            '1 chr'=>'1chronicles','2 chr'=>'2chronicles',
                            'ezr'=>'ezra','neh'=>'nehemiah','tb'=>'tobit','tob'=>'tobit',
                            'jdt'=>'judith','est'=>'esther',
                            '1 mc'=>'1maccabees','2 mc'=>'2maccabees',
                            'jb'=>'job','job'=>'job','ps'=>'psalms','pss'=>'psalms','psalm'=>'psalms',
                            'prv'=>'proverbs','prov'=>'proverbs','eccl'=>'ecclesiastes',
                            'sg'=>'songofsongs','song'=>'songofsongs',
                            'wis'=>'wisdom','sir'=>'sirach','sirach'=>'sirach',
                            'is'=>'isaiah','isa'=>'isaiah','jer'=>'jeremiah','lam'=>'lamentations',
                            'bar'=>'baruch','ez'=>'ezekiel','ezek'=>'ezekiel',
                            'dn'=>'daniel','dan'=>'daniel',
                            'hos'=>'hosea','jl'=>'joel','am'=>'amos','ob'=>'obadiah',
                            'jon'=>'jonah','mi'=>'micah','na'=>'nahum','hb'=>'habakkuk',
                            'zep'=>'zephaniah','hg'=>'haggai','zec'=>'zechariah','mal'=>'malachi',
                            'mt'=>'matthew','mk'=>'mark','lk'=>'luke','jn'=>'john',
                            'acts'=>'acts','rom'=>'romans',
                            '1 cor'=>'1corinthians','2 cor'=>'2corinthians',
                            'gal'=>'galatians','eph'=>'ephesians','phil'=>'philippians',
                            'col'=>'colossians','1 thes'=>'1thessalonians','2 thes'=>'2thessalonians',
                            '1 tm'=>'1timothy','2 tm'=>'2timothy',
                            'ti'=>'titus','phlm'=>'philemon','heb'=>'hebrews',
                            'jas'=>'james','1 pt'=>'1peter','2 pt'=>'2peter',
                            '1 jn'=>'1john','2 jn'=>'2john','3 jn'=>'3john',
                            'jude'=>'jude','rv'=>'revelation','rev'=>'revelation',
                        ];

                        $buildBibleUrl = function(string $citation) use ($bookSlugs) {
                            $citation = trim($citation);
                            $citation = preg_replace('/\s+or\s+.*/i', '', $citation);
                            if (preg_match('/^(\d?\s*\w[\w\s]*?)\s+(\d+)(?::(\d+))?/', $citation, $m)) {
                                $bookKey = strtolower(rtrim(trim($m[1]), '.'));
                                $slug = $bookSlugs[$bookKey] ?? null;
                                if ($slug) {
                                    $url = "https://bible.usccb.org/bible/{$slug}/{$m[2]}";
                                    if (!empty($m[3])) $url .= "?{$m[3]}";
                                    return $url;
                                }
                            }
                            return null;
                        };

                        $allParts = [];
                        foreach ($todayEvent['readings']['citations'] ?? [] as $citationGroup) {
                            $parts = preg_split('#/(?=[A-Z]|\d\s+[A-Z])#', $citationGroup);
                            $allParts = array_merge($allParts, $parts);
                        }

                        $count = count($allParts);
                        $isSunday = $lectDate->isSunday();
                        $isSolemnity = ($todayEvent['rank'] ?? '') === 'solemnity';

                        if ($count === 2) {
                            $types = ['reading_1', 'gospel'];
                        } elseif ($count === 3 && ($isSunday || $isSolemnity)) {
                            $types = ['reading_1', 'reading_2', 'gospel'];
                        } elseif ($count === 3) {
                            $types = ['reading_1', 'psalm', 'gospel'];
                        } elseif ($count === 4) {
                            $types = ['reading_1', 'psalm', 'reading_2', 'gospel'];
                        } else {
                            $types = array_fill(0, $count, 'reading_1');
                        }

                        $labeled = [];
                        foreach ($allParts as $i => $part) {
                            $type = $types[$i] ?? 'reading_1';
                            $cfg = $typeConfig[$type];
                            $labeled[] = [
                                'label'    => $cfg['label'],
                                'icon'     => $cfg['icon'],
                                'color'    => $cfg['color'],
                                'citation' => trim($part),
                                'url'      => $buildBibleUrl(trim($part)),
                                'refrain'  => null,
                                'type'     => $type,
                            ];
                        }
                    }
                @endphp

                @if(!empty($labeled))
                <div class="mb-3">
                    {{-- Header with lectionary link --}}
                    <h6 class="text-muted text-uppercase fs-11 mb-3 d-flex align-items-center">
                        <i class="ri-book-open-line me-1"></i>Lectionary Readings
                        @if(!empty($lectNums))
                            <a href="{{ $lectUrl }}" target="_blank" rel="noopener"
                                class="ms-auto badge bg-primary text-white text-decoration-none"
                                title="View full readings on USCCB">
                                <i class="ri-external-link-line me-1"></i>Lect. No. {{ implode(', ', $lectNums) }}
                            </a>
                        @endif
                    </h6>

                    {{-- Each reading as a separate row --}}
                    <div class="vstack gap-0">
                        @foreach($labeled as $reading)
                            <div class="d-flex align-items-start py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                {{-- Left: reading label --}}
                                <div class="d-flex align-items-center" style="min-width: 160px;">
                                    <i class="{{ $reading['icon'] }} text-{{ $reading['color'] }} me-2 fs-16"></i>
                                    <span class="text-muted fs-12 text-uppercase fw-semibold">{{ $reading['label'] }}</span>
                                </div>
                                {{-- Right: citation + optional refrain --}}
                                <div class="ms-auto text-end">
                                    @if($reading['url'])
                                        <a href="{{ $reading['url'] }}" target="_blank" rel="noopener"
                                            class="fw-semibold fs-13 text-primary text-decoration-none"
                                            title="Read {{ $reading['citation'] }} on USCCB">
                                            {{ $reading['citation'] }}
                                            <i class="ri-external-link-line ms-1 fs-11"></i>
                                        </a>
                                    @else
                                        <span class="fw-semibold fs-13">{{ $reading['citation'] }}</span>
                                    @endif
                                    @if(!empty($reading['refrain']))
                                        <div class="text-muted fs-11 fst-italic mt-1">
                                            R. {{ $reading['refrain'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif

            {{-- LOTH Volume — inline date range + tooltip for others --}}
            @if($activeLoth)
                <div class="mb-2 d-flex align-items-center">
                    <span class="text-muted fs-12">
                        <i class="ri-booklet-line me-1"></i>{{ $activeLoth }}
                    </span>
                    @if(!empty($lothTooltip))
                        <span class="ms-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                            title="{{ $lothTooltip }}">
                            <i class="ri-information-line text-muted fs-14" style="cursor: help;"></i>
                        </span>
                    @endif
                </div>
            @elseif(!empty($todayEvent['loth_volume']))
                <div class="mb-2">
                    <span class="text-muted fs-12">
                        <i class="ri-booklet-line me-1"></i>LOTH Volume:
                    </span>
                    <span class="badge bg-info-subtle text-info">
                        Vol. {{ $todayEvent['loth_volume'] }}
                    </span>
                </div>
            @endif

            {{-- Notes --}}
            @if(!empty($todayEvent['notes']))
                <div class="mt-2">
                    @foreach((array) $todayEvent['notes'] as $note)
                        <div class="text-muted fs-12">
                            <i class="ri-information-line me-1"></i>{{ $note }}
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <div class="text-muted text-center py-3">
                <i class="ri-calendar-line fs-24 d-block mb-2"></i>
                <p class="mb-0">No liturgical data available for today.</p>
            </div>
        @endif
    </div>

    <div class="card-footer border-top text-muted fs-12">
        <i class="ri-time-line me-1"></i>{{ now()->format('l, F j, Y') }}
    </div>
</div>