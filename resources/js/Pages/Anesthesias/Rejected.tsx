import AnesthesiaListPage from '../../Components/Anesthesias/AnesthesiaListPage';
import {
    AnesthesiaListFilters,
    AnesthesiaListUrls,
    PaginatedAnesthesias,
    SelectOption,
} from '../../types/anesthesia';

interface RejectedProps {
    anesthesias: PaginatedAnesthesias;
    filters: AnesthesiaListFilters;
    urls: AnesthesiaListUrls;
    filterOptions: {
        operationTypes: SelectOption[];
        departments: SelectOption[];
    };
}

export default function AnesthesiasRejected(props: RejectedProps) {
    return <AnesthesiaListPage titleKey="global.rejected_anesthesias" variant="rejected" {...props} />;
}
