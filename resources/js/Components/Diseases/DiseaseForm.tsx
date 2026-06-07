import { useForm } from '@inertiajs/react';
import { Button, Label, Spinner, TextInput, Textarea } from 'flowbite-react';
import { FormEvent } from 'react';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { OptionItem, SettingsFormUrls } from '../../types/settings';

interface DiseaseFormProps {
    mode: 'create' | 'edit';
    urls: SettingsFormUrls;
    formData: { departments: OptionItem[]; diseaseCategories: OptionItem[] };
    disease?: {
        id: number;
        name: string;
        description: string;
        department_id: string;
        disease_category_id: string;
    };
}

export default function DiseaseForm({ mode, urls, formData, disease }: DiseaseFormProps) {
    const { t } = useTranslation();
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm({
        name: disease?.name ?? '',
        description: disease?.description ?? '',
        department_id: disease?.department_id ?? '',
        disease_category_id: disease?.disease_category_id ?? '',
    });

    return (
        <form
            onSubmit={(event: FormEvent) => {
                event.preventDefault();
                const opts = { preserveScroll: true };
                if (isEdit) put(urls.update, opts);
                else post(urls.store, opts);
            }}
            className="space-y-4"
        >
            <div>
                <Label htmlFor="name">{t('global.name')} *</Label>
                <TextInput
                    id="name"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    required
                />
                {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
            </div>
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="disease_category_id">{t('global.disease_category')}</Label>
                    <SearchableSelect
                        value={data.disease_category_id}
                        onChange={(value) => setData('disease_category_id', value)}
                        options={formData.diseaseCategories.map((category) => ({
                            value: String(category.id),
                            label: category.name,
                        }))}
                        placeholder={t('global.select')}
                    />
                    {errors.disease_category_id && (
                        <p className="mt-1 text-sm text-red-600">{errors.disease_category_id}</p>
                    )}
                </div>
                <div>
                    <Label htmlFor="department_id">{t('global.department')} *</Label>
                    <SearchableSelect
                        value={data.department_id}
                        onChange={(value) => setData('department_id', value)}
                        options={formData.departments.map((department) => ({
                            value: String(department.id),
                            label: department.name,
                        }))}
                        placeholder={t('global.select')}
                    />
                    {errors.department_id && (
                        <p className="mt-1 text-sm text-red-600">{errors.department_id}</p>
                    )}
                </div>
            </div>
            <div>
                <Label htmlFor="description">{t('global.description')}</Label>
                <Textarea
                    id="description"
                    rows={3}
                    value={data.description}
                    onChange={(event) => setData('description', event.target.value)}
                />
                {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
            </div>
            <div className="flex justify-end gap-2 border-t pt-4">
                <Button color="light" type="button" as="a" href={urls.back} disabled={processing}>
                    {t('global.cancel')}
                </Button>
                <Button type="submit" color="blue" disabled={processing}>
                    {processing ? <Spinner size="sm" /> : isEdit ? t('global.update') : t('global.create')}
                </Button>
            </div>
        </form>
    );
}
