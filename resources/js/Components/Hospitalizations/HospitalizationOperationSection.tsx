import OperationReferralSection from '../Operations/OperationReferralSection';

interface HospitalizationOperationSectionProps {
    hospitalizationId: number;
    isDischarged?: boolean;
}

export default function HospitalizationOperationSection({
    hospitalizationId,
    isDischarged = false,
}: HospitalizationOperationSectionProps) {
    return (
        <OperationReferralSection
            baseUrl={`/react/hospitalizations/${hospitalizationId}/operations`}
            accordionId={`hospitalization-operations-${hospitalizationId}`}
            isDischarged={isDischarged}
            reloadPageOnSuccess
        />
    );
}
