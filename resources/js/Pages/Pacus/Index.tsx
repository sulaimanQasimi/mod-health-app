import PacuListPage from '../../Components/Pacus/PacuListPage';
import { PacuListFilters, PacuListUrls, PaginatedPacus } from '../../types/pacu';

interface IndexProps {
    pacus: PaginatedPacus;
    filters: PacuListFilters;
    urls: PacuListUrls;
}

export default function PacusIndex(props: IndexProps) {
    return <PacuListPage titleKey="global.new_pacus" variant="new" {...props} />;
}
