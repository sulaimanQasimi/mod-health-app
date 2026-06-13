import { router } from '@inertiajs/react';
import { Badge, Card } from 'flowbite-react';
import { useState } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import { TableActionsCell } from '../ui/TableActions';
import LaboratoryPriorityBadge from './LaboratoryPriorityBadge';
import LaboratoryRegistrationActions from './LaboratoryRegistrationActions';
import LaboratoryStatusBadge from './LaboratoryStatusBadge';
import { LaboratoryPatientGroup } from '../../types/laboratory';
import { useTranslation } from '../../hooks/useTranslation';

interface LaboratoryPatientAccordionProps {
    patients: LaboratoryPatientGroup[];
    listMode: 'pending' | 'in_progress' | 'completed';
}

export default function LaboratoryPatientAccordion({
    patients,
    listMode,
}: LaboratoryPatientAccordionProps) {
    const { t } = useTranslation();
    const [expanded, setExpanded] = useState<Record<number, boolean>>({});

    const toggle = (patientId: number) => {
        setExpanded((current) => ({ ...current, [patientId]: !current[patientId] }));
    };

    const acceptAllPending = (group: LaboratoryPatientGroup) => {
        const pending = group.registrations.filter((r) => r.permissions.accept);
        if (pending.length === 0) {
            return;
        }

        if (!window.confirm(t('global.are_you_sure') || 'Are you sure?')) {
            return;
        }

        pending.forEach((registration, index) => {
            setTimeout(() => {
                router.post(registration.urls.accept, {}, { preserveScroll: true });
            }, index * 200);
        });
    };

    if (patients.length === 0) {
        return (
            <Card className="shadow-sm">
                <div className="flex flex-col items-center justify-center py-16 text-center">
                    <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <i className="bx bx-test-tube text-3xl text-gray-400" />
                    </div>
                    <p className="text-gray-500 dark:text-gray-400">{t('global.no_item_is_found')}</p>
                </div>
            </Card>
        );
    }

    return (
        <div className="space-y-4">
            {patients.map((group) => {
                const isOpen = expanded[group.patient_id] ?? listMode !== 'completed';

                return (
                    <Card key={group.patient_id} className="overflow-hidden shadow-sm">
                        <button
                            type="button"
                            onClick={() => toggle(group.patient_id)}
                            className="flex w-full items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-teal-50/80 to-cyan-50/50 p-4 text-left transition hover:from-teal-50 dark:border-gray-700 dark:from-teal-950/20 dark:to-cyan-950/10"
                        >
                            <div className="flex min-w-0 items-center gap-4">
                                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-cyan-600 text-white shadow">
                                    <i className="bx bx-user text-xl" />
                                </div>
                                <div className="min-w-0">
                                    <h3 className="truncate font-semibold text-gray-900 dark:text-white">
                                        {group.patient_name}
                                    </h3>
                                    <p className="mt-0.5 truncate text-sm text-gray-500 dark:text-gray-400">
                                        {group.father_name && (
                                            <span>
                                                <i className="bx bx-user me-1" />
                                                {group.father_name}
                                            </span>
                                        )}
                                        {group.age != null && (
                                            <span className="ms-2">
                                                <i className="bx bx-calendar me-1" />
                                                {group.age}
                                            </span>
                                        )}
                                        {group.phone && (
                                            <span className="ms-2">
                                                <i className="bx bx-phone me-1" />
                                                {group.phone}
                                            </span>
                                        )}
                                    </p>
                                </div>
                            </div>

                            <div className="flex shrink-0 items-center gap-2">
                                <Badge color="info">
                                    {group.registration_count} {t('global.tests') || 'tests'}
                                </Badge>
                                {group.pending_accept_count > 0 && (
                                    <Badge color="warning">
                                        {group.pending_accept_count} {t('global.pending')}
                                    </Badge>
                                )}
                                <i
                                    className={`bx text-xl text-gray-500 transition-transform ${isOpen ? 'bx-chevron-up' : 'bx-chevron-down'}`}
                                />
                            </div>
                        </button>

                        {isOpen && (
                            <div className="border-t border-gray-100 dark:border-gray-700">
                                {listMode === 'pending' && group.pending_accept_count > 0 && (
                                    <div className="border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                                        <button
                                            type="button"
                                            onClick={() => acceptAllPending(group)}
                                            className="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"
                                        >
                                            <i className="bx bx-check-double" />
                                            {t('global.accept')} ({group.pending_accept_count})
                                        </button>
                                    </div>
                                )}

                                <div className="p-4 pt-3">
                                    <Table embedded>
                                        <TableHead>
                                            <TableRow variant="header">
                                                <TableHeader>{t('global.reference_number')}</TableHeader>
                                                <TableHeader>{t('global.test_type')}</TableHeader>
                                                <TableHeader>{t('global.department')}</TableHeader>
                                                <TableHeader>{t('global.status')}</TableHeader>
                                                <TableHeader>{t('global.priority')}</TableHeader>
                                                <TableHeader>{t('global.doctor')}</TableHeader>
                                                <TableHeader>{t('global.date')}</TableHeader>
                                                <TableHeader align="center">
                                                    {t('global.actions')}
                                                </TableHeader>
                                            </TableRow>
                                        </TableHead>
                                        <TableBody>
                                            {group.registrations.map((registration) => (
                                                <TableRow key={registration.id}>
                                                    <TableCell className="whitespace-nowrap font-mono text-sm">
                                                        {registration.ref_no}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="font-medium text-gray-900 dark:text-white">
                                                            {registration.lab_type_name ?? '—'}
                                                        </div>
                                                        {registration.category_name && (
                                                            <div className="text-xs text-gray-500 dark:text-gray-400">
                                                                {registration.category_name}
                                                            </div>
                                                        )}
                                                    </TableCell>
                                                    <TableCell muted>
                                                        {registration.department_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell>
                                                        <LaboratoryStatusBadge
                                                            status={registration.status}
                                                        />
                                                    </TableCell>
                                                    <TableCell>
                                                        <LaboratoryPriorityBadge
                                                            priority={registration.priority}
                                                        />
                                                    </TableCell>
                                                    <TableCell muted>
                                                        {registration.doctor_name ?? '—'}
                                                    </TableCell>
                                                    <TableCell muted className="whitespace-nowrap">
                                                        {registration.date ??
                                                            registration.registration_date ??
                                                            '—'}
                                                    </TableCell>
                                                    <TableActionsCell>
                                                        <LaboratoryRegistrationActions
                                                            registration={registration}
                                                        />
                                                    </TableActionsCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>
                        )}
                    </Card>
                );
            })}
        </div>
    );
}
