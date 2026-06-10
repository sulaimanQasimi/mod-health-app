import IcuListPage from '../../Components/Icus/IcuListPage';
import { IcuListFilters, IcuListUrls, PaginatedIcus } from '../../types/icu';

interface ApprovedProps {
    icus: PaginatedIcus;
    filters: IcuListFilters;
    urls: IcuListUrls;
}

export default function IcusApproved(props: ApprovedProps) {
    return (
        <IcuListPage
            titleKey="global.approved_icus"
            variant="approved"
            showDischargeTabs
            {...props}
        />
    );
}
