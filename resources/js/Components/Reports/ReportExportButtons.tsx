import { SettingsPageActions } from '../Settings/SettingsPageHeader';

interface ReportExportButtonsProps {
    action: string;
    method?: 'GET' | 'POST';
    csrfToken?: string;
    fields: Record<string, string>;
    typeField?: string;
    excelValue?: string;
    pdfValue?: string;
    excelLabel?: string;
    pdfLabel?: string;
    showPdf?: boolean;
}

export default function ReportExportButtons({
    action,
    method = 'POST',
    csrfToken,
    fields,
    typeField = 'type',
    excelValue = 'excel',
    pdfValue = 'pdf',
    excelLabel = 'Excel',
    pdfLabel = 'PDF',
    showPdf = true,
}: ReportExportButtonsProps) {
    return (
        <SettingsPageActions>
            <form action={action} method={method} target="_blank" className="inline-flex gap-2">
                {method === 'POST' && csrfToken ? (
                    <input type="hidden" name="_token" value={csrfToken} />
                ) : null}
                {Object.entries(fields).map(([key, value]) => (
                    <input key={key} type="hidden" name={key} value={value} />
                ))}
                <button
                    type="submit"
                    name={typeField}
                    value={excelValue}
                    className="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300"
                >
                    <i className="bx bx-spreadsheet" />
                    {excelLabel}
                </button>
                {showPdf ? (
                    <button
                        type="submit"
                        name={typeField}
                        value={pdfValue}
                        className="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300"
                    >
                        <i className="bx bx-file" />
                        {pdfLabel}
                    </button>
                ) : null}
            </form>
        </SettingsPageActions>
    );
}
