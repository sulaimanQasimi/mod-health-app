import { Alert } from 'flowbite-react';

interface LaboratoryFlashAlertsProps {
    flash?: {
        success?: string | null;
        error?: string | null;
    };
}

export default function LaboratoryFlashAlerts({ flash }: LaboratoryFlashAlertsProps) {
    if (!flash?.success && !flash?.error) {
        return null;
    }

    return (
        <>
            {flash.success && (
                <Alert color="success" className="mb-4">
                    {flash.success}
                </Alert>
            )}
            {flash.error && (
                <Alert color="failure" className="mb-4">
                    {flash.error}
                </Alert>
            )}
        </>
    );
}
