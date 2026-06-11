import BloodFormSegmented, { SegmentOption } from './BloodFormSegmented';
import { BLOOD_UNIT_TEST_RESULT_OPTIONS } from './bloodBankUi';

interface BloodTestResultSegmentedProps {
    value: string;
    onChange: (value: string) => void;
    options?: readonly string[];
}

const RESULT_META: Record<string, Pick<SegmentOption, 'icon' | 'tone'>> = {
    pending: { icon: 'bx-time-five', tone: 'amber' },
    negative: { icon: 'bx-check-circle', tone: 'emerald' },
    positive: { icon: 'bx-x-circle', tone: 'red' },
    inconclusive: { icon: 'bx-help-circle', tone: 'sky' },
};

function formatLabel(value: string): string {
    return value.charAt(0).toUpperCase() + value.slice(1);
}

export default function BloodTestResultSegmented({
    value,
    onChange,
    options = BLOOD_UNIT_TEST_RESULT_OPTIONS,
}: BloodTestResultSegmentedProps) {
    const segmentOptions: SegmentOption[] = options.map((option) => {
        const meta = RESULT_META[option] ?? RESULT_META.pending;

        return {
            value: option,
            label: formatLabel(option),
            icon: meta.icon,
            tone: meta.tone,
        };
    });

    return (
        <BloodFormSegmented
            value={value}
            onChange={onChange}
            options={segmentOptions}
            columns={2}
            size="sm"
            track="neutral"
        />
    );
}
