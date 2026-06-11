import OperationReferralSection from '../../Operations/OperationReferralSection';

interface OperationSectionProps {
    appointmentId: number;
}

export default function OperationSection({ appointmentId }: OperationSectionProps) {
    return (
        <OperationReferralSection
            baseUrl={`/react/appointments/${appointmentId}/operations`}
            accordionId={`operations-${appointmentId}`}
        />
    );
}
