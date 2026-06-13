import PacuListPage from '../../Components/Pacus/PacuListPage';
import { PacuListFilters, PacuListUrls, PaginatedPacus } from '../../types/pacu';

interface CompletedProps {
    pacus: PaginatedPacus;
    filters: PacuListFilters;
    urls: PacuListUrls;
}

export default function PacusCompleted(props: CompletedProps) {
    return <PacuListPage titleKey="global.completed_pacus" variant="completed" {...props} />;
}
