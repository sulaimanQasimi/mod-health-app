import { router } from '@inertiajs/react';
import { Alert, Button, Label, Modal, ModalBody, ModalFooter, ModalHeader, TextInput } from 'flowbite-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import TableActionButton from '../ui/TableActionButton';
import { TableActionsCell } from '../ui/TableActions';
import SettingsEmptyState from '../Settings/SettingsEmptyState';
import { useTranslation } from '../../hooks/useTranslation';

export interface LabCategoryItem {
    id: number;
    name: string;
    lab_types_count: number;
}

interface LabCategoriesModalProps {
    show: boolean;
    onClose: () => void;
    categories: LabCategoryItem[];
    canManage: boolean;
    urls: {
        store: string;
        update: string;
        destroy: string;
    };
}

type FormMode = 'create' | 'edit' | null;

export default function LabCategoriesModal({
    show,
    onClose,
    categories,
    canManage,
    urls,
}: LabCategoriesModalProps) {
    const { t } = useTranslation();
    const [search, setSearch] = useState('');
    const [formMode, setFormMode] = useState<FormMode>(null);
    const [editingId, setEditingId] = useState<number | null>(null);
    const [name, setName] = useState('');
    const [processing, setProcessing] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    useEffect(() => {
        if (!show) {
            setSearch('');
            setFormMode(null);
            setEditingId(null);
            setName('');
            setProcessing(false);
            setDeletingId(null);
        }
    }, [show]);

    const filteredCategories = useMemo(() => {
        const query = search.trim().toLowerCase();
        if (!query) {
            return categories;
        }

        return categories.filter((category) => category.name.toLowerCase().includes(query));
    }, [categories, search]);

    const resetForm = () => {
        setFormMode(null);
        setEditingId(null);
        setName('');
    };

    const openCreateForm = () => {
        resetForm();
        setFormMode('create');
    };

    const openEditForm = (category: LabCategoryItem) => {
        setFormMode('edit');
        setEditingId(category.id);
        setName(category.name);
    };

    const reloadCategories = () => {
        router.reload({
            only: ['categories', 'filterOptions'],
            preserveScroll: true,
            preserveState: true,
        });
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (!canManage || !name.trim()) {
            return;
        }

        setProcessing(true);

        if (formMode === 'edit' && editingId) {
            router.put(
                `${urls.update}/${editingId}`,
                { name: name.trim() },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        resetForm();
                        reloadCategories();
                    },
                    onFinish: () => setProcessing(false),
                },
            );
            return;
        }

        router.post(
            urls.store,
            { name: name.trim() },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    resetForm();
                    reloadCategories();
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    const handleDelete = (category: LabCategoryItem) => {
        if (!canManage || category.lab_types_count > 0) {
            return;
        }

        setDeletingId(category.id);
        router.delete(`${urls.destroy}/${category.id}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => reloadCategories(),
            onFinish: () => setDeletingId(null),
        });
    };

    return (
        <Modal show={show} onClose={() => !processing && onClose()} size="3xl">
            <ModalHeader className="border-b border-gray-200 dark:border-gray-700">
                <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow">
                        <i className="bx bx-category text-xl" />
                    </div>
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.categories')}
                        </h3>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            {t('global.manage_lab_test_categories') || t('global.lab_types')}
                        </p>
                    </div>
                </div>
            </ModalHeader>

            <ModalBody className="space-y-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div className="flex-1">
                        <Label htmlFor="category-search">{t('global.search')}</Label>
                        <TextInput
                            id="category-search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={t('global.search_by_name')}
                        />
                    </div>
                    {canManage && (
                        <Button color="blue" onClick={openCreateForm} disabled={formMode === 'create'}>
                            <i className="bx bx-plus me-2" />
                            {t('global.create_category')}
                        </Button>
                    )}
                </div>

                {canManage && formMode && (
                    <form
                        onSubmit={handleSubmit}
                        className="rounded-lg border border-violet-200 bg-violet-50/60 p-4 dark:border-violet-900 dark:bg-violet-950/20"
                    >
                        <h4 className="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                            {formMode === 'edit'
                                ? t('global.edit_category')
                                : t('global.create_category')}
                        </h4>
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div className="flex-1">
                                <Label htmlFor="category-name">{t('global.name')}</Label>
                                <TextInput
                                    id="category-name"
                                    value={name}
                                    onChange={(event) => setName(event.target.value)}
                                    required
                                    autoFocus
                                />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" color="blue" disabled={processing}>
                                    {formMode === 'edit' ? t('global.update') : t('global.create')}
                                </Button>
                                <Button type="button" color="light" onClick={resetForm} disabled={processing}>
                                    {t('global.cancel')}
                                </Button>
                            </div>
                        </div>
                    </form>
                )}

                {filteredCategories.length > 0 ? (
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>#</TableHeader>
                                <TableHeader>{t('global.name')}</TableHeader>
                                <TableHeader>{t('global.lab_types')}</TableHeader>
                                {canManage && (
                                    <TableHeader align="center">{t('global.actions')}</TableHeader>
                                )}
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {filteredCategories.map((category, index) => (
                                <TableRow key={category.id}>
                                    <TableCell>{index + 1}</TableCell>
                                    <TableCell className="font-medium text-gray-900 dark:text-white">
                                        {category.name}
                                    </TableCell>
                                    <TableCell muted>{category.lab_types_count}</TableCell>
                                    {canManage && (
                                        <TableActionsCell>
                                            <TableActionButton
                                                kind="edit"
                                                permission
                                                onClick={() => openEditForm(category)}
                                            />
                                            <TableActionButton
                                                kind="delete"
                                                permission
                                                disabled={
                                                    deletingId === category.id ||
                                                    category.lab_types_count > 0
                                                }
                                                confirm={t('global.are_you_sure')}
                                                onClick={() => handleDelete(category)}
                                            />
                                        </TableActionsCell>
                                    )}
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                ) : (
                    <SettingsEmptyState
                        message={
                            search
                                ? t('global.no_results_found')
                                : t('global.no_categories_found')
                        }
                    />
                )}

                {canManage && (
                    <Alert color="info" className="text-sm">
                        {t('global.category_delete_hint') ||
                            'Categories linked to lab types cannot be deleted.'}
                    </Alert>
                )}
            </ModalBody>

            <ModalFooter className="border-t border-gray-200 dark:border-gray-700">
                <Button color="light" onClick={onClose} disabled={processing}>
                    {t('global.close')}
                </Button>
            </ModalFooter>
        </Modal>
    );
}
