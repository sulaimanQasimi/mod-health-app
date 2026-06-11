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

export default function OperationsNew(props: Props) {
    return <OperationListPage titleKey="global.new_operations" variant="new" {...props} />;
}
