import React from 'react';
import DepartmentPatientBreakdownReport from './DepartmentPatientBreakdownReport';

interface Props {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const NumberOfIcusBaseOnDepartment: React.FC<Props> = (props) => (
    <DepartmentPatientBreakdownReport
        endpoint="/react/api/reports/general/icus"
        exportFileName="icus-by-department"
        {...props}
    />
);

export default NumberOfIcusBaseOnDepartment;
