import { Link } from '@inertiajs/react';
import { PaginationLink } from '../types/settings';

export function decodePaginationLabel(label: string): string {
    return label
        .replace('&laquo;', '«')
        .replace('&raquo;', '»')
        .replace(/&[^;]+;/g, '')
        .trim();
}

export function renderPaginationLink(link: PaginationLink, index: number) {
    const label = decodePaginationLabel(link.label);
    const isPrevious = label === '«' || label.toLowerCase().includes('previous');
    const isNext = label === '»' || label.toLowerCase().includes('next');
    const isEllipsis = label === '...';

    if (isEllipsis) {
        return (
            <li key={`ellipsis-${index}`}>
                <span className="flex h-9 items-center border border-gray-300 bg-white px-3 leading-tight text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    ...
                </span>
            </li>
        );
    }

    const baseClass = 'flex h-9 items-center border border-gray-300 px-3 leading-tight dark:border-gray-700';
    const activeClass =
        'z-10 border-blue-300 bg-blue-50 text-blue-600 dark:border-gray-700 dark:bg-gray-700 dark:text-white';
    const inactiveClass =
        'bg-white text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white';
    const disabledClass = 'cursor-not-allowed bg-white text-gray-300 dark:bg-gray-800 dark:text-gray-600';
    const roundedClass = isPrevious ? 'rounded-s-lg' : isNext ? 'rounded-e-lg' : '';

    if (!link.url) {
        return (
            <li key={`${label}-${index}`}>
                <span className={`${baseClass} ${disabledClass} ${roundedClass}`}>
                    {isPrevious ? (
                        <i className="bx bx-chevron-left text-lg" />
                    ) : isNext ? (
                        <i className="bx bx-chevron-right text-lg" />
                    ) : (
                        label
                    )}
                </span>
            </li>
        );
    }

    return (
        <li key={`${label}-${index}`}>
            <Link
                href={link.url}
                preserveScroll
                className={`${baseClass} ${link.active ? activeClass : inactiveClass} ${roundedClass}`}
            >
                {isPrevious ? (
                    <i className="bx bx-chevron-left text-lg" />
                ) : isNext ? (
                    <i className="bx bx-chevron-right text-lg" />
                ) : (
                    label
                )}
            </Link>
        </li>
    );
}
