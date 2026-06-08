import { Head, router } from '@inertiajs/react';
import { Alert, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

interface ScanCodeProps {
    urls: { scan: string; index: string };
    error?: string | null;
}

export default function ScanCode({ urls, error }: ScanCodeProps) {
    const { t } = useTranslation();
    const [qrCodeData, setQrCodeData] = useState('');
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!qrCodeData.trim()) return;

        setProcessing(true);
        router.post(
            urls.scan,
            { qrCodeData: qrCodeData.trim() },
            { onFinish: () => setProcessing(false) },
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.scan_prescription')} />
            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.scan_prescription')}
                        subtitle={t('global.please_scan_prescription')}
                        icon="bx-qr-scan"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.index}
                        backLabel={t('global.back')}
                    />

                    {error && (
                        <Alert color="failure" className="mb-4">
                            {error}
                        </Alert>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <Label htmlFor="qrCodeData">{t('global.qr_code_data')}</Label>
                            <TextInput
                                id="qrCodeData"
                                value={qrCodeData}
                                onChange={(e) => setQrCodeData(e.target.value)}
                                placeholder={t('global.scan_prescription')}
                                autoFocus
                                required
                            />
                        </div>
                        <Button type="submit" color="blue" disabled={processing}>
                            {t('global.search')}
                        </Button>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
