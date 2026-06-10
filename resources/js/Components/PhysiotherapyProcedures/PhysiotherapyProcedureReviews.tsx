import { Badge, Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, Spinner, Textarea } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import TableActionButton from '../ui/TableActionButton';
import { TableActions } from '../ui/TableActions';
import {
    PhysiotherapyProcedureReview,
    PhysiotherapyProcedureShowPermissions,
    PhysiotherapyProcedureStatus,
} from '../../types/physiotherapyProcedure';
import PhysiotherapyProcedureStatusBadge from './PhysiotherapyProcedureStatusBadge';

interface PhysiotherapyProcedureReviewsProps {
    reviews: PhysiotherapyProcedureReview[];
    permissions: PhysiotherapyProcedureShowPermissions;
    processing: boolean;
    onCreate: (payload: { description: string; status: PhysiotherapyProcedureStatus; days_count: number }) => void;
    onUpdate: (
        reviewId: number,
        payload: { description: string; status: PhysiotherapyProcedureStatus; days_count: number },
    ) => void;
    onDelete: (reviewId: number) => void;
}

const EMPTY_FORM = {
    description: '',
    status: 'pending' as PhysiotherapyProcedureStatus,
    days_count: '0',
};

export default function PhysiotherapyProcedureReviews({
    reviews,
    permissions,
    processing,
    onCreate,
    onUpdate,
    onDelete,
}: PhysiotherapyProcedureReviewsProps) {
    const { t } = useTranslation();
    const [formOpen, setFormOpen] = useState(false);
    const [editingReview, setEditingReview] = useState<PhysiotherapyProcedureReview | null>(null);
    const [form, setForm] = useState(EMPTY_FORM);

    const openCreate = () => {
        setEditingReview(null);
        setForm(EMPTY_FORM);
        setFormOpen(true);
    };

    const openEdit = (review: PhysiotherapyProcedureReview) => {
        setEditingReview(review);
        setForm({
            description: review.description,
            status: review.status,
            days_count: String(review.days_count ?? 0),
        });
        setFormOpen(true);
    };

    const closeForm = () => {
        setFormOpen(false);
        setEditingReview(null);
        setForm(EMPTY_FORM);
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        const payload = {
            description: form.description,
            status: form.status,
            days_count: Number(form.days_count) || 0,
        };

        if (editingReview) {
            onUpdate(editingReview.id, payload);
        } else {
            onCreate(payload);
        }
        closeForm();
    };

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between gap-3">
                <h3 className="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                    <i className="bx bx-message-square-dots text-cyan-500" />
                    {t('global.procedure_reviews')}
                </h3>
                {permissions.addReview && (
                    <Button size="sm" color="blue" onClick={openCreate}>
                        <i className="bx bx-plus me-2" />
                        {t('global.add_review')}
                    </Button>
                )}
            </div>

            {reviews.length === 0 ? (
                <div className="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800/40">
                    <i className="bx bx-message-square-dots mb-2 text-3xl" />
                    <p>{t('global.no_reviews_found')}</p>
                </div>
            ) : (
                <div className="space-y-3">
                    {reviews.map((review) => (
                        <div
                            key={review.id}
                            className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800/40"
                        >
                            <div className="mb-3 flex flex-wrap items-start justify-between gap-2">
                                <div className="flex flex-wrap items-center gap-2">
                                    <PhysiotherapyProcedureStatusBadge status={review.status} />
                                    <span className="text-xs text-gray-500">{review.created_at}</span>
                                </div>
                                {(permissions.editReview || permissions.deleteReview) && (
                                    <TableActions className="flex gap-1">
                                        <TableActionButton
                                            kind="edit"
                                            permission={permissions.editReview}
                                            onClick={() => openEdit(review)}
                                        />
                                        <TableActionButton
                                            kind="delete"
                                            permission={permissions.deleteReview}
                                            confirm={t('global.confirm_delete')}
                                            onClick={() => onDelete(review.id)}
                                        />
                                    </TableActions>
                                )}
                            </div>
                            <p className="text-sm text-gray-700 dark:text-gray-300">{review.description}</p>
                            <div className="mt-3 flex flex-wrap gap-3 text-xs text-gray-500">
                                <span>
                                    {t('global.created_by')}: {review.created_by_name ?? '—'}
                                </span>
                                {review.days_count > 0 && (
                                    <Badge color="info">
                                        {review.days_count} {t('global.days')}
                                    </Badge>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}

            <Modal show={formOpen} onClose={closeForm} size="lg">
                <ModalHeader>
                    {editingReview ? t('global.edit_review') : t('global.add_review')}
                </ModalHeader>
                <form onSubmit={handleSubmit}>
                    <ModalBody className="space-y-4">
                        <div>
                            <Label>{t('global.description')}</Label>
                            <Textarea
                                rows={4}
                                value={form.description}
                                onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))}
                                required
                            />
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label>{t('global.status')}</Label>
                                <select
                                    className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    value={form.status}
                                    onChange={(event) =>
                                        setForm((current) => ({
                                            ...current,
                                            status: event.target.value as PhysiotherapyProcedureStatus,
                                        }))
                                    }
                                >
                                    <option value="pending">{t('global.status_pending')}</option>
                                    <option value="in_progress">{t('global.status_in_progress')}</option>
                                    <option value="completed">{t('global.status_completed')}</option>
                                    <option value="cancelled">{t('global.status_cancelled')}</option>
                                </select>
                            </div>
                            <div>
                                <Label>{t('global.days_count')}</Label>
                                <input
                                    type="number"
                                    min={0}
                                    className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    value={form.days_count}
                                    onChange={(event) =>
                                        setForm((current) => ({ ...current, days_count: event.target.value }))
                                    }
                                />
                            </div>
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" type="button" onClick={closeForm} disabled={processing}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="blue" disabled={processing}>
                            {processing ? <Spinner size="sm" /> : t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>
        </div>
    );
}
