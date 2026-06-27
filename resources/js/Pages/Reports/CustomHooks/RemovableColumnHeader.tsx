export default function RemovableColumnHeader({
    columnName,
    onRemove,
    removeLabel,
    isFirstInGroup,
}: {
    columnName: string;
    onRemove: () => void;
    removeLabel: string;
    isFirstInGroup: boolean;
}) {
    return (
        <th
            className={`h-28 min-w-12 border-b border-gray-200 bg-gray-100 p-0 align-middle text-center dark:border-gray-700 dark:bg-gray-800 ${
                isFirstInGroup ? 'border-s-2 border-s-gray-300 dark:border-s-gray-600' : ''
            }`}
        >
            <button
                type="button"
                onClick={onRemove}
                title={removeLabel}
                aria-label={`${removeLabel} ${columnName}`}
                className="group/col-action relative flex h-full w-full items-center justify-center overflow-hidden transition hover:bg-red-50 dark:hover:bg-red-950/30"
            >
                <span
                    className="inline-block origin-center -rotate-90 whitespace-nowrap text-xs font-semibold leading-none text-gray-700 transition-opacity group-hover/col-action:opacity-0 dark:text-gray-200"
                    title={columnName}
                >
                    {columnName}
                </span>
                <i className="bx bx-trash absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-lg text-red-500 opacity-0 transition-opacity group-hover/col-action:opacity-100" />
            </button>
        </th>
    );
}
