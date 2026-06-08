{{-- Liturgy of the Hours Volume Guide --}}
@php
    $lothRanges = $lothRanges ?? [];
    $today = now()->toDateString();
@endphp

<div class="card" id="loth-guide-card">
    <div class="card-header">
        <h4 class="card-title mb-0">
            <i class="ri-booklet-line align-middle me-1"></i>
            Liturgy of the Hours
        </h4>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="fs-12">Date Range</th>
                        <th class="fs-12">Season</th>
                        <th class="fs-12 text-center">Volume</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lothRanges as $range)
                        @php
                            $isActive = $today >= $range['start'] && $today <= $range['end'];
                            $seasonLabels = [
                                'advent_christmas' => 'Advent / Christmas',
                                'ordinary_time' => 'Ordinary Time',
                                'lent_easter' => 'Lent / Easter',
                            ];
                        @endphp
                        <tr class="{{ $isActive ? 'table-active fw-medium' : '' }}">
                            <td class="fs-13">
                                @if($isActive)
                                    <i class="ri-arrow-right-s-fill text-primary me-1"></i>
                                @endif
                                {{ \Carbon\Carbon::parse($range['start'])->format('M j') }}
                                &ndash;
                                {{ \Carbon\Carbon::parse($range['end'])->format('M j, Y') }}
                            </td>
                            <td class="fs-13">
                                {{ $seasonLabels[$range['season']] ?? ucfirst(str_replace('_', ' ', $range['season'])) }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $isActive ? 'bg-primary' : 'bg-light text-dark border' }}">
                                    Vol. {{ $range['loth_volume'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                No LOTH data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>