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

export default function BloodBanksApproved(props: Props) {
    return <BloodBankListPage titleKey="global.approved_blood_requests" variant="approved" {...props} />;
}
