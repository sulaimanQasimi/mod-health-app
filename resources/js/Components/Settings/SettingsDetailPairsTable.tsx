import { ReactNode } from 'react';

export interface DetailPairCell {
    label: string;
    value: ReactNode;
}

export interface DetailPairRow {
    cells: DetailPairCell[];
    fullWidth?: boolean;
}

interface SettingsDetailPairsTableProps {
    rows: DetailPairRow[];
}

export default function SettingsDetailPairsTable({ rows }: SettingsDetailPairsTableProps) {
    return (
        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table className="w-full text-sm">
                <tbody>
                    {rows.map((row, rowIndex) => (
                        <tr
                            key={rowIndex}
                            className="border-b border-gray-200 last:border-b-0 dark:border-gray-700"
                        >
                            {row.fullWidth && row.cells.length === 1 ? (
                                <>
                                    <th className="w-1/4 bg-gray-50 px-4 py-3 text-start font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        {row.cells[0].label}
                                    </th>
                                    <td colSpan={3} className="px-4 py-3 text-gray-900 dark:text-white">
                                        {row.cells[0].value}
                                    </td>
                                </>
                            ) : (
                                row.cells.flatMap((cell, cellIndex) => [
                                    <th
                                        key={`${rowIndex}-${cellIndex}-label`}
                                        className="w-1/4 bg-gray-50 px-4 py-3 text-start font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    >
                                        {cell.label}
                                    </th>,
                                    <td
                                        key={`${rowIndex}-${cellIndex}-value`}
                                        className="w-1/4 px-4 py-3 text-gray-900 dark:text-white"
                                    >
                                        {cell.value}
                                    </td>,
                                ])
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
