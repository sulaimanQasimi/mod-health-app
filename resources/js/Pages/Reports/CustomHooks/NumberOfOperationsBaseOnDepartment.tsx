import React from 'react';
import DepartmentPatientBreakdownReport from './DepartmentPatientBreakdownReport';

interface Props {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const NumberOfOperationsBaseOnDepartment: React.FC<Props> = (props) => (
    <DepartmentPatientBreakdownReport
        endpoint="/react/api/reports/general/operations"
        exportFileName="operations-by-department"
        {...props}
    />
);

export default NumberOfOperationsBaseOnDepartment;
