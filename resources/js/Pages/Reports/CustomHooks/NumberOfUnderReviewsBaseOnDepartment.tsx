import React from 'react';
import DepartmentPatientBreakdownReport from './DepartmentPatientBreakdownReport';

interface Props {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const NumberOfUnderReviewsBaseOnDepartment: React.FC<Props> = (props) => (
    <DepartmentPatientBreakdownReport
        endpoint="/react/api/reports/general/under-reviews"
        exportFileName="under-reviews-by-department"
        {...props}
    />
);

export default NumberOfUnderReviewsBaseOnDepartment;
