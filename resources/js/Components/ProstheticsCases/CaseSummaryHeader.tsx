import { Badge, Button } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { ProstheticCaseDetail } from '../../types/prosthetics';

interface CaseSummaryHeaderProps {
    prostheticCase: ProstheticCaseDetail;
    printUrl: string;
}

export default function CaseSummaryHeader({ prostheticCase, printUrl }: CaseSummaryHeaderProps) {
    const { t } = useTranslation();

    const fields = [
        { label: t('global.patient_name'), value: prostheticCase.patient_name },
        { label: t('global.id'), value: prostheticCase.patient_id },
        {
            label: t('global.prosthetics_referral'),
            value: prostheticCase.referral?.referral_number,
        },
    ];

    return (
        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div className="grid flex-1 gap-3 sm:grid-cols-3">
                    {fields.map((field) => (
                        <div
                            key={field.label}
                            className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 dark:border-gray-800 dark:bg-gray-800/40"
                        >
                            <p className="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {field.label}
                            </p>
                            <p className="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">
                                {field.value ?? '—'}
                            </p>
                        </div>
                    ))}
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    <Badge color="gray" className="w-fit">
                        {t(`global.prosthetics_case_status_${prostheticCase.status}`)}
                    </Badge>
                    <Button as="a" href={printUrl} color="light" size="sm" target="_blank">
                        <i className="bx bx-printer me-2" />
                        {t('global.prosthetics_print_summary')}
                    </Button>
                </div>
            </div>
            {prostheticCase.referral && (
                <p className="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    {t('global.prosthetics_referral')}:{' '}
                    <span className="font-mono text-gray-700 dark:text-gray-300">
                        {prostheticCase.referral.referral_number}
                    </span>
                </p>
            )}
        </div>
    );
}
