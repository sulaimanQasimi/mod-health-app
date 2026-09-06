import { router } from '@inertiajs/react';
import { useState } from 'react';
import DepartmentReportPanel, {
    DepartmentReportTabData,
} from '../../Components/Reports/DepartmentReportPanel';
import PatientsReportPanel, {
    PatientsReportTabData,
} from '../../Components/Reports/PatientsReportPanel';
import { ReportPageShell } from '../../Components/Reports';
import ReportTabs, { ReportTabId } from '../../Components/Reports/ReportTabs';
import { useTranslation } from '../../hooks/useTranslation';

interface ReportProps {
    tab: ReportTabId;
    permissions: {
        department: boolean;
    };
    patientsTab: PatientsReportTabData | null;
    departmentTab: DepartmentReportTabData | null;
    urls: {
        current: string;
        index: string;
        export: string;
    };
}

const PARTIAL_KEYS = ['tab', 'patientsTab', 'departmentTab', 'permissions', 'urls'] as const;

export default function PatientsReport({
    tab,
    permissions,
    patientsTab,
    departmentTab,
    urls,
}: ReportProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);

    const visit = (params: Record<string, string>, options?: { replace?: boolean }) => {
        setProcessing(true);
        router.get(urls.current, params, {
            only: [...PARTIAL_KEYS],
            preserveScroll: true,
            preserveState: true,
            replace: options?.replace ?? false,
            onFinish: () => setProcessing(false),
        });
    };

    const switchTab = (next: ReportTabId) => {
        if (next === tab || (next === 'department' && !permissions.department)) {
            return;
        }
        visit({ tab: next }, { replace: true });
    };

    return (
        <ReportPageShell
            title={t('global.reports')}
            subtitle={t('global.reception')}
            icon="bx-pie-chart-alt-2"
            accent="from-slate-800 to-cyan-700"
            backHref={urls.index}
            backLabel={t('global.back')}
            headTitle={t('global.reports')}
        >
            <ReportTabs
                active={tab}
                onChange={switchTab}
                trailing={
                    processing ? (
                        <span className="inline-flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                            <span className="h-2 w-2 animate-pulse rounded-full bg-cyan-500" />
                            {t('global.loading')}
                        </span>
                    ) : null
                }
                tabs={[
                    {
                        id: 'patients',
                        label: t('global.patients'),
                        icon: 'bx-user-circle',
                        description: t('global.reports'),
                    },
                    {
                        id: 'department',
                        label: t('global.department_report'),
                        icon: 'bx-buildings',
                        description: t('global.appointments'),
                        disabled: !permissions.department,
                    },
                ]}
            />

            <div className="relative">
                {processing ? (
                    <div className="pointer-events-none absolute inset-0 z-10 rounded-2xl bg-white/40 backdrop-blur-[1px] dark:bg-gray-900/30" />
                ) : null}

                {tab === 'patients' && patientsTab ? (
                    <PatientsReportPanel
                        data={patientsTab}
                        urls={urls}
                        processing={processing}
                        onVisit={visit}
                    />
                ) : null}

                {tab === 'department' && departmentTab ? (
                    <DepartmentReportPanel
                        data={departmentTab}
                        processing={processing}
                        onVisit={visit}
                    />
                ) : null}
            </div>
        </ReportPageShell>
    );
}
