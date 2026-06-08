import { PaginationLink } from '../../types/appointment';
import { buildPaginationSummary } from '../../utils/pagination';
import SettingsPagination from '../Settings/SettingsPagination';

interface AppointmentPaginationProps {
    links: PaginationLink[];
    meta: {
        from: number | null;
        to: number | null;
        total: number;
    };
    t: (key: string) => string;
}

export default function AppointmentPagination({ links, meta, t }: AppointmentPaginationProps) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <div className="mt-4 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-4 dark:border-gray-700 sm:flex-row">
            <p className="text-sm text-gray-500 dark:text-gray-400">
                {buildPaginationSummary(meta, t)} {t('global.results')}
            </p>
            <nav aria-label="Pagination">
                <SettingsPagination links={links} />
            </nav>
        </div>
    );
}
