import { Head, router } from '@inertiajs/react';
import { Badge, Card } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import {
    AppointmentActionGroup,
    AppointmentIconLink,
    AppointmentPillButton,
} from '../../Components/Appointments/AppointmentTableActions';
import AppointmentPageHeader from '../../Components/Appointments/AppointmentPageHeader';
import AppointmentPagination from '../../Components/Appointments/AppointmentPagination';
import MyVisitFilters, { MyVisitFilterValues } from '../../Components/Appointments/MyVisitFilters';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import UnderReviewNavTabs from '../../Components/UnderReviews/UnderReviewNavTabs';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    PaginatedUnderReviews,
    UnderReviewWorkflowFilters,
    UnderReviewWorkflowUrls,
} from '../../types/underReview';

interface PendingProps {
    underReviews: PaginatedUnderReviews;
    activeTab: 'pending';
    filters: UnderReviewWorkflowFilters;
    urls: UnderReviewWorkflowUrls;
}

const EMPTY_FILTERS: MyVisitFilterValues = {
    search: '',
    token_id: '',
    patient_id: '',
};

function cleanFilters(filters: MyVisitFilterValues): Record<string, string> {
    return Object.fromEntries(
        Object.entries({
            search: filters.search,
            record_id: filters.token_id,
            patient_id: filters.patient_id,
        }).filter(([, value]) => value !== '')
    );
}

export default function UnderReviewsPending({
    underReviews,
    activeTab,
    filters: serverFilters,
    urls,
}: PendingProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState<MyVisitFilterValues>({
        search: serverFilters.search,
        token_id: serverFilters.record_id,
        patient_id: serverFilters.patient_id,
    });
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setFilters({
            search: serverFilters.search,
            token_id: serverFilters.record_id,
            patient_id: serverFilters.patient_id,
        });
    }, [serverFilters]);

    const applyFilters = useCallback(
        (next: MyVisitFilterValues) => {
            setProcessing(true);
            router.get(urls.pending, cleanFilters(next), {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => setProcessing(false),
            });
        },
        [urls.pending]
    );

    const handleFilterSubmit = (event: FormEvent) => {
        event.preventDefault();
        applyFilters(filters);
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        applyFilters(EMPTY_FILTERS);
    };

    const handleAccept = (underReviewId: number) => {
        if (!window.confirm(t('global.are_you_sure_accept_appointment'))) {
            return;
        }

        router.post(`${urls.accept}/${underReviewId}/accept`, {}, { preserveScroll: true });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.pending')} />

            <div className="mx-auto max-w-[1600px]">
                <Card className="shadow-sm">
                    <AppointmentPageHeader
                        title={t('global.under_review_patients')}
                        subtitle={t('global.pending')}
                        icon="bx-revision"
                    />

                    <UnderReviewNavTabs activeTab={activeTab} urls={urls} />

                    <MyVisitFilters
                        filters={filters}
                        processing={processing}
                        onFilterChange={(field, value) =>
                            setFilters((current) => ({ ...current, [field]: value }))
                        }
                        onSubmit={handleFilterSubmit}
                        onReset={handleReset}
                        showSearch
                    />

                    <Table id="under-reviews-pending-table" className="mt-6">
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>{t('global.id')}</TableHeader>
                                <TableHeader>{t('global.card_number')}</TableHeader>
                                <TableHeader>{t('global.patient_name')}</TableHeader>
                                <TableHeader>{t('global.room')}</TableHeader>
                                <TableHeader>{t('global.bed')}</TableHeader>
                                <TableHeader>{t('global.date')}</TableHeader>
                                <TableHeader>{t('global.status')}</TableHeader>
                                <TableHeader align="center">{t('global.actions')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {underReviews.data.length === 0 ? (
                                <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
                                    <TableCell colSpan={8} align="center" muted className="py-12 text-base">
                                        <div className="flex flex-col items-center gap-2">
                                            <div className="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                <i className="bx bx-user-x text-xl text-gray-400" />
                                            </div>
                                            {t('global.no_records_found')}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ) : (
                                underReviews.data.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell className="font-medium text-gray-900 dark:text-white">
                                            {item.id}
                                        </TableCell>
                                        <TableCell>{item.patient_id_card ?? '—'}</TableCell>
                                        <TableCell className="font-medium text-gray-900 dark:text-white">
                                            {item.patient_name ?? '—'}
                                        </TableCell>
                                        <TableCell muted>{item.room_name ?? '—'}</TableCell>
                                        <TableCell muted>{item.bed_number ?? '—'}</TableCell>
                                        <TableCell muted dir="ltr">
                                            {item.admission_date ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge color="warning">{t('global.pending')}</Badge>
                                        </TableCell>
                                        <TableCell align="center">
                                            <AppointmentActionGroup>
                                                {item.permissions?.accept && (
                                                    <AppointmentPillButton
                                                        label={t('global.accept')}
                                                        variant="accept"
                                                        onClick={() => handleAccept(item.id)}
                                                    />
                                                )}
                                                <AppointmentIconLink
                                                    href={item.urls.show}
                                                    icon="bx-expand"
                                                    title={t('global.view')}
                                                    variant="view"
                                                />
                                            </AppointmentActionGroup>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>

                    <AppointmentPagination
                        links={underReviews.links}
                        meta={underReviews.meta}
                        t={t}
                    />
                </Card>
            </div>
        </DashboardLayout>
    );
}
