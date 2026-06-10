import AnesthesiaListPage from '../../Components/Anesthesias/AnesthesiaListPage';
import {
    AnesthesiaListFilters,
    AnesthesiaListUrls,
    PaginatedAnesthesias,
    SelectOption,
} from '../../types/anesthesia';

interface ApprovedProps {
    anesthesias: PaginatedAnesthesias;
    filters: AnesthesiaListFilters;
    urls: AnesthesiaListUrls;
    filterOptions: {
        operationTypes: SelectOption[];
        departments: SelectOption[];
    };
}

export default function AnesthesiasApproved(props: ApprovedProps) {
    return <AnesthesiaListPage titleKey="global.approved_anesthesias" variant="approved" {...props} />;
}
