import IcuListPage from '../../Components/Icus/IcuListPage';
import { IcuListFilters, IcuListUrls, PaginatedIcus } from '../../types/icu';

interface RejectedProps {
    icus: PaginatedIcus;
    filters: IcuListFilters;
    urls: IcuListUrls;
}

export default function IcusRejected(props: RejectedProps) {
    return <IcuListPage titleKey="global.rejected_icus" variant="rejected" {...props} />;
}
