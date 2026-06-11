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

export default function BloodBanksNew(props: Props) {
    return <BloodBankListPage titleKey="global.new_blood_requests" variant="new" {...props} />;
}
