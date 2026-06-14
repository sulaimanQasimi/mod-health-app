import { Head } from '@inertiajs/react';
import { Badge, Button, Card, ToggleSwitch } from 'flowbite-react';
import { ReactNode, useMemo, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import BackLink from '../../Components/ui/BackLink';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface ActivityDetail {
    id: number;
    description: string;
    event: string;
    event_label: string;
    log_name: string;
    subject_type: string | null;
    subject_type_full: string | null;
    subject_id: number | null;
    causer: { id: number; name: string; email: string } | null;
    created_at: string | null;
    properties: Record<string, unknown>;
}

type ChangeStatus = 'added' | 'removed' | 'changed' | 'unchanged';

interface ChangeRow {
    key: string;
    oldValue: unknown;
    newValue: unknown;
    status: ChangeStatus;
}

interface EventStyle {
    icon: string;
    gradient: string;
    glow: string;
    badge: 'success' | 'info' | 'failure' | 'warning' | 'purple';
}

const EVENT_STYLES: Record<string, EventStyle> = {
    created: {
        icon: 'bx-plus-circle',
        gradient: 'from-emerald-500 to-teal-600',
        glow: 'shadow-emerald-500/25',
        badge: 'success',
    },
    updated: {
        icon: 'bx-edit-alt',
        gradient: 'from-blue-500 to-indigo-600',
        glow: 'shadow-blue-500/25',
        badge: 'info',
    },
    deleted: {
        icon: 'bx-trash',
        gradient: 'from-rose-500 to-red-600',
        glow: 'shadow-rose-500/25',
        badge: 'failure',
    },
    restored: {
        icon: 'bx-reset',
        gradient: 'from-amber-500 to-orange-600',
        glow: 'shadow-amber-500/25',
        badge: 'warning',
    },
};

const DEFAULT_EVENT_STYLE: EventStyle = {
    icon: 'bx-history',
    gradient: 'from-violet-500 to-purple-600',
    glow: 'shadow-violet-500/25',
    badge: 'purple',
};

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

function formatValue(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (typeof value === 'boolean') {
        return value ? 'true' : 'false';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value, null, 2);
    }

    return String(value);
}

function buildChangeRows(event: string, properties: Record<string, unknown>): ChangeRow[] {
    const old = (properties.old as Record<string, unknown> | undefined) ?? {};
    const attributes = (properties.attributes as Record<string, unknown> | undefined) ?? {};

    if (event === 'created') {
        return Object.entries(attributes)
            .sort(([a], [b]) => a.localeCompare(b))
            .map(([key, newValue]) => ({
                key,
                oldValue: null,
                newValue,
                status: 'added' as const,
            }));
    }

    if (event === 'deleted') {
        return Object.entries(old)
            .sort(([a], [b]) => a.localeCompare(b))
            .map(([key, oldValue]) => ({
                key,
                oldValue,
                newValue: null,
                status: 'removed' as const,
            }));
    }

    const keys = new Set([...Object.keys(old), ...Object.keys(attributes)]);

    return Array.from(keys)
        .sort()
        .map((key) => {
            const oldValue = old[key] ?? null;
            const newValue = attributes[key] ?? null;
            const hasOld = key in old;
            const hasNew = key in attributes;

            let status: ChangeStatus = 'unchanged';
            if (!hasOld && hasNew) {
                status = 'added';
            } else if (hasOld && !hasNew) {
                status = 'removed';
            } else if (JSON.stringify(oldValue) !== JSON.stringify(newValue)) {
                status = 'changed';
            }

            return { key, oldValue, newValue, status };
        });
}

function eventStyle(event: string): EventStyle {
    return EVENT_STYLES[event] ?? DEFAULT_EVENT_STYLE;
}

export default function ActivityLogsShow({
    activity,
    urls,
}: {
    activity: ActivityDetail;
    urls: { index: string };
}) {
    const { t } = useTranslation();
    const [showAllFields, setShowAllFields] = useState(false);
    const [rawOpen, setRawOpen] = useState(false);
    const [copied, setCopied] = useState(false);

    const style = eventStyle(activity.event);

    const changeRows = useMemo(
        () => buildChangeRows(activity.event, activity.properties ?? {}),
        [activity.event, activity.properties],
    );

    const visibleRows = useMemo(
        () => (showAllFields ? changeRows : changeRows.filter((row) => row.status !== 'unchanged')),
        [changeRows, showAllFields],
    );

    const changeCount = changeRows.filter((row) => row.status !== 'unchanged').length;

    const handleCopyJson = async () => {
        try {
            await navigator.clipboard.writeText(JSON.stringify(activity.properties ?? {}, null, 2));
            setCopied(true);
            window.setTimeout(() => setCopied(false), 2000);
        } catch {
            setCopied(false);
        }
    };

    return (
        <DashboardLayout>
            <Head title={`${t('activity_log.details')} #${activity.id}`} />

            <div className={mergeClasses('mx-auto space-y-6', SETTINGS_INDEX_WIDTH.wide)}>
                <BackLink href={urls.index}>{t('global.back')}</BackLink>

                <Card className="overflow-hidden border-0 shadow-lg">
                    <div
                        className={mergeClasses(
                            'relative bg-gradient-to-br px-6 py-8 text-white sm:px-8',
                            style.gradient,
                        )}
                    >
                        <div className="pointer-events-none absolute -end-8 -top-8 h-40 w-40 rounded-full bg-white/10 blur-2xl" />
                        <div className="pointer-events-none absolute -bottom-10 start-1/3 h-32 w-32 rounded-full bg-black/10 blur-2xl" />

                        <div className="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div className="flex min-w-0 items-start gap-4">
                                <div
                                    className={mergeClasses(
                                        'flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/20 shadow-xl backdrop-blur-sm',
                                        style.glow,
                                    )}
                                >
                                    <i className={`bx ${style.icon} text-3xl`} />
                                </div>
                                <div className="min-w-0">
                                    <div className="mb-2 flex flex-wrap items-center gap-2">
                                        <Badge color={style.badge} className="w-fit">
                                            {activity.event_label}
                                        </Badge>
                                        <span className="rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium">
                                            #{activity.id}
                                        </span>
                                    </div>
                                    <h1 className="text-xl font-bold leading-relaxed sm:text-2xl">
                                        {activity.description}
                                    </h1>
                                    <p className="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-white/85">
                                        <span className="inline-flex items-center gap-1.5">
                                            <i className="bx bx-time-five text-base" />
                                            {activity.created_at ?? '—'}
                                        </span>
                                        {activity.log_name && (
                                            <span className="inline-flex items-center gap-1.5">
                                                <i className="bx bx-bookmark text-base" />
                                                {activity.log_name}
                                            </span>
                                        )}
                                    </p>
                                </div>
                            </div>

                            <div className="grid shrink-0 grid-cols-2 gap-3 sm:grid-cols-3 lg:max-w-md">
                                <HeroStat label={t('activity_log.changed_fields')} value={String(changeCount)} />
                                <HeroStat
                                    label={t('activity_log.subject')}
                                    value={activity.subject_type ? `#${activity.subject_id}` : '—'}
                                />
                                <HeroStat
                                    label={t('activity_log.causer')}
                                    value={activity.causer?.name?.split(' ')[0] ?? t('activity_log.system')}
                                    className="col-span-2 sm:col-span-1"
                                />
                            </div>
                        </div>
                    </div>
                </Card>

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        <Card className="shadow-sm">
                            <div className="mb-5 flex flex-col gap-4 border-b border-gray-100 pb-5 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                        {t('activity_log.changed_fields')}
                                    </h2>
                                    <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                        {t('activity_log.fields_count').replace(':count', String(changeCount))}
                                    </p>
                                </div>
                                <div className="flex items-center gap-3">
                                    <span className="text-sm text-gray-600 dark:text-gray-400">
                                        {showAllFields
                                            ? t('activity_log.show_all_fields')
                                            : t('activity_log.show_changes_only')}
                                    </span>
                                    <ToggleSwitch checked={showAllFields} onChange={setShowAllFields} />
                                </div>
                            </div>

                            {visibleRows.length === 0 ? (
                                <EmptyChanges message={t('activity_log.no_field_changes')} />
                            ) : (
                                <Table embedded>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>{t('activity_log.field_name')}</TableHeader>
                                            <TableHeader>{t('activity_log.value_before')}</TableHeader>
                                            <TableHeader>{t('activity_log.value_after')}</TableHeader>
                                            <TableHeader align="center">{t('activity_log.status')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {visibleRows.map((row) => (
                                            <TableRow key={row.key}>
                                                <TableCell>
                                                    <code className="rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-violet-700 dark:bg-gray-700 dark:text-violet-300">
                                                        {row.key}
                                                    </code>
                                                </TableCell>
                                                <TableCell className="max-w-xs">
                                                    <ValueCell value={row.oldValue} variant="old" />
                                                </TableCell>
                                                <TableCell className="max-w-xs">
                                                    <ValueCell value={row.newValue} variant="new" />
                                                </TableCell>
                                                <TableCell align="center">
                                                    <StatusBadge status={row.status} t={t} />
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            )}
                        </Card>

                        <Card className="shadow-sm">
                            <button
                                type="button"
                                onClick={() => setRawOpen((open) => !open)}
                                className="flex w-full items-center justify-between gap-3 text-start"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700">
                                        <i className="bx bx-code-alt text-xl text-gray-600 dark:text-gray-300" />
                                    </div>
                                    <div>
                                        <h2 className="font-semibold text-gray-900 dark:text-white">
                                            {t('activity_log.raw_data')}
                                        </h2>
                                        <p className="text-sm text-gray-500 dark:text-gray-400">
                                            {t('activity_log.technical_details')}
                                        </p>
                                    </div>
                                </div>
                                <i
                                    className={mergeClasses(
                                        'bx bx-chevron-down text-2xl text-gray-400 transition-transform',
                                        rawOpen && 'rotate-180',
                                    )}
                                />
                            </button>

                            {rawOpen && (
                                <div className="mt-5 border-t border-gray-100 pt-5 dark:border-gray-700">
                                    <div className="mb-3 flex justify-end">
                                        <Button size="xs" color="light" onClick={handleCopyJson}>
                                            <i className="bx bx-copy me-1.5" />
                                            {copied ? t('activity_log.copied') : t('activity_log.copy_json')}
                                        </Button>
                                    </div>
                                    <pre className="max-h-96 overflow-auto rounded-xl bg-gray-950 p-4 text-xs leading-relaxed text-emerald-300">
                                        {JSON.stringify(activity.properties ?? {}, null, 2)}
                                    </pre>
                                </div>
                            )}
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <SidebarCard
                            title={t('activity_log.performed_by')}
                            icon="bx-user-circle"
                            accent="from-sky-500 to-blue-600"
                        >
                            {activity.causer ? (
                                <div className="flex items-start gap-3">
                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-blue-600 text-lg font-bold text-white">
                                        {activity.causer.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div className="min-w-0">
                                        <p className="font-semibold text-gray-900 dark:text-white">
                                            {activity.causer.name}
                                        </p>
                                        <p className="mt-0.5 truncate text-sm text-gray-500 dark:text-gray-400">
                                            {activity.causer.email}
                                        </p>
                                        <p className="mt-1 text-xs text-gray-400">ID: {activity.causer.id}</p>
                                    </div>
                                </div>
                            ) : (
                                <div className="flex items-center gap-3 rounded-xl bg-gray-50 p-3 dark:bg-gray-800/60">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                                        <i className="bx bx-cog text-xl text-gray-500" />
                                    </div>
                                    <p className="font-medium text-gray-700 dark:text-gray-300">
                                        {t('activity_log.system')}
                                    </p>
                                </div>
                            )}
                        </SidebarCard>

                        <SidebarCard
                            title={t('activity_log.target_record')}
                            icon="bx-data"
                            accent="from-violet-500 to-purple-600"
                        >
                            <dl className="space-y-3 text-sm">
                                <MetaItem label={t('activity_log.subject_type')} value={activity.subject_type ?? '—'} />
                                <MetaItem
                                    label={t('activity_log.subject_id')}
                                    value={activity.subject_id != null ? String(activity.subject_id) : '—'}
                                />
                                <MetaItem
                                    label={t('activity_log.technical_details')}
                                    value={
                                        activity.subject_type_full ? (
                                            <code className="block break-all text-xs text-gray-600 dark:text-gray-400">
                                                {activity.subject_type_full}
                                            </code>
                                        ) : (
                                            '—'
                                        )
                                    }
                                />
                            </dl>
                        </SidebarCard>

                        <SidebarCard title={t('activity_log.details')} icon="bx-info-circle" accent="from-slate-500 to-gray-600">
                            <dl className="space-y-3 text-sm">
                                <MetaItem label={t('activity_log.activity_id')} value={`#${activity.id}`} />
                                <MetaItem label={t('activity_log.event')} value={activity.event_label} />
                                <MetaItem label={t('activity_log.log_name')} value={activity.log_name || '—'} />
                                <MetaItem label={t('activity_log.created_at')} value={activity.created_at ?? '—'} />
                            </dl>
                        </SidebarCard>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}

function HeroStat({
    label,
    value,
    className,
}: {
    label: string;
    value: string;
    className?: string;
}) {
    return (
        <div
            className={mergeClasses(
                'rounded-xl border border-white/20 bg-white/15 px-3 py-2.5 backdrop-blur-sm',
                className,
            )}
        >
            <p className="text-xs text-white/75">{label}</p>
            <p className="mt-0.5 truncate text-lg font-bold">{value}</p>
        </div>
    );
}

function SidebarCard({
    title,
    icon,
    accent,
    children,
}: {
    title: string;
    icon: string;
    accent: string;
    children: ReactNode;
}) {
    return (
        <Card className="shadow-sm">
            <div className="mb-4 flex items-center gap-3">
                <div
                    className={mergeClasses(
                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-md',
                        accent,
                    )}
                >
                    <i className={`bx ${icon} text-lg`} />
                </div>
                <h2 className="font-semibold text-gray-900 dark:text-white">{title}</h2>
            </div>
            {children}
        </Card>
    );
}

function MetaItem({ label, value }: { label: string; value: ReactNode }) {
    return (
        <div>
            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {label}
            </dt>
            <dd className="mt-1 font-medium text-gray-900 dark:text-white">{value}</dd>
        </div>
    );
}

function ValueCell({ value, variant }: { value: unknown; variant: 'old' | 'new' }) {
    const formatted = formatValue(value);
    const isEmpty = formatted === '—';

    return (
        <div
            className={mergeClasses(
                'rounded-lg px-2.5 py-2 text-xs',
                isEmpty && 'text-gray-400',
                !isEmpty && variant === 'old' && 'bg-rose-50 text-rose-900 dark:bg-rose-950/40 dark:text-rose-200',
                !isEmpty && variant === 'new' && 'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200',
            )}
        >
            <pre className="whitespace-pre-wrap break-words font-mono">{formatted}</pre>
        </div>
    );
}

function StatusBadge({ status, t }: { status: ChangeStatus; t: (key: string) => string }) {
    const config: Record<ChangeStatus, { color: 'success' | 'failure' | 'warning' | 'gray'; label: string }> = {
        added: { color: 'success', label: t('activity_log.status_added') },
        removed: { color: 'failure', label: t('activity_log.status_removed') },
        changed: { color: 'warning', label: t('activity_log.status_changed') },
        unchanged: { color: 'gray', label: t('activity_log.status_unchanged') },
    };

    const { color, label } = config[status];

    return (
        <Badge color={color} className="w-fit">
            {label}
        </Badge>
    );
}

function EmptyChanges({ message }: { message: string }) {
    return (
        <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 py-14 text-center dark:border-gray-700">
            <div className="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                <i className="bx bx-check-circle text-2xl text-gray-400" />
            </div>
            <p className="text-sm text-gray-500 dark:text-gray-400">{message}</p>
        </div>
    );
}
