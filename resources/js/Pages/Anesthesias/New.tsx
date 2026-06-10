import AnesthesiaListPage from '../../Components/Anesthesias/AnesthesiaListPage';
import {
    AnesthesiaListFilters,
    AnesthesiaListUrls,
    PaginatedAnesthesias,
    SelectOption,
} from '../../types/anesthesia';

interface NewProps {
    anesthesias: PaginatedAnesthesias;
    filters: AnesthesiaListFilters;
    urls: AnesthesiaListUrls;
    filterOptions: {
        operationTypes: SelectOption[];
        departments: SelectOption[];
    };
}

export default function AnesthesiasNew(props: NewProps) {
    return <AnesthesiaListPage titleKey="global.new_anesthesias" variant="new" {...props} />;
}
