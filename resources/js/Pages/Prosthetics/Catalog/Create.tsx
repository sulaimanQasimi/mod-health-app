import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Checkbox, Label, TextInput } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import DashboardLayout from '../../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../../Components/Settings/SettingsPageHeader';
import { useTranslation } from '../../../hooks/useTranslation';
import { SETTINGS_FORM_WIDTH } from '../../../utils/settingsUi';

interface CreateProps {
    urls: { index: string; store: string };
}

export default function ProstheticsCatalogCreate({ urls }: CreateProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [form, setForm] = useState({
        item_code: '',
        name: '',
        category: '',
        standard_cost: '',
        is_active: true,
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        router.post(urls.store, form, { onFinish: () => setProcessing(false) });
    };

    return (
        <DashboardLayout>
            <Head title={t('global.add')} />

            <div className={`mx-auto space-y-6 ${SETTINGS_FORM_WIDTH}`}>
                <SettingsPageHeader
                    title={t('global.add')}
                    icon="bx-plus"
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
                        <div>
                            <Label htmlFor="category" value={t('global.category')} />
                            <TextInput
                                id="category"
                                value={form.category}
                                onChange={(e) => setForm((prev) => ({ ...prev, category: e.target.value }))}
                            />
                        </div>
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
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="is_active"
                                checked={form.is_active}
                                onChange={(e) => setForm((prev) => ({ ...prev, is_active: e.target.checked }))}
                            />
                            <Label htmlFor="is_active" value={t('global.active')} />
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
