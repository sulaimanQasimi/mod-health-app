import BloodBankListPage from '../../Components/BloodBanks/BloodBankListPage';
import {
    BloodBankListUrls,
    BloodRequestFilterOptions,
    BloodRequestListFilters,
    PaginatedBloodRequests,
} from '../../types/bloodBank';

interface Props {
    bloodRequests: PaginatedBloodRequests;
    filters: BloodRequestListFilters;
    urls: BloodBankListUrls & { current: string };
    filterOptions: BloodRequestFilterOptions;
}

export default function BloodBanksDelivered(props: Props) {
    return <BloodBankListPage titleKey="global.delivered_blood_requests" variant="delivered" {...props} />;
}
