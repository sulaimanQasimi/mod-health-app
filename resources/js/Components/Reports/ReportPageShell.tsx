import { Head } from '@inertiajs/react';
import { ReactNode } from 'react';
import DashboardLayout from '../Layout/DashboardLayout';
import SettingsPageHeader from '../Settings/SettingsPageHeader';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ReportPageShellProps {
    title: string;
    subtitle: string;
    icon?: string;
    accent?: string;
    backHref?: string;
    backLabel: string;
    action?: ReactNode;
    children: ReactNode;
    headTitle?: string;
}

export default function ReportPageShell({
    title,
    subtitle,
    icon = 'bx-bar-chart-alt-2',
    accent = 'from-cyan-600 to-blue-700',
    backHref,
    backLabel,
    action,
    children,
    headTitle,
}: ReportPageShellProps) {
    return (
        <DashboardLayout>
            <Head title={headTitle ?? title} />
            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={title}
                    subtitle={subtitle}
                    icon={icon}
                    accent={accent}
                    backHref={backHref}
                    backLabel={backLabel}
                    action={action}
                />
                {children}
            </div>
        </DashboardLayout>
    );
}
