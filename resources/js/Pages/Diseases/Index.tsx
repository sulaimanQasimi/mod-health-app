import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner, Tabs, TextInput } from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, PaginatedResult, SettingsPermissions } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

interface DiseaseItem {
    id: number;
    name: string;
    description: string | null;
    disease_category_name: string | null;
    department_name: string | null;
}

interface DiseaseCategoryItem {
    id: number;
    name: string;
    diseases_count: number;
}

interface DiseaseUrls {
    index: string;
    create: string;
    edit: string;
    destroy: string;
    storeCategory: string;
    updateCategory: string;
    destroyCategory: string;
}

export default function IndexDiseases({
    diseases,
    categories,
    filters: serverFilters,
    filterOptions,
    permissions,
    urls,
}: {
    diseases: PaginatedResult<DiseaseItem>;
    categories: DiseaseCategoryItem[];
    filters: { search: string; disease_category_id: string; per_page: string };
    filterOptions: { diseaseCategories: OptionItem[] };
    permissions: SettingsPermissions;
    urls: DiseaseUrls;
}) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState(0);
    const [filters, setFilters] = useState(serverFilters);
    const [processing, setProcessing] = useState(false);
    const [categoryName, setCategoryName] = useState('');
    const [editingCategoryId, setEditingCategoryId] = useState<number | null>(null);
    const [editingCategoryName, setEditingCategoryName] = useState('');
    const [categoryProcessing, setCategoryProcessing] = useState(false);

    useEffect(() => setFilters(serverFilters), [serverFilters]);

    const applyFilters = useCallback(
        (next: typeof filters) => {
            setProcessing(true);
            router.get(
                urls.index,
                Object.fromEntries(Object.entries(next).filter(([, value]) => value !== '')),
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                    onFinish: () => setProcessing(false),
                },
            );
        },
        [urls.index],
    );

    const handleSaveCategory = () => {
        const name = editingCategoryId ? editingCategoryName.trim() : categoryName.trim();
        if (!name) return;

        setCategoryProcessing(true);
        if (editingCategoryId) {
            router.put(
                `${urls.updateCategory}/${editingCategoryId}`,
                { name },
                {
                    preserveScroll: true,
                    onFinish: () => {
                        setCategoryProcessing(false);
                        setEditingCategoryId(null);
                        setEditingCategoryName('');
                    },
                },
            );
            return;
        }

        router.post(
            urls.storeCategory,
            { name },
            {
                preserveScroll: true,
                onFinish: () => {
                    setCategoryProcessing(false);
                    setCategoryName('');
                },
            },
        );
    };

    const handleDeleteCategory = (category: DiseaseCategoryItem) => {
        if (!window.confirm(t('global.are_you_sure'))) return;
        setCategoryProcessing(true);
        router.delete(`${urls.destroyCategory}/${category.id}`, {
            preserveScroll: true,
            onFinish: () => setCategoryProcessing(false),
        });
    };

    const summaryLabel =
        diseases.meta.from && diseases.meta.to
            ? `${t('global.showing')} ${diseases.meta.from}-${diseases.meta.to} ${t('global.of')} ${diseases.meta.total}`
            : `${diseases.meta.total} ${t('global.results')}`;

    return (
        <DashboardLayout>
            <Head title={t('global.diseases')} />
            <div className="mx-auto max-w-7xl">
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.diseases')}
                        subtitle={summaryLabel}
                        icon="bx-pulse"
                        accent="from-red-500 to-rose-600"
                        backLabel={t('global.back')}
                        action={
                            permissions.create ? (
                                <Button color="blue" as={Link} href={urls.create}>
                                    <i className="bx bx-plus me-2 text-lg" />
                                    {t('global.create_disease')}
                                </Button>
                            ) : undefined
                        }
                    />

                    <Tabs
                        aria-label={t('global.diseases')}
                        style="underline"
                        onActiveTabChange={(tab) => setActiveTab(tab)}
                    >
                        <Tabs.Item active={activeTab === 0} title={t('global.diseases')}>
                            <form
                                onSubmit={(event: FormEvent) => {
                                    event.preventDefault();
                                    applyFilters(filters);
                                }}
                                className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                            >
                                <div>
                                    <Label>{t('global.search')}</Label>
                                    <TextInput
                                        value={filters.search}
                                        onChange={(event) =>
                                            setFilters({ ...filters, search: event.target.value })
                                        }
                                        placeholder={t('global.search_by_name')}
                                    />
                                </div>
                                <div>
                                    <Label>{t('global.disease_category')}</Label>
                                    <SearchableSelect
                                        value={filters.disease_category_id}
                                        onChange={(value) => {
                                            const next = { ...filters, disease_category_id: value };
                                            setFilters(next);
                                            applyFilters(next);
                                        }}
                                        options={filterOptions.diseaseCategories.map((category) => ({
                                            value: String(category.id),
                                            label: category.name,
                                        }))}
                                        placeholder={t('global.all')}
                                    />
                                </div>
                                <div className="flex items-end">
                                    <Button type="submit" color="blue" disabled={processing}>
                                        {processing ? <Spinner size="sm" /> : t('global.search')}
                                    </Button>
                                </div>
                            </form>

                            {diseases.data.length > 0 ? (
                                <Table>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>#</TableHeader>
                                            <TableHeader>{t('global.name')}</TableHeader>
                                            <TableHeader>{t('global.disease_category')}</TableHeader>
                                            <TableHeader>{t('global.department')}</TableHeader>
                                            <TableHeader>{t('global.description')}</TableHeader>
                                            <TableHeader align="center">{t('global.actions')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {diseases.data.map((item, index) => (
                                            <TableRow key={item.id}>
                                                <TableCell>{(diseases.meta.from ?? 1) + index}</TableCell>
                                                <TableCell>{item.name}</TableCell>
                                                <TableCell muted>
                                                    {item.disease_category_name ?? '—'}
                                                </TableCell>
                                                <TableCell muted>{item.department_name ?? '—'}</TableCell>
                                                <TableCell muted>{item.description ?? '—'}</TableCell>
                                                <TableCell align="center">
                                                    <div className="flex justify-center gap-1">
                                                        {permissions.edit && (
                                                            <Link
                                                                href={`${urls.edit}/${item.id}/edit`}
                                                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50"
                                                            >
                                                                <i className="bx bx-edit text-lg" />
                                                            </Link>
                                                        )}
                                                        {permissions.delete && (
                                                            <button
                                                                type="button"
                                                                onClick={() => {
                                                                    if (
                                                                        window.confirm(t('global.are_you_sure'))
                                                                    ) {
                                                                        router.delete(
                                                                            `${urls.destroy}/${item.id}`,
                                                                            { preserveScroll: true },
                                                                        );
                                                                    }
                                                                }}
                                                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50"
                                                            >
                                                                <i className="bx bx-trash text-lg" />
                                                            </button>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            ) : (
                                <p className="py-12 text-center text-sm text-gray-500">
                                    {t('global.no_results_found')}
                                </p>
                            )}
                            {diseases.links.length > 0 && (
                                <ul className="mt-6 inline-flex -space-x-px text-sm">
                                    {diseases.links.map((link, index) => renderPaginationLink(link, index))}
                                </ul>
                            )}
                        </Tabs.Item>

                        <Tabs.Item active={activeTab === 1} title={t('global.disease_categories')}>
                            {permissions.create && (
                                <div className="mb-6 flex flex-wrap gap-2">
                                    <TextInput
                                        className="min-w-[220px] flex-1"
                                        value={editingCategoryId ? editingCategoryName : categoryName}
                                        onChange={(event) =>
                                            editingCategoryId
                                                ? setEditingCategoryName(event.target.value)
                                                : setCategoryName(event.target.value)
                                        }
                                        placeholder={t('global.name')}
                                    />
                                    <Button
                                        color="blue"
                                        disabled={categoryProcessing}
                                        onClick={handleSaveCategory}
                                    >
                                        {categoryProcessing ? (
                                            <Spinner size="sm" />
                                        ) : editingCategoryId ? (
                                            t('global.update')
                                        ) : (
                                            t('global.create_disease_category')
                                        )}
                                    </Button>
                                    {editingCategoryId && (
                                        <Button
                                            color="light"
                                            disabled={categoryProcessing}
                                            onClick={() => {
                                                setEditingCategoryId(null);
                                                setEditingCategoryName('');
                                            }}
                                        >
                                            {t('global.cancel')}
                                        </Button>
                                    )}
                                </div>
                            )}

                            {categories.length > 0 ? (
                                <Table>
                                    <TableHead>
                                        <TableRow variant="header">
                                            <TableHeader>#</TableHeader>
                                            <TableHeader>{t('global.name')}</TableHeader>
                                            <TableHeader>{t('global.diseases')}</TableHeader>
                                            <TableHeader align="center">{t('global.actions')}</TableHeader>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {categories.map((category, index) => (
                                            <TableRow key={category.id}>
                                                <TableCell>{index + 1}</TableCell>
                                                <TableCell>{category.name}</TableCell>
                                                <TableCell muted>{category.diseases_count}</TableCell>
                                                <TableCell align="center">
                                                    <div className="flex justify-center gap-1">
                                                        {permissions.edit && (
                                                            <button
                                                                type="button"
                                                                onClick={() => {
                                                                    setEditingCategoryId(category.id);
                                                                    setEditingCategoryName(category.name);
                                                                }}
                                                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50"
                                                            >
                                                                <i className="bx bx-edit text-lg" />
                                                            </button>
                                                        )}
                                                        {permissions.delete && (
                                                            <button
                                                                type="button"
                                                                disabled={categoryProcessing}
                                                                onClick={() => handleDeleteCategory(category)}
                                                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50"
                                                            >
                                                                <i className="bx bx-trash text-lg" />
                                                            </button>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            ) : (
                                <p className="py-12 text-center text-sm text-gray-500">
                                    {t('global.no_results_found')}
                                </p>
                            )}
                        </Tabs.Item>
                    </Tabs>
                </Card>
            </div>
        </DashboardLayout>
    );
}
