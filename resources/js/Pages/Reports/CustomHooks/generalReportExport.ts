import { ColumnGroupView } from './generalReportTableSettings';

export interface GeneralReportExportRow {
    rowLabel: string;
    cells: Record<string, string | number>;
    total: string | number;
}

export interface GeneralReportExportData {
    fileName: string;
    indexLabel: string;
    rowLabelColumn: string;
    totalLabel: string;
    columnGroups: ColumnGroupView[];
    rows: GeneralReportExportRow[];
    totalsRow?: GeneralReportExportRow;
}

interface BuildGeneralReportExportDataParams<TRow> {
    fileName: string;
    indexLabel: string;
    rowLabelColumn: string;
    totalLabel: string;
    visibleColumnGroups: ColumnGroupView[];
    displayReport: TRow[];
    getRowLabel: (row: TRow) => string;
    getCellValue: (row: TRow, columnId: string) => string | number;
    getRowTotal: (row: TRow) => string | number;
    showTotalsRow: boolean;
    totalsLabel: string;
    columnTotals: Map<string, number>;
    grandTotal: number;
}

function escapeHtml(value: string | number): string {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function downloadBlob(blob: Blob, filename: string): void {
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
}

function getFlatColumnIds(columnGroups: ColumnGroupView[]): string[] {
    return columnGroups.flatMap((group) => group.columns.map((column) => column.id));
}

function buildExportTableHtml(data: GeneralReportExportData): string {
    const flatColumns = data.columnGroups.flatMap((group) => group.columns);

    let html = '<table border="1" dir="rtl">';

    html += '<tr>';
    html += `<th rowspan="2">${escapeHtml(data.indexLabel)}</th>`;
    html += `<th rowspan="2">${escapeHtml(data.rowLabelColumn)}</th>`;
    data.columnGroups.forEach((group) => {
        html += `<th colspan="${group.columns.length}">${escapeHtml(group.title)}</th>`;
    });
    html += `<th rowspan="2">${escapeHtml(data.totalLabel)}</th>`;
    html += '</tr>';

    html += '<tr>';
    data.columnGroups.forEach((group) => {
        group.columns.forEach((column) => {
            html += `<th>${escapeHtml(column.name)}</th>`;
        });
    });
    html += '</tr>';

    data.rows.forEach((row, index) => {
        html += '<tr>';
        html += `<td>${index + 1}</td>`;
        html += `<td>${escapeHtml(row.rowLabel)}</td>`;
        flatColumns.forEach((column) => {
            html += `<td>${escapeHtml(row.cells[column.id] ?? 0)}</td>`;
        });
        html += `<td>${escapeHtml(row.total)}</td>`;
        html += '</tr>';
    });

    if (data.totalsRow) {
        html += '<tr>';
        html += '<td></td>';
        html += `<td>${escapeHtml(data.totalsRow.rowLabel)}</td>`;
        flatColumns.forEach((column) => {
            html += `<td>${escapeHtml(data.totalsRow!.cells[column.id] ?? 0)}</td>`;
        });
        html += `<td>${escapeHtml(data.totalsRow.total)}</td>`;
        html += '</tr>';
    }

    html += '</table>';
    return html;
}

export function buildGeneralReportExportData<TRow>({
    fileName,
    indexLabel,
    rowLabelColumn,
    totalLabel,
    visibleColumnGroups,
    displayReport,
    getRowLabel,
    getCellValue,
    getRowTotal,
    showTotalsRow,
    totalsLabel,
    columnTotals,
    grandTotal,
}: BuildGeneralReportExportDataParams<TRow>): GeneralReportExportData | null {
    if (displayReport.length === 0 || visibleColumnGroups.length === 0) {
        return null;
    }

    const flatColumnIds = getFlatColumnIds(visibleColumnGroups);

    const rows: GeneralReportExportRow[] = displayReport.map((row) => {
        const cells: Record<string, string | number> = {};
        flatColumnIds.forEach((columnId) => {
            cells[columnId] = getCellValue(row, columnId);
        });

        return {
            rowLabel: getRowLabel(row),
            cells,
            total: getRowTotal(row),
        };
    });

    let totalsRow: GeneralReportExportRow | undefined;

    if (showTotalsRow && flatColumnIds.length > 0) {
        const cells: Record<string, string | number> = {};
        flatColumnIds.forEach((columnId) => {
            cells[columnId] = columnTotals.get(columnId) ?? 0;
        });

        totalsRow = {
            rowLabel: totalsLabel,
            cells,
            total: grandTotal,
        };
    }

    return {
        fileName,
        indexLabel,
        rowLabelColumn,
        totalLabel,
        columnGroups: visibleColumnGroups,
        rows,
        totalsRow,
    };
}

export function exportGeneralReportToExcel(data: GeneralReportExportData): void {
    const tableHtml = buildExportTableHtml(data);
    const html = `<!DOCTYPE html><html dir="rtl"><head><meta charset="UTF-8"></head><body>${tableHtml}</body></html>`;
    const blob = new Blob(['\ufeff', html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    downloadBlob(blob, `${data.fileName}.xls`);
}

export function exportGeneralReportToPdf(data: GeneralReportExportData): void {
    const tableHtml = buildExportTableHtml(data);
    const printWindow = window.open('', '_blank');

    if (!printWindow) {
        return;
    }

    printWindow.document.write(`<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>${escapeHtml(data.fileName)}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { margin: 0; font-family: Arial, sans-serif; direction: rtl; }
        table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        th, td { border: 1px solid #333; padding: 4px; text-align: center; }
        th { background: #eee; font-weight: bold; }
        td:nth-child(2) { text-align: right; }
    </style>
</head>
<body>${tableHtml}</body>
</html>`);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}
