type EyeSide = 'od' | 'os';

type JsonMap = Record<string, any>;

export interface EyeExaminationDiagramProps {
    visualExamination?: JsonMap;
    refraction?: JsonMap;
    compact?: boolean;
    className?: string;
}

const VISUAL_LABELS: Array<[string, string]> = [
    ['visual_acuity', 'VA'],
    ['best_corrected_acuity', 'BCVA'],
    ['pinhole_vision', 'PH'],
    ['vision_with_glasses', 'cGL'],
    ['near_vision', 'NV'],
    ['intraocular_pressure', 'IOP'],
];

function valueOf(data: JsonMap | undefined, ...path: string[]): string {
    let cursor: any = data;
    for (const part of path) {
        cursor = cursor?.[part];
    }
    if (cursor === null || cursor === undefined || cursor === '') {
        return '—';
    }
    return String(cursor);
}

function EyeSvg({ side, accent }: { side: EyeSide; accent: string }) {
    const isOd = side === 'od';
    const gradientId = `irisTexture-${side}`;
    return (
        <svg viewBox="0 0 220 160" className="h-auto w-full max-w-[240px]" aria-hidden>
            {/* soft glow */}
            <ellipse cx="110" cy="82" rx="92" ry="52" fill={`${accent}18`} />
            {/* upper lid */}
            <path
                d="M28 78 C60 28, 160 28, 192 78"
                fill="none"
                stroke="#64748b"
                strokeWidth="4"
                strokeLinecap="round"
            />
            {/* lower lid */}
            <path
                d="M28 78 C60 128, 160 128, 192 78"
                fill="none"
                stroke="#64748b"
                strokeWidth="4"
                strokeLinecap="round"
            />
            {/* sclera */}
            <ellipse cx="110" cy="78" rx="78" ry="38" fill="#f8fafc" stroke="#94a3b8" strokeWidth="2" />
            {/* iris */}
            <circle cx="110" cy="78" r="28" fill={accent} opacity="0.85" />
            <circle cx="110" cy="78" r="28" fill={`url(#${gradientId})`} opacity="0.35" />
            {/* pupil */}
            <circle cx="110" cy="78" r="12" fill="#0f172a" />
            {/* highlight */}
            <circle cx={isOd ? 102 : 118} cy="70" r="4.5" fill="#ffffff" opacity="0.9" />
            {/* lashes hint */}
            {[40, 60, 80, 100, 120, 140, 160, 180].map((x) => (
                <line
                    key={x}
                    x1={x}
                    y1={48 - Math.abs(110 - x) * 0.08}
                    x2={x + (isOd ? -4 : 4)}
                    y2={36 - Math.abs(110 - x) * 0.05}
                    stroke="#475569"
                    strokeWidth="1.5"
                    strokeLinecap="round"
                />
            ))}
            <defs>
                <radialGradient id={gradientId} cx="40%" cy="35%" r="65%">
                    <stop offset="0%" stopColor="#ffffff" stopOpacity="0.55" />
                    <stop offset="55%" stopColor="#ffffff" stopOpacity="0" />
                    <stop offset="100%" stopColor="#000000" stopOpacity="0.25" />
                </radialGradient>
            </defs>
        </svg>
    );
}

function MetricChip({ label, value, tone }: { label: string; value: string; tone: string }) {
    return (
        <div className={`rounded-xl border px-2.5 py-1.5 text-center shadow-sm ${tone}`}>
            <div className="text-[10px] font-semibold uppercase tracking-wide opacity-70">{label}</div>
            <div className="mt-0.5 text-sm font-bold tabular-nums">{value}</div>
        </div>
    );
}

function EyePanel({
    side,
    title,
    subtitle,
    visualExamination,
    refraction,
    accentClass,
    chipClass,
    svgAccent,
}: {
    side: EyeSide;
    title: string;
    subtitle: string;
    visualExamination?: JsonMap;
    refraction?: JsonMap;
    accentClass: string;
    chipClass: string;
    svgAccent: string;
}) {
    const sphere = valueOf(refraction, side, 'sphere');
    const cylinder = valueOf(refraction, side, 'cylinder');
    const axis = valueOf(refraction, side, 'axis');
    const add = valueOf(refraction, side, 'add');
    const hasRefraction = [sphere, cylinder, axis, add].some((item) => item !== '—');

    return (
        <div className={`rounded-2xl border p-4 ${accentClass}`}>
            <div className="mb-3 flex items-center justify-between gap-2">
                <div>
                    <div className="text-sm font-bold">{title}</div>
                    <div className="text-xs opacity-70">{subtitle}</div>
                </div>
                <span className="rounded-full bg-white/80 px-2.5 py-1 text-xs font-bold dark:bg-gray-900/60">
                    {side.toUpperCase()}
                </span>
            </div>

            <div className="mx-auto mb-3 flex justify-center">
                <EyeSvg side={side} accent={svgAccent} />
            </div>

            <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                {VISUAL_LABELS.map(([key, label]) => (
                    <MetricChip
                        key={key}
                        label={label}
                        value={valueOf(visualExamination, side, key)}
                        tone={chipClass}
                    />
                ))}
            </div>

            {hasRefraction && (
                <div className="mt-3 grid grid-cols-4 gap-2 rounded-xl border border-dashed border-current/20 bg-white/50 p-2 text-center text-xs dark:bg-gray-900/30">
                    <div>
                        <div className="opacity-60">SPH</div>
                        <div className="font-semibold tabular-nums">{sphere}</div>
                    </div>
                    <div>
                        <div className="opacity-60">CYL</div>
                        <div className="font-semibold tabular-nums">{cylinder}</div>
                    </div>
                    <div>
                        <div className="opacity-60">Axis</div>
                        <div className="font-semibold tabular-nums">{axis}</div>
                    </div>
                    <div>
                        <div className="opacity-60">ADD</div>
                        <div className="font-semibold tabular-nums">{add}</div>
                    </div>
                </div>
            )}
        </div>
    );
}

export default function EyeExaminationDiagram({
    visualExamination,
    refraction,
    compact = false,
    className = '',
}: EyeExaminationDiagramProps) {
    const extras: Array<[string, string, string]> = [
        ['squint_assessment', 'انحراف چشم', valueOf(visualExamination, 'squint_assessment')],
        ['blood_pressure', 'فشار خون', valueOf(visualExamination, 'blood_pressure')],
        ['color_vision', 'دید رنگی', valueOf(visualExamination, 'color_vision')],
    ];

    return (
        <div className={`space-y-4 ${className}`}>
            <div className={`grid gap-4 ${compact ? 'md:grid-cols-2' : 'lg:grid-cols-2'}`}>
                <EyePanel
                    side="od"
                    title="چشم راست"
                    subtitle="Oculus Dexter (OD)"
                    visualExamination={visualExamination}
                    refraction={refraction}
                    accentClass="border-cyan-200 bg-gradient-to-br from-cyan-50 to-white text-cyan-900 dark:border-cyan-900 dark:from-cyan-950/40 dark:to-gray-900 dark:text-cyan-100"
                    chipClass="border-cyan-200/80 bg-white/80 text-cyan-900 dark:border-cyan-800 dark:bg-gray-900/50 dark:text-cyan-100"
                    svgAccent="#0891b2"
                />
                <EyePanel
                    side="os"
                    title="چشم چپ"
                    subtitle="Oculus Sinister (OS)"
                    visualExamination={visualExamination}
                    refraction={refraction}
                    accentClass="border-violet-200 bg-gradient-to-br from-violet-50 to-white text-violet-900 dark:border-violet-900 dark:from-violet-950/40 dark:to-gray-900 dark:text-violet-100"
                    chipClass="border-violet-200/80 bg-white/80 text-violet-900 dark:border-violet-800 dark:bg-gray-900/50 dark:text-violet-100"
                    svgAccent="#7c3aed"
                />
            </div>

            <div className="grid gap-3 sm:grid-cols-3">
                {extras.map(([key, label, value]) => (
                    <div
                        key={key}
                        className="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800/70"
                    >
                        <div className="text-xs font-medium text-gray-500 dark:text-gray-400">{label}</div>
                        <div className="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{value}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}
