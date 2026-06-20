import {
    Button,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Spinner,
    Textarea,
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
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import {
    AccordionActionBar,
    AccordionButton,
    SectionEmptyState,
    SectionLoadingState,
    SectionShell,
} from '../Appointments/Sections/AppointmentSectionAccordion';
import { SectionActionButton } from '../Appointments/Sections/SimpleTableSection';

interface HospitalizationNurseNoteSectionProps {
    hospitalizationId: number;
    isDischarged?: boolean;
    baseUrl?: string;
}

interface NurseNoteListItem {
    id: number;
    date: string | null;
    time_am: string | null;
    time_pm: string | null;
    note: string | null;
    nurse_name: string | null;
    created_by_name: string | null;
}

interface NoteFormState {
    date: string;
    time_am: string;
    time_pm: string;
    note: string;
}

interface SectionData {
    items: NurseNoteListItem[];
    count: number;
    permissions: {
        view?: boolean;
        create?: boolean;
        edit?: boolean;
        delete?: boolean;
    };
    urls?: { print?: string | null };
}

const EMPTY_FORM: NoteFormState = {
    date: '',
    time_am: '',
    time_pm: '',
    note: '',
};

function NoteFormFields({
    form,
    setForm,
    currentNurseName,
    t,
}: {
    form: NoteFormState;
    setForm: React.Dispatch<React.SetStateAction<NoteFormState>>;
    currentNurseName: string | null;
    t: (key: string) => string;
}) {
    return (
        <div className="space-y-5">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="nurse-note-date">{t('global.date')}</Label>
                    <PersianDateInput
                        id="nurse-note-date"
                        required
                        className="mt-2"
                        value={form.date}
                        onChange={(value) => setForm((prev) => ({ ...prev, date: value }))}
                    />
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

            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="nurse-note-time-am">{t('global.nurse_note_am_time')}</Label>
                    <TextInput
                        id="nurse-note-time-am"
                        type="time"
                        className="mt-1"
                        value={form.time_am}
                        onChange={(e) => setForm((prev) => ({ ...prev, time_am: e.target.value }))}
                    />
                </div>
                <div>
                    <Label htmlFor="nurse-note-time-pm">{t('global.nurse_note_pm_time')}</Label>
                    <TextInput
                        id="nurse-note-time-pm"
                        type="time"
                        className="mt-1"
                        value={form.time_pm}
                        onChange={(e) => setForm((prev) => ({ ...prev, time_pm: e.target.value }))}
                    />
                </div>
            </div>

            <div>
                <Label htmlFor="nurse-note-text">{t('global.note')}</Label>
                <Textarea
                    id="nurse-note-text"
                    rows={5}
                    className="mt-2"
                    value={form.note}
                    onChange={(e) => setForm((prev) => ({ ...prev, note: e.target.value }))}
                />
            </div>
        </div>
    );
}

export default function HospitalizationNurseNoteSection({
    hospitalizationId,
    isDischarged = false,
    baseUrl: baseUrlProp,
}: HospitalizationNurseNoteSectionProps) {
    const { t } = useTranslation();
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = baseUrlProp ?? `/react/hospitalizations/${hospitalizationId}/nurse-notes`;

    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [data, setData] = useState<SectionData | null>(null);
    const [currentNurseName, setCurrentNurseName] = useState<string | null>(null);
    const [formOpen, setFormOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [editingNoteId, setEditingNoteId] = useState<number | null>(null);
    const [selectedNote, setSelectedNote] = useState<NurseNoteListItem | null>(null);
    const [form, setForm] = useState<NoteFormState>(EMPTY_FORM);

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

            setCurrentNurseName(payload.data.current_nurse?.name ?? null);

            return payload.data as { default_date?: string };
        } catch {
            return null;
        }
    }, [baseUrl]);

    useEffect(() => {
        loadData();
    }, [loadData]);

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

    const resolveFormDate = () => {
        const input = document.getElementById('nurse-note-date') as HTMLInputElement | null;
        return (input?.value ?? form.date).trim();
    };

    const openCreate = async () => {
        const meta = await loadMeta();
        setEditingNoteId(null);
        setForm({
            ...EMPTY_FORM,
            date: meta?.default_date ?? '',
        });
        setFormOpen(true);
    };

    const openEdit = (note: NurseNoteListItem) => {
        setEditingNoteId(note.id);
        setForm({
            date: note.date ?? '',
            time_am: note.time_am ?? '',
            time_pm: note.time_pm ?? '',
            note: note.note ?? '',
        });
        setDetailsOpen(false);
        setFormOpen(true);
        void loadMeta();
    };

    const closeForm = () => {
        setFormOpen(false);
        setEditingNoteId(null);
        setForm(EMPTY_FORM);
    };

    const buildPayload = () => ({
        date: resolveFormDate(),
        time_am: form.time_am || null,
        time_pm: form.time_pm || null,
        note: form.note || null,
    });

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        const date = resolveFormDate();
        if (!date) {
            return;
        }
        setForm((prev) => ({ ...prev, date }));

        const ok = editingNoteId
            ? await postJson(`${baseUrl}/${editingNoteId}`, 'PUT', buildPayload())
            : await postJson(baseUrl, 'POST', buildPayload());

        if (ok) {
            closeForm();
        }
    };

    const handleDelete = async (noteId: number) => {
        if (!window.confirm(t('global.confirm_delete'))) {
            return;
        }
        const ok = await postJson(`${baseUrl}/${noteId}`, 'DELETE');
        if (ok) {
            setDetailsOpen(false);
            setSelectedNote(null);
        }
    };

    const openDetails = async (note: NurseNoteListItem) => {
        setSelectedNote(note);
        setDetailsOpen(true);

        try {
            const response = await fetch(`${baseUrl}/${note.id}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setSelectedNote(payload.data);
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
                id={`hospitalization-nurse-notes-${hospitalizationId}`}
                icon="bx-note"
                iconClassName="text-violet-500"
                title={t('global.nurse_notes')}
                count={data?.count}
                badgeColor="purple"
            >
                {loading ? (
                    <SectionLoadingState />
                ) : (
                    <>
                        {(data?.urls?.print || (data?.permissions.create && !isDischarged)) && (
                            <AccordionActionBar>
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
                                        {t('global.print_notes')}
                                    </Button>
                                )}
                                <AccordionButton
                                    bare
                                    onClick={openCreate}
                                    permission={Boolean(data?.permissions.create && !isDischarged)}
                                >
                                    {t('global.add_nurse_note')}
                                </AccordionButton>
                            </AccordionActionBar>
                        )}

                        {(data?.items.length ?? 0) > 0 ? (
                            <Table embedded className="min-w-[960px]">
                                <TableHead>
                                    <TableRow variant="header">
                                        <TableHeader className="w-12">{t('global.number')}</TableHeader>
                                        <TableHeader>{t('global.date')}</TableHeader>
                                        <TableHeader>{t('global.nurse')}</TableHeader>
                                        <TableHeader>{t('global.am_time')}</TableHeader>
                                        <TableHeader>{t('global.pm_time')}</TableHeader>
                                        <TableHeader className="min-w-[200px]">{t('global.note')}</TableHeader>
                                        <TableHeader>{t('global.created_by')}</TableHeader>
                                        <TableHeader align="right" className="w-28">
                                            {t('global.actions')}
                                        </TableHeader>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {data?.items.map((note, index) => (
                                        <TableRow key={note.id}>
                                            <TableCell className="text-gray-500">{index + 1}</TableCell>
                                            <TableCell dir="ltr">{note.date ?? '—'}</TableCell>
                                            <TableCell muted>{note.nurse_name ?? '—'}</TableCell>
                                            <TableCell muted dir="ltr">
                                                {note.time_am ?? '—'}
                                            </TableCell>
                                            <TableCell muted dir="ltr">
                                                {note.time_pm ?? '—'}
                                            </TableCell>
                                            <TableCell className="max-w-xs truncate">
                                                {note.note ?? '—'}
                                            </TableCell>
                                            <TableCell muted>{note.created_by_name ?? '—'}</TableCell>
                                            <TableCell align="right">
                                                <SectionActionButton
                                                    icon="bx-expand"
                                                    title={t('global.view')}
                                                    onClick={() => openDetails(note)}
                                                    colorClass="text-cyan-600 hover:bg-cyan-50 dark:text-cyan-400 dark:hover:bg-cyan-900/30"
                                                />
                                                {data?.permissions.edit && !isDischarged && (
                                                    <SectionActionButton
                                                        icon="bx-edit"
                                                        title={t('global.edit')}
                                                        onClick={() => openEdit(note)}
                                                        colorClass="text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                                    />
                                                )}
                                                {data?.permissions.delete && !isDischarged && (
                                                    <SectionActionButton
                                                        icon="bx-trash"
                                                        title={t('global.delete')}
                                                        onClick={() => handleDelete(note.id)}
                                                        colorClass="text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <SectionEmptyState message={t('global.no_nurse_notes_found')} />
                        )}
                    </>
                )}
            </SectionShell>

            <Modal show={formOpen} onClose={closeForm} size="7xl">
                <form onSubmit={handleSubmit}>
                    <ModalHeader>
                        {editingNoteId ? t('global.edit_nurse_note') : t('global.add_nurse_note')}
                    </ModalHeader>
                    <ModalBody>
                        <NoteFormFields
                            form={form}
                            setForm={setForm}
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
                <ModalHeader>{t('global.nurse_note_details')}</ModalHeader>
                <ModalBody className="space-y-4">
                    {selectedNote && (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.date')}
                                    </p>
                                    <p className="mt-1" dir="ltr">
                                        {selectedNote.date ?? '—'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.nurse')}
                                    </p>
                                    <p className="mt-1">{selectedNote.nurse_name ?? '—'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.created_by')}
                                    </p>
                                    <p className="mt-1">{selectedNote.created_by_name ?? '—'}</p>
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.am_time')}
                                    </p>
                                    <p className="mt-1" dir="ltr">
                                        {selectedNote.time_am ?? '—'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs font-medium uppercase text-gray-500">
                                        {t('global.pm_time')}
                                    </p>
                                    <p className="mt-1" dir="ltr">
                                        {selectedNote.time_pm ?? '—'}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <p className="text-xs font-medium uppercase text-gray-500">
                                    {t('global.note')}
                                </p>
                                <p className="mt-1 whitespace-pre-wrap">{selectedNote.note ?? '—'}</p>
                            </div>
                        </>
                    )}
                </ModalBody>
                <ModalFooter>
                    {selectedNote && data?.permissions.edit && !isDischarged && (
                        <Button color="warning" onClick={() => openEdit(selectedNote)}>
                            {t('global.edit')}
                        </Button>
                    )}
                    {selectedNote && data?.permissions.delete && !isDischarged && (
                        <Button color="failure" onClick={() => handleDelete(selectedNote.id)}>
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
