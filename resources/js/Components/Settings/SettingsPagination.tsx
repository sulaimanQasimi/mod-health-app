import { PaginationLink } from '../../types/settings';
import { renderPaginationLink } from '../../utils/pagination';

interface SettingsPaginationProps {
    links: PaginationLink[];
    only?: string[];
}

export default function SettingsPagination({ links, only }: SettingsPaginationProps) {
    if (links.length === 0) {
        return null;
    }

    return (
        <ul className="mt-6 inline-flex -space-x-px text-sm">
            {links.map((link, index) => renderPaginationLink(link, index, only))}
        </ul>
    );
}
