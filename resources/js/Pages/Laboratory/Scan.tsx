import { Head, router } from '@inertiajs/react';
import { Alert, Button, Card, Label, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import LaboratoryPageHeader from '../../Components/Laboratory/LaboratoryPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { LaboratoryNavUrls } from '../../types/laboratory';
import { SETTINGS_FORM_WIDTH } from '../../utils/settingsUi';

interface ScanProps {
    urls: LaboratoryNavUrls & { scanSubmit: string };
    error?: string | null;
}

export default function Scan({ urls, error }: ScanProps) {
    const { t } = useTranslation();
    const [refNo, setRefNo] = useState('');
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!refNo.trim()) {
            return;
        }

        setProcessing(true);
        router.post(
            urls.scanSubmit,
            { ref_no: refNo.trim() },
            { onFinish: () => setProcessing(false) },
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.scan_test')} />

            <LaboratoryPageHeader
                title={t('global.scan_test')}
                subtitle={t('global.please_scan_test')}
                icon="bx-qr-scan"
                accent="from-teal-500 to-cyan-600"
                navUrls={urls}
                activeTab="scan"
            />

            <div className={`mx-auto ${SETTINGS_FORM_WIDTH}`}>
                {error && (
                    <Alert color="failure" className="mb-4">
                        {error}
                    </Alert>
                )}

                <Card className="overflow-hidden shadow-sm">
                    <div className="border-b border-gray-100 bg-gradient-to-br from-teal-50 via-white to-cyan-50 px-6 py-10 text-center dark:border-gray-700 dark:from-teal-950/30 dark:via-gray-800 dark:to-cyan-950/20">
                        <div className="mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-white shadow-md ring-1 ring-teal-100 dark:bg-gray-900 dark:ring-teal-900/50">
                            <i className="bx bx-barcode-reader text-5xl text-teal-600 dark:text-teal-400" />
                        </div>
                        <h2 className="mt-5 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.please_scan_test')}
                        </h2>
                        <p className="mx-auto mt-2 max-w-md text-sm text-gray-500 dark:text-gray-400">
                            {t('global.scan_test_help')}
                        </p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-5 p-6">
                        <div>
                            <Label
                                htmlFor="ref_no"
                                className="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                {t('global.reference_number')}
                            </Label>
                            <TextInput
                                id="ref_no"
                                value={refNo}
                                onChange={(e) => setRefNo(e.target.value)}
                                placeholder={t('global.reference_number')}
                                autoFocus
                                required
                                sizing="lg"
                                className="font-mono tracking-wide"
                            />
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button type="submit" color="blue" disabled={processing}>
                                <i className="bx bx-search me-1.5" />
                                {t('global.search')}
                            </Button>
                            <Button
                                type="button"
                                color="light"
                                disabled={processing}
                                onClick={() => router.get(urls.pending)}
                            >
                                <i className="bx bx-hourglass me-1.5" />
                                {t('global.pending_tests')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
