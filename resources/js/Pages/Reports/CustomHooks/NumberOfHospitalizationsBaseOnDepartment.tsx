import React from 'react';
import DepartmentPatientBreakdownReport from './DepartmentPatientBreakdownReport';

interface Props {
    branch_id?: string | number;
    date_from?: string;
    date_to?: string;
}

const NumberOfHospitalizationsBaseOnDepartment: React.FC<Props> = (props) => (
    <DepartmentPatientBreakdownReport
        endpoint="/api/reports/general/hospitalization"
        exportFileName="hospitalizations-by-department"
        {...props}
    />
);

export default NumberOfHospitalizationsBaseOnDepartment;
