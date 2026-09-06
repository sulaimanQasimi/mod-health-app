import React from 'react';
import DepartmentPatientBreakdownReport from './DepartmentPatientBreakdownReport';

interface Props {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const NumberOfAnesthesiasBaseOnDepartment: React.FC<Props> = (props) => (
    <DepartmentPatientBreakdownReport
        endpoint="/api/reports/general/anesthesias"
        exportFileName="anesthesias-by-department"
        {...props}
    />
);

export default NumberOfAnesthesiasBaseOnDepartment;
