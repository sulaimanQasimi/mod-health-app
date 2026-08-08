@php
    $visual = $visualExamination ?? [];
    $refractionData = $refraction ?? [];
    $display = function (...$path) use ($visual, $refractionData) {
        $source = $path[0] === 'refraction' ? $refractionData : $visual;
        $keys = $path[0] === 'refraction' ? array_slice($path, 1) : $path;
        $cursor = $source;
        foreach ($keys as $key) {
            $cursor = is_array($cursor) ? ($cursor[$key] ?? null) : null;
        }
        return ($cursor === null || $cursor === '') ? '—' : $cursor;
    };
    $metrics = [
        ['visual_acuity', 'VA'],
        ['pinhole_vision', 'PH'],
        ['vision_with_glasses', 'cGL'],
        ['near_vision', 'NV'],
        ['intraocular_pressure', 'IOP'],
    ];
@endphp

<div class="eye-diagram" id="eye-diagram">
    <div class="row g-3">
        @foreach ([
            ['od', 'چشم راست', 'Oculus Dexter (OD)', '#0891b2', 'eye-panel-od'],
            ['os', 'چشم چپ', 'Oculus Sinister (OS)', '#7c3aed', 'eye-panel-os'],
        ] as [$side, $title, $subtitle, $accent, $panelClass])
            @php
                $sphere = $display('refraction', $side, 'sphere');
                $cylinder = $display('refraction', $side, 'cylinder');
                $axis = $display('refraction', $side, 'axis');
                $hasRefraction = collect([$sphere, $cylinder, $axis])->contains(fn ($v) => $v !== '—');
            @endphp
            <div class="col-md-6">
                <div class="eye-panel {{ $panelClass }} h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold">{{ $title }}</div>
                            <div class="small text-muted">{{ $subtitle }}</div>
                        </div>
                        <span class="badge bg-white text-dark border">{{ strtoupper($side) }}</span>
                    </div>

                    <div class="text-center mb-3">
                        <svg viewBox="0 0 220 160" class="eye-svg" aria-hidden="true">
                            <ellipse cx="110" cy="82" rx="92" ry="52" fill="{{ $accent }}18" />
                            <path d="M28 78 C60 28, 160 28, 192 78" fill="none" stroke="#64748b" stroke-width="4" stroke-linecap="round" />
                            <path d="M28 78 C60 128, 160 128, 192 78" fill="none" stroke="#64748b" stroke-width="4" stroke-linecap="round" />
                            <ellipse cx="110" cy="78" rx="78" ry="38" fill="#f8fafc" stroke="#94a3b8" stroke-width="2" />
                            <circle cx="110" cy="78" r="28" fill="{{ $accent }}" opacity="0.85" />
                            <circle cx="110" cy="78" r="28" fill="url(#iris-{{ $side }})" opacity="0.35" />
                            <circle cx="110" cy="78" r="12" fill="#0f172a" />
                            <circle cx="{{ $side === 'od' ? 102 : 118 }}" cy="70" r="4.5" fill="#ffffff" opacity="0.9" />
                            <defs>
                                <radialGradient id="iris-{{ $side }}" cx="40%" cy="35%" r="65%">
                                    <stop offset="0%" stop-color="#ffffff" stop-opacity="0.55" />
                                    <stop offset="55%" stop-color="#ffffff" stop-opacity="0" />
                                    <stop offset="100%" stop-color="#000000" stop-opacity="0.25" />
                                </radialGradient>
                            </defs>
                        </svg>
                    </div>

                    <div class="row g-2">
                        @foreach ($metrics as [$key, $label])
                            <div class="col-4">
                                <div class="eye-metric-chip text-center">
                                    <div class="eye-metric-label">{{ $label }}</div>
                                    <div class="eye-metric-value fw-bold"
                                         data-eye-metric="{{ $side }}.{{ $key }}">
                                        {{ $display($side, $key) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row g-2 mt-2 refraction-row {{ $hasRefraction ? '' : 'd-none' }}"
                         data-refraction-row="{{ $side }}">
                        <div class="col-4 text-center">
                            <div class="small text-muted">SPH</div>
                            <div class="fw-semibold" data-eye-metric="refraction.{{ $side }}.sphere">{{ $sphere }}</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="small text-muted">CYL</div>
                            <div class="fw-semibold" data-eye-metric="refraction.{{ $side }}.cylinder">{{ $cylinder }}</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="small text-muted">Axis</div>
                            <div class="fw-semibold" data-eye-metric="refraction.{{ $side }}.axis">{{ $axis }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mt-1">
        @foreach ([
            ['squint_assessment', 'انحراف چشم'],
            ['blood_pressure', 'فشار خون'],
            ['color_vision', 'دید رنگی'],
        ] as [$key, $label])
            <div class="col-md-4">
                <div class="eye-extra-chip text-center">
                    <div class="small text-muted">{{ $label }}</div>
                    <div class="fw-semibold" data-eye-metric="{{ $key }}">{{ $display($key) }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .eye-panel {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem;
        background: linear-gradient(180deg, #fff, #f8fafc);
    }
    .eye-panel-od {
        border-color: #a5f3fc;
        background: linear-gradient(160deg, #ecfeff, #fff);
        color: #155e75;
    }
    .eye-panel-os {
        border-color: #ddd6fe;
        background: linear-gradient(160deg, #f5f3ff, #fff);
        color: #5b21b6;
    }
    .eye-svg {
        width: 100%;
        max-width: 220px;
        height: auto;
    }
    .eye-metric-chip,
    .eye-extra-chip {
        border: 1px solid rgba(148, 163, 184, 0.45);
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.85);
        padding: 0.45rem 0.35rem;
    }
    .eye-metric-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        opacity: 0.7;
    }
    .eye-metric-value {
        font-size: 0.9rem;
    }
    .refraction-row {
        border: 1px dashed rgba(100, 116, 139, 0.35);
        border-radius: 0.75rem;
        padding: 0.5rem 0.25rem;
        margin-left: 0;
        margin-right: 0;
        background: rgba(255, 255, 255, 0.55);
    }
</style>
