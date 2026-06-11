import OperationListPage from '../../Components/Operations/OperationListPage';
import {
    OperationListFilters,
    OperationListUrls,
    PaginatedOperations,
    SelectOption,
} from '../../types/operation';

interface Props {
    operations: PaginatedOperations;
    filters: OperationListFilters;
    urls: OperationListUrls & { current: string };
    filterOptions: {
        branches: SelectOption[];
        departments: SelectOption[];
        operationTypes: SelectOption[];
        surgeons: SelectOption[];
    };
}

export default function OperationsApproved(props: Props) {
    return <OperationListPage titleKey="global.approved_operations" variant="approved" {...props} />;
}
