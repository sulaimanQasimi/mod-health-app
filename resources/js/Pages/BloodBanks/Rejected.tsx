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

export default function BloodBanksRejected(props: Props) {
    return <BloodBankListPage titleKey="global.rejected_blood_requests" variant="rejected" {...props} />;
}
