import { ReactNode } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../ui/Table';
import { useTranslation } from '../../../hooks/useTranslation';
import { useAppointmentSection } from '../../../hooks/useAppointmentSection';
import AppointmentSectionAccordion, {
    SectionEmptyState,
    SectionLoadingState,
} from './AppointmentSectionAccordion';

export interface TableColumn<T> {
    key: string;
    header: string;
    render?: (item: T, index: number) => ReactNode;
    muted?: boolean;
}

interface SimpleTableSectionProps<T extends { id?: number | string }> {
    appointmentId: number;
    sectionPath: string;
    accordionId: string;
    icon: string;
    iconClassName?: string;
    title: string;
    badgeColor?: 'info' | 'success' | 'warning' | 'failure' | 'gray';
    emptyMessage: string;
    columns: TableColumn<T>[];
    headerAction?: (ctx: {
        permissions: Record<string, boolean>;
        reload: () => Promise<void>;
    }) => ReactNode;
    rowActions?: (item: T, ctx: {
        permissions: Record<string, boolean>;
        reload: () => Promise<void>;
        destroy: (path: string) => Promise<void>;
    }) => ReactNode;
    footer?: (data: NonNullable<ReturnType<typeof useAppointmentSection>['data']>) => ReactNode;
}

export default function SimpleTableSection<T extends { id?: number | string }>({
    appointmentId,
    sectionPath,
    accordionId,
    icon,
    iconClassName,
    title,
    badgeColor = 'info',
    emptyMessage,
    columns,
    headerAction,
    rowActions,
    footer,
}: SimpleTableSectionProps<T>) {
    const { t } = useTranslation();
    const { loading, data, reload, destroy } = useAppointmentSection<T>(appointmentId, sectionPath);

    return (
        <AppointmentSectionAccordion
            id={accordionId}
            icon={icon}
            iconClassName={iconClassName}
            title={title}
            count={data?.count}
            badgeColor={badgeColor}
        >
            {loading ? (
                <SectionLoadingState />
            ) : (
                <>
                    {headerAction && data && (
                        <div className="mb-4 flex justify-end">
                            {headerAction({ permissions: data.permissions, reload })}
                        </div>
                    )}

                    {data && data.items.length > 0 ? (
                        <Table>
                            <TableHead>
                                <TableRow variant="header">
                                    <TableHeader>{t('global.number')}</TableHeader>
                                    {columns.map((column) => (
                                        <TableHeader key={column.key}>{column.header}</TableHeader>
                                    ))}
                                    {rowActions && (
                                        <TableHeader align="center">{t('global.actions')}</TableHeader>
                                    )}
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={String(item.id ?? index)}>
                                        <TableCell>{index + 1}</TableCell>
                                        {columns.map((column) => (
                                            <TableCell key={column.key} muted={column.muted}>
                                                {column.render
                                                    ? column.render(item, index)
                                                    : String((item as Record<string, unknown>)[column.key] ?? '—')}
                                            </TableCell>
                                        ))}
                                        {rowActions && (
                                            <TableCell align="center">
                                                <div className="flex items-center justify-center gap-1">
                                                    {rowActions(item, {
                                                        permissions: data.permissions,
                                                        reload,
                                                        destroy,
                                                    })}
                                                </div>
                                            </TableCell>
                                        )}
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <SectionEmptyState message={emptyMessage} />
                    )}

                    {footer && data && footer(data)}
                </>
            )}
        </AppointmentSectionAccordion>
    );
}

export function SectionActionButton({
    icon,
    title,
    onClick,
    href,
    colorClass,
}: {
    icon: string;
    title: string;
    onClick?: () => void;
    href?: string;
    colorClass: string;
}) {
    const className = `inline-flex h-8 w-8 items-center justify-center rounded-lg ${colorClass}`;

    if (href) {
        return (
            <a href={href} className={className} title={title}>
                <i className={`bx ${icon} text-lg`} />
            </a>
        );
    }

    return (
        <button type="button" onClick={onClick} className={className} title={title}>
            <i className={`bx ${icon} text-lg`} />
        </button>
    );
}
