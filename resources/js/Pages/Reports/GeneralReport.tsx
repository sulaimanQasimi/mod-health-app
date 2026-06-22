import NumberOfPatientsBaseOnDepartment from './CustomHooks/NumberOfPatientsBaseOnDepartment';
import NumberOfPatientsBaseOnPatientMiliteryTypes from './CustomHooks/NumberOfPatientsBaseOnPatientMiliteryTypes';

export default function GeneralReport() {

    return (
        <div>
            <NumberOfPatientsBaseOnDepartment />
            <NumberOfPatientsBaseOnPatientMiliteryTypes />
        </div>
    );
}