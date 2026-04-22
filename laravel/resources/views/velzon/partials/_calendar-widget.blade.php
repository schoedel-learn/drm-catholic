{{-- Calendar Widget with Liturgical Overlay Toggle --}}
@php
    $todayColor = $todayEvent['color_hex'] ?? '#198754';
@endphp

<div class="card" id="calendar-widget-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">
            <i class="ri-calendar-2-line align-middle me-1"></i>
            Calendar
        </h4>
        <div class="d-flex align-items-center gap-3">
            {{-- Liturgical Overlay Toggle --}}
            <div class="form-check form-switch mb-0" title="Toggle liturgical calendar overlay">
                <input class="form-check-input" type="checkbox" id="toggleLiturgicalOverlay" checked>
                <label class="form-check-label fs-13" for="toggleLiturgicalOverlay">
                    <span class="rounded-circle d-inline-block me-1" id="liturgicalColorDot"
                        style="width: 8px; height: 8px; background-color: {{ $todayColor }};"></span>
                    Liturgical Calendar
                </label>
            </div>
            {{-- Full View Link --}}
            <a href="{{ route('calendar') }}" class="btn btn-sm btn-soft-primary">
                <i class="ri-fullscreen-line me-1"></i>Full View
            </a>
        </div>
    </div>
    <div class="card-body">
        <div id="calendar-widget"></div>
    </div>
</div>