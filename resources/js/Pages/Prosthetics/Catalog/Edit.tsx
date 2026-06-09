import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Checkbox, Label, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../../utils/settingsUi';

interface EditProps {
    item: {
        id: number;
        item_code: string;
        name: string;
        local_name: string | null;
        category: string | null;
        subcategory: string | null;
        brand: string | null;
        unit_of_measure: string | null;
        standard_cost: number | null;
        minimum_stock: number | null;
        tracks_serial: boolean;
        is_active: boolean;
    };
    urls: { index: string; update: string };
}

export default function ProstheticsCatalogEdit({ item, urls }: EditProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState({
        item_code: item.item_code,
        name: item.name,
        local_name: item.local_name ?? '',
        category: item.category ?? '',
        subcategory: item.subcategory ?? '',
        brand: item.brand ?? '',
        unit_of_measure: item.unit_of_measure ?? '',
        standard_cost: item.standard_cost != null ? String(item.standard_cost) : '',
        minimum_stock: item.minimum_stock != null ? String(item.minimum_stock) : '',
        tracks_serial: item.tracks_serial,
        is_active: item.is_active,
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        router.put(urls.update, form, { onFinish: () => setProcessing(false) });
    };

    return (
        <DashboardLayout>
            <Head title={item.item_code} />

            <div className={`mx-auto space-y-6 ${SETTINGS_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={item.item_code}
                    icon="bx-edit"
                    accent="from-amber-500 to-orange-600"
                    backHref={urls.index}
                    backLabel={t('global.back')}
                />

                <Card>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <Label htmlFor="item_code" value={`${t('global.code')} *`} />
                            <TextInput
                                id="item_code"
                                required
                                value={form.item_code}
                                onChange={(e) => setForm((prev) => ({ ...prev, item_code: e.target.value }))}
                            />
                        </div>
                        <div>
                            <Label htmlFor="name" value={`${t('global.name')} *`} />
                            <TextInput
                                id="name"
                                required
                                value={form.name}
                                onChange={(e) => setForm((prev) => ({ ...prev, name: e.target.value }))}
                            />
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="category" value={t('global.category')} />
                                <TextInput
                                    id="category"
                                    value={form.category}
                                    onChange={(e) => setForm((prev) => ({ ...prev, category: e.target.value }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="brand" value={t('global.brand')} />
                                <TextInput
                                    id="brand"
                                    value={form.brand}
                                    onChange={(e) => setForm((prev) => ({ ...prev, brand: e.target.value }))}
                                />
                            </div>
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="standard_cost" value={t('global.cost')} />
                                <TextInput
                                    id="standard_cost"
                                    type="number"
                                    step="0.01"
                                    min={0}
                                    value={form.standard_cost}
                                    onChange={(e) => setForm((prev) => ({ ...prev, standard_cost: e.target.value }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="minimum_stock" value={t('global.minimum_stock')} />
                                <TextInput
                                    id="minimum_stock"
                                    type="number"
                                    min={0}
                                    value={form.minimum_stock}
                                    onChange={(e) => setForm((prev) => ({ ...prev, minimum_stock: e.target.value }))}
                                />
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-4">
                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="tracks_serial"
                                    checked={form.tracks_serial}
                                    onChange={(e) => setForm((prev) => ({ ...prev, tracks_serial: e.target.checked }))}
                                />
                                <Label htmlFor="tracks_serial" value={t('global.tracks_serial')} />
                            </div>
                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="is_active"
                                    checked={form.is_active}
                                    onChange={(e) => setForm((prev) => ({ ...prev, is_active: e.target.checked }))}
                                />
                                <Label htmlFor="is_active" value={t('global.active')} />
                            </div>
                        </div>
                        <div className="flex gap-2">
                            <Button type="submit" color="blue" disabled={processing}>
                                {t('global.save')}
                            </Button>
                            <Button as={Link} href={urls.index} color="light">
                                {t('global.back')}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </DashboardLayout>
    );
}
