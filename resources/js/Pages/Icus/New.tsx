import IcuListPage from '../../Components/Icus/IcuListPage';
import { IcuListFilters, IcuListUrls, PaginatedIcus } from '../../types/icu';

interface NewProps {
    icus: PaginatedIcus;
    filters: IcuListFilters;
    urls: IcuListUrls;
}

export default function IcusNew(props: NewProps) {
    return <IcuListPage titleKey="global.new_icus" variant="new" {...props} />;
}
