import { PatientFormPage, PatientFormPageProps } from './Create';

type EditPatientProps = Omit<PatientFormPageProps, 'mode'>;

export default function EditPatient(props: EditPatientProps) {
    return <PatientFormPage {...props} mode="edit" />;
}
