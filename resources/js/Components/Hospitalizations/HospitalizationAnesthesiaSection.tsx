import AnesthesiaReferralSection from '../Anesthesias/AnesthesiaReferralSection';

interface HospitalizationAnesthesiaSectionProps {
    hospitalizationId: number;
    isDischarged?: boolean;
}

export default function HospitalizationAnesthesiaSection({
    hospitalizationId,
    isDischarged = false,
}: HospitalizationAnesthesiaSectionProps) {
    return (
        <AnesthesiaReferralSection
            baseUrl={`/react/hospitalizations/${hospitalizationId}/anesthesia`}
            accordionId={`hospitalization-anesthesia-${hospitalizationId}`}
            isDischarged={isDischarged}
            reloadPageOnSuccess
        />
    );
}
