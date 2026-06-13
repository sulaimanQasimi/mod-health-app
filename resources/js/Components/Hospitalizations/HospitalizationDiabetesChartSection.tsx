import {
    Badge,
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    TextInput,
} from 'flowbite-react';
import { FormEvent, useCallback, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../ui/Table';
import PersianDateInput from '../ui/PersianDateInput';
import SearchableSelect from '../ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

interface HospitalizationDiabetesChartSectionProps {
    hospitalizationId: number;
    isDischarged?: boolean;
    baseUrl?: string;
}

interface MedicineOption {
    id: number;
    name: string;
}

interface DiabetesChartListItem {
    id: number;
    date: string | null;
    time: string | null;
    rbs: string | number | null;
    fbs: string | number | null;
    insulin_dose: string | number | null;
    unit: string | null;
    nurse_name: string | null;
    medicine_name: string | null;
    medicine_id: number | null;
}

interface ChartFormState {
    date: string;
    time: string;
    rbs: string;
    fbs: string;
    unit: string;
    insulin_dose: string;
    medicine_id: string;
}

interface SectionData {
    items: DiabetesChartListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
    urls?: { print?: string | null };
}

const EMPTY_FORM: ChartFormState = {
    date: '',
    time: '',
    rbs: '',
    fbs: '',
    unit: '',
    insulin_dose: '',
    medicine_id: '',
};

function formatReading(value: string | number | null, unit: string | null) {
    if (value === null || value === '') {
        return '—';
    }

    return unit ? `${value} ${unit}` : String(value);
}

function ChartFormFields({
    form,
    setForm,
    medicines,
    unitOptions,
    currentNurseName,
    t,
}: {
    form: ChartFormState;
    setForm: React.Dispatch<React.SetStateAction<ChartFormState>>;
    medicines: MedicineOption[];
    unitOptions: string[];
    currentNurseName: string | null;
    t: (key: string) => string;
}) {
    return (
        <div className="space-y-5">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="diabetes-chart-date">{t('global.date')}</Label>
                    <PersianDateInput
                        id="diabetes-chart-date"
                        required
                        className="mt-2"
                        value={form.date}
                        onChange={(value) => setForm((prev) => ({ ...prev, date: value }))}
                    />
                </div>
                <div>
                    <Label htmlFor="diabetes-chart-time">{t('global.time')}</Label>
                    <TextInput
                        id="diabetes-chart-time"
                        type="time"
                        className="mt-2"
                        value={form.time}
                        onChange={(e) => setForm((prev) => ({ ...prev, time: e.target.value }))}
                    />
                </div>
            </div>

            <div>
                <p className="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                    {t('global.blood_sugar_reading')}
                </p>
                <div className="grid gap-4 md:grid-cols-3">
                    <div>
                        <Label htmlFor="diabetes-chart-rbs">{t('global.rbs')}</Label>
                        <TextInput
                            id="diabetes-chart-rbs"
                            type="number"
                            step="0.1"
                            className="mt-1"
                            value={form.rbs}
                            onChange={(e) => setForm((prev) => ({ ...prev, rbs: e.target.value }))}
                        />
                    </div>
                    <div>
                        <Label htmlFor="diabetes-chart-fbs">{t('global.fbs')}</Label>
                        <TextInput
                            id="diabetes-chart-fbs"
                            type="number"
                            step="0.1"
                            className="mt-1"
                            value={form.fbs}
                            onChange={(e) => setForm((prev) => ({ ...prev, fbs: e.target.value }))}
                        />
                    </div>
                    <div>
                        <Label htmlFor="diabetes-chart-unit">{t('global.unit')}</Label>
                        <SearchableSelect
                            id="diabetes-chart-unit"
                            className="mt-1"
                            value={form.unit}
                            onChange={(value) => setForm((prev) => ({ ...prev, unit: value }))}
                            options={unitOptions.map((unit) => ({ value: unit, label: unit }))}
                            placeholder={t('global.select')}
                        />
                    </div>
                </div>
            </div>

            <div>
                <p className="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                    {t('global.insulin_administration')}
                </p>
                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label htmlFor="diabetes-chart-insulin">{t('global.insulin_dose')}</Label>
                        <TextInput
                            id="diabetes-chart-insulin"
                            type="number"
                            step="0.1"
                            className="mt-1"
                            value={form.insulin_dose}
                            onChange={(e) =>
                                setForm((prev) => ({ ...prev, insulin_dose: e.target.value }))
                            }
                        />
                    </div>
                    <div>
                        <Label htmlFor="diabetes-chart-medicine">{t('global.medicine')}</Label>
                        <SearchableSelect
                            id="diabetes-chart-medicine"
                            className="mt-1"
                            value={form.medicine_id}
                            onChange={(value) => setForm((prev) => ({ ...prev, medicine_id: value }))}
                            options={medicines.map((medicine) => ({
                                value: String(medicine.id),
                                label: medicine.name,
                            }))}
                            placeholder={t('global.select')}
                        />
                    </div>
                </div>
            </div>

            {currentNurseName && (
                <div className="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                    <p className="text-xs font-medium uppercase text-gray-500">{t('global.nurse')}</p>
                    <p className="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {currentNurseName}
                    </p>
                </div>
            )}
        </div>
    );
}

export default function HospitalizationDiabetesChartSection({
    hospitalizationId,
    isDischarged = false,
    baseUrl: baseUrlProp,
}: HospitalizationDiabetesChartSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = baseUrlProp ?? `/react/hospitalizations/${hospitalizationId}/diabetes-charts`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [medicines, setMedicines] = useState<MedicineOption[]>([]);
    const [unitOptions, setUnitOptions] = useState<string[]>(['mg/dl', 'mmol/l']);
    const [currentNurseName, setCurrentNurseName] = useState<string | null>(null);
    const [formOpen, setFormOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [editingChartId, setEditingChartId] = useState<number | null>(null);
    const [selectedChart, setSelectedChart] = useState<DiabetesChartListItem | null>(null);
    const [form, setForm] = useState<ChartFormState>(EMPTY_FORM);

    const loadData = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch(baseUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setData(payload.data);
            }
        } finally {
            setLoading(false);
        }
    }, [baseUrl]);

    const loadMeta = useCallback(async () => {
        try {
            const response = await fetch(`${baseUrl}/meta`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) {
                return null;
            }
            const payload = await response.json();
            if (!payload.success) {
                return null;
            }

            setMedicines(payload.data.medicines ?? []);
            setUnitOptions(payload.data.unit_options ?? ['mg/dl', 'mmol/l']);
            setCurrentNurseName(payload.data.current_nurse?.name ?? null);

            return payload.data as {
                default_date?: string;
                current_nurse?: { name?: string } | null;
            };
        } catch {
            return null;
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
        loadMeta();
    }, [loadData, loadMeta]);

    const postJson = async (url: string, method: string, body?: Record<string, unknown>) => {
        setSubmitting(true);
        try {
            const response = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: body ? JSON.stringify(body) : undefined,
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                return false;
            }
            await loadData();
            return true;
        } finally {
            setSubmitting(false);
        }
    };

    const openCreate = async () => {
        const meta = await loadMeta();
        setEditingChartId(null);
        setForm({
            ...EMPTY_FORM,
            date: meta?.default_date ?? '',
        });
        setFormOpen(true);
    };

    const openEdit = (chart: DiabetesChartListItem) => {
        setEditingChartId(chart.id);
        setForm({
            date: chart.date ?? '',
            time: chart.time ?? '',
            rbs: chart.rbs != null ? String(chart.rbs) : '',
            fbs: chart.fbs != null ? String(chart.fbs) : '',
            unit: chart.unit ?? '',
            insulin_dose: chart.insulin_dose != null ? String(chart.insulin_dose) : '',
            medicine_id: chart.medicine_id != null ? String(chart.medicine_id) : '',
        });
        setDetailsOpen(false);
        setFormOpen(true);
        void loadMeta();
    };

    const closeForm = () => {
        setFormOpen(false);
        setEditingChartId(null);
        setForm(EMPTY_FORM);
    };

    const resolveFormDate = () => {
        const input = document.getElementById('diabetes-chart-date') as HTMLInputElement | null;
        return (input?.value ?? form.date).trim();
    };

    const buildPayload = () => ({
        date: resolveFormDate(),
        time: form.time || null,
        rbs: form.rbs ? Number(form.rbs) : null,
        fbs: form.fbs ? Number(form.fbs) : null,
        unit: form.unit || null,
        insulin_dose: form.insulin_dose ? Number(form.insulin_dose) : null,
        medicine_id: form.medicine_id ? Number(form.medicine_id) : null,
    });

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        const date = resolveFormDate();
        if (!date) {
            return;
        }
        setForm((prev) => ({ ...prev, date }));

        const ok = editingChartId
            ? await postJson(`${baseUrl}/${editingChartId}`, 'PUT', buildPayload())
            : await postJson(baseUrl, 'POST', buildPayload());

        if (ok) {
            closeForm();
        }
    };

    const handleDelete = async (chartId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        const ok = await postJson(`${baseUrl}/${chartId}`, 'DELETE');
        if (ok) {
            setDetailsOpen(false);
            setSelectedChart(null);
        }
    };

    const openDetails = async (chart: DiabetesChartListItem) => {
        setSelectedChart(chart);
        setDetailsOpen(true);

        try {
            const response = await fetch(`${baseUrl}/${chart.id}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setSelectedChart(payload.data);
            }
        } catch {
            // Keep row data when detail fetch fails.
        }
    };

    if (!loading && data?.permissions.view === false) {
        return null;
    }

    return (
        <>
            <SectionShell
                id={`hospitalization-diabetes-charts-${hospitalizationId}`}
                icon="bx-line-chart"
                iconClassName="text-orange-500"
                title={t('global.diabetes_charts')}
                count={data?.count}
                badgeColor="warning"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        <div className="mb-4 flex flex-wrap justify-end gap-2">
                            {data?.urls?.print && (
                                <Button
                                    as="a"
                                    href={data.urls.print}
                                    target="_blank"
                                    rel="noreferrer"
                                    size="sm"
                                    color="info"
                                >
                                    <i className="bx bx-printer me-2" />
                                    {t('global.print_chart')}
                                </Button>
                            )}
                            {data?.permissions.create && !isDischarged && (
                                <Button size="sm" color="success" onClick={openCreate}>
                                    <i className="bx bx-plus me-2" />
                                    {t('global.add_diabetes_chart')}
                                </Button>
                            )}
                        </div>

                        {(data?.items.length ?? 0) > 0 ? (
                            <Table embedded className="min-w-[1100px]">
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader className="w-12">{t('global.number')}</TableHeader>
                                        <TableHeader>{t('global.date')}</TableHeader>
                                        <TableHeader>{t('global.time')}</TableHeader>
                                        <TableHeader>{t('global.rbs')}</TableHeader>
                                        <TableHeader>{t('global.fbs')}</TableHeader>
                                        <TableHeader>{t('global.insulin_dose')}</TableHeader>
                                        <TableHeader>{t('global.unit')}</TableHeader>
                                        <TableHeader>{t('global.nurse')}</TableHeader>
                                        <TableHeader>{t('global.medicine')}</TableHeader>
                                        <TableHeader align="right" className="w-28">
                                            {t('global.actions')}
                                        </TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data?.items.map((chart, index) => (
                                        <TableRow key={chart.id}>
                                            <TableCell className="text-gray-500">{index + 1}</TableCell>
                                            <TableCell dir="ltr">{chart.date ?? '—'}</TableCell>
                                            <TableCell muted dir="ltr">
                                                {chart.time ?? '—'}
                                            </TableCell>
                                            <TableCell>
                                                {chart.rbs ? (
                                                    <Badge color="warning">
                                                        {formatReading(chart.rbs, chart.unit)}
                                                    </Badge>
                                                ) : (
                                                    '—'
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {chart.fbs ? (
                                                    <Badge color="success">
                                                        {formatReading(chart.fbs, chart.unit)}
                                                    </Badge>
                                                ) : (
                                                    '—'
                                                )}
                                            </TableCell>
                                            <TableCell muted>{chart.insulin_dose ?? '—'}</TableCell>
                                            <TableCell muted>{chart.unit ?? '—'}</TableCell>
                                            <TableCell muted>{chart.nurse_name ?? '—'}</TableCell>
                                            <TableCell muted>{chart.medicine_name ?? '—'}</TableCell>
                                            <TableCell align="right">
                                                <SectionActionButton
                                                    icon="bx-expand"
                                                    title={t('global.view')}
                                                    onClick={() => openDetails(chart)}
                                                    colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                                />
                                                {data?.permissions.edit && !isDischarged && (
                                                    <SectionActionButton
                                                        icon="bx-edit"
                                                        title={t('global.edit')}
                                                        onClick={() => openEdit(chart)}
                                                        colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                    />
                                                )}
                                                {data?.permissions.delete && !isDischarged && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => handleDelete(chart.id)}
                                                        colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_diabetes_charts_found')} />
                        )}
                    </>
                )}
            </SectionShell>

            <Modal show={formOpen} onClose={closeForm} size="7xl">
                <form onSubmit={handleSubmit}>
                    <ModalHeader>
                        {editingChartId ? t('global.edit_diabetes_chart') : t('global.add_diabetes_chart')}
                    </ModalHeader>
                    <ModalBody>
                        <ChartFormFields
                            form={form}
                            setForm={setForm}
                            medicines={medicines}
                            unitOptions={unitOptions}
                            currentNurseName={currentNurseName}
                            t={t}
                        />
                    </ModalBody>
                    <ModalFooter>
                        <Button type="button" color="light" onClick={closeForm}>
                            {t('global.cancel')}
                        </Button>
                        <Button type="submit" color="success" disabled={submitting}>
                            {submitting && <Spinner size="sm" className="me-2" />}
                            {t('global.save')}
                        </Button>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal show={detailsOpen} onClose={() => setDetailsOpen(false)} size="7xl">
                <ModalHeader>{t('global.diabetes_chart_details')}</ModalHeader>
                <ModalBody className="space-y-4">
                    {selectedChart && (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.date')}
                                    </p>
                                    <p className="mt-1" dir="ltr">
                                        {selectedChart.date ?? '—'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.time')}
                                    </p>
                                    <p className="mt-1" dir="ltr">
                                        {selectedChart.time ?? '—'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.nurse')}
                                    </p>
                                    <p className="mt-1">{selectedChart.nurse_name ?? '—'}</p>
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.rbs')}
                                    </p>
                                    <p className="mt-1">
                                        {formatReading(selectedChart.rbs, selectedChart.unit)}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.fbs')}
                                    </p>
                                    <p className="mt-1">
                                        {formatReading(selectedChart.fbs, selectedChart.unit)}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.insulin_dose')}
                                    </p>
                                    <p className="mt-1">{selectedChart.insulin_dose ?? '—'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.medicine')}
                                    </p>
                                    <p className="mt-1">{selectedChart.medicine_name ?? '—'}</p>
                                </div>
                            </div>
                        </>
                    )}
                </ModalBody>
                <ModalFooter>
                    {selectedChart && data?.permissions.edit && !isDischarged && (
                        <Button color="warning" onClick={() => openEdit(selectedChart)}>
                            {t('global.edit')}
                        </Button>
                    )}
                    {selectedChart && data?.permissions.delete && !isDischarged && (
                        <Button color="failure" onClick={() => handleDelete(selectedChart.id)}>
                            {t('global.delete')}
                        </Button>
                    )}
                    <Button color="light" onClick={() => setDetailsOpen(false)}>
                        {t('global.close')}
                    </Button>
                </ModalFooter>
            </Modal>
        </>
    );
}
