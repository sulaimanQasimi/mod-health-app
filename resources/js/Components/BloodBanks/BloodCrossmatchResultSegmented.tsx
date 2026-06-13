import BloodFormSegmented, { SegmentOption } from './BloodFormSegmented';

interface BloodCrossmatchResultSegmentedProps {
    value: string;
    onChange: (value: string) => void;
    options: readonly string[];
}

const RESULT_META: Record<string, Pick<SegmentOption, 'icon' | 'tone'>> = {
    pending: { icon: 'bx-time-five', tone: 'amber' },
    compatible: { icon: 'bx-check-circle', tone: 'emerald' },
    incompatible: { icon: 'bx-x-circle', tone: 'red' },
    inconclusive: { icon: 'bx-help-circle', tone: 'sky' },
};

function formatLabel(value: string): string {
    return value.charAt(0).toUpperCase() + value.slice(1);
}

export default function BloodCrossmatchResultSegmented({
    value,
    onChange,
    options,
}: BloodCrossmatchResultSegmentedProps) {
    const segmentOptions: SegmentOption[] = options.map((option) => {
        const meta = RESULT_META[option] ?? RESULT_META.pending;

        return {
            value: option,
            label: formatLabel(option),
            icon: meta.icon,
            tone: meta.tone,
        };
    });

    const columns = (Math.min(Math.max(options.length, 2), 4) as 2 | 3 | 4);

    return (
        <BloodFormSegmented
            value={value}
            onChange={onChange}
            options={segmentOptions}
            columns={columns}
            size="sm"
            track="neutral"
        />
    );
}
