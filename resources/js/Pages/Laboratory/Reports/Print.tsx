import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import '../../../../css/laboratory-report-print.css';
import { useTranslation } from '../../../hooks/useTranslation';
import {
    LaboratoryPrintAssets,
    LaboratoryPrintPatient,
    LaboratoryPrintRegistration,
    LaboratoryPrintResultRow,
} from '../../../types/laboratory';

interface PrintProps {
    patient: LaboratoryPrintPatient | null;
    registration: LaboratoryPrintRegistration;
    results: LaboratoryPrintResultRow[];
    hasParameters: boolean;
    hasTextResult: boolean;
    textResult: string | null;
    expectedParameters: LaboratoryPrintResultRow[];
    generatedAt: string;
    assets: LaboratoryPrintAssets;
}

export default function Print({
    patient,
    registration,
    results,
    hasParameters,
    hasTextResult,
    textResult,
    expectedParameters,
    generatedAt,
    assets,
}: PrintProps) {
    const { t } = useTranslation();

    useEffect(() => {
        window.print();
    }, []);

    const parameterResults = results.filter((row) => row.parameter_name);

    return (
        <>
            <Head title={`${t('global.laboratory_test_report')} - ${patient?.name ?? 'Unknown'}`} />

            <div className="lab-report-print">
                <div className="report-container">
                    <div className="header">
                        <div className="header-grid">
                            <div className="logo-container logo-left">
                                <img src={assets.leftLogo} alt="Left Logo" className="logo-image" />
                            </div>

                            <div className="text-column text-column-left">
                                <h2>امارت اسلامی افغانستان</h2>
                                <div className="font-bold">وزارت دفاع ملی</div>
                                <div className="font-bold">ستـــــــــــــردرستیــــــــــــز</div>
                                <div className="font-bold">قوماندانیت صحیه</div>
                                <div className="font-bold">قوماندانی اکادمی علوم طبی</div>
                                <div className="font-bold">{registration.assigned_section_name ?? '—'}</div>
                            </div>

                            <div className="logo-container logo-right">
                                <img src={assets.rightLogo} alt="Right Logo" className="logo-image" />
                            </div>
                        </div>
                    </div>

                    {patient && (
                        <div>
                            <table className="patient-details">
                                <tbody>
                                    <tr>
                                        <th>{t('global.name')}</th>
                                        <td>{patient.name}</td>
                                        <th>{t('global.father_name')}</th>
                                        <td>{patient.father_name ?? '—'}</td>
                                        <th>{t('global.age')}</th>
                                        <td>{patient.age ?? '—'}</td>
                                    </tr>
                                    <tr>
                                        <th>{t('global.phone')}</th>
                                        <td>{patient.phone ?? '—'}</td>
                                        <th>{t('global.gender')}</th>
                                        <td>{patient.gender ?? '—'}</td>
                                    </tr>
                                    {patient.id_number && (
                                        <tr>
                                            <th>{t('global.id_number')}</th>
                                            <td>{patient.id_number}</td>
                                        </tr>
                                    )}
                                    {patient.date_of_birth && (
                                        <tr>
                                            <th>{t('global.date_of_birth')}</th>
                                            <td>{patient.date_of_birth}</td>
                                        </tr>
                                    )}
                                    {patient.email && (
                                        <tr>
                                            <th>{t('global.email')}</th>
                                            <td>{patient.email}</td>
                                        </tr>
                                    )}
                                    {patient.emergency_contact && (
                                        <tr>
                                            <th>{t('global.emergency_contact')}</th>
                                            <td>{patient.emergency_contact}</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    )}

                    <div className="test-section">
                        <div className="test-details">
                            <div className="category-banner">{registration.category_name ?? '—'}</div>
                            <div className="lab-type-title">{registration.lab_type_name ?? '—'}</div>

                            <table className="test-meta">
                                <tbody>
                                    <tr>
                                        <th>{t('global.reference_number')}</th>
                                        <td>{registration.ref_no ?? '—'}</td>
                                        <th>{t('global.doctor')}</th>
                                        <td>{registration.doctor_name ?? '—'}</td>
                                        <th>{t('global.registration_date')}</th>
                                        <td>{registration.registration_date ?? '—'}</td>
                                        {registration.completed_at && (
                                            <>
                                                <th>{t('global.completed_date')}</th>
                                                <td>{registration.completed_at}</td>
                                            </>
                                        )}
                                    </tr>
                                    <tr>
                                        {registration.assigned_to_name && (
                                            <>
                                                <th>{t('global.assigned_to')}</th>
                                                <td>{registration.assigned_to_name}</td>
                                            </>
                                        )}
                                        {registration.assigned_section_name && (
                                            <>
                                                <th>{t('global.assigned_section')}</th>
                                                <td>{registration.assigned_section_name}</td>
                                            </>
                                        )}
                                        {registration.category_name && (
                                            <>
                                                <th>{t('global.test_category')}</th>
                                                <td>{registration.category_name}</td>
                                            </>
                                        )}
                                    </tr>
                                </tbody>
                            </table>

                            {hasParameters ? (
                                <>
                                    <table className="parameters-table">
                                        <thead>
                                            <tr>
                                                <th style={{ textAlign: 'center', direction: 'ltr' }}>
                                                    Reference Value
                                                </th>
                                                <th style={{ textAlign: 'center', direction: 'ltr' }}>Unit</th>
                                                <th style={{ textAlign: 'center', direction: 'ltr' }}>Result</th>
                                                <th style={{ textAlign: 'center', direction: 'ltr' }}>
                                                    Investigation
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {parameterResults.map((row, index) => (
                                                <tr key={`${row.parameter_name}-${index}`}>
                                                    <td
                                                        className="normal-range"
                                                        style={{ textAlign: 'center' }}
                                                    >
                                                        {row.normal_range ?? '—'}
                                                    </td>
                                                    <td className="unit" style={{ textAlign: 'center' }}>
                                                        {row.unit ?? '—'}
                                                    </td>
                                                    <td
                                                        className="result-value"
                                                        style={{ textAlign: 'center' }}
                                                    >
                                                        {row.result ?? '—'}
                                                    </td>
                                                    <td style={{ textAlign: 'center' }}>
                                                        {row.parameter_name ?? '—'}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>

                                    {expectedParameters.length > 0 && parameterResults.length === 0 && (
                                        <div className="expected-parameters">
                                            <h4 style={{ marginBottom: '15px', color: '#333' }}>
                                                {t('global.expected_parameters')}
                                            </h4>
                                            <table className="parameters-table">
                                                <thead>
                                                    <tr>
                                                        <th style={{ textAlign: 'center', direction: 'ltr' }}>
                                                            Reference Value
                                                        </th>
                                                        <th style={{ textAlign: 'center', direction: 'ltr' }}>
                                                            Unit
                                                        </th>
                                                        <th style={{ textAlign: 'center', direction: 'ltr' }}>
                                                            Investigation
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {expectedParameters.map((row, index) => (
                                                        <tr key={`${row.parameter_name}-${index}`}>
                                                            <td
                                                                className="normal-range"
                                                                style={{ textAlign: 'center' }}
                                                            >
                                                                {row.normal_range ?? '—'}
                                                            </td>
                                                            <td className="unit" style={{ textAlign: 'center' }}>
                                                                {row.unit ?? '—'}
                                                            </td>
                                                            <td style={{ textAlign: 'center' }}>
                                                                {row.parameter_name ?? '—'}
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}
                                </>
                            ) : hasTextResult ? (
                                <div className="text-result-section">
                                    <h4 style={{ marginBottom: '15px', color: '#333' }}>
                                        {t('global.test_result')}
                                    </h4>
                                    <div
                                        className="text-result-content"
                                        dangerouslySetInnerHTML={{
                                            __html:
                                                textResult ??
                                                t('global.no_result_available'),
                                        }}
                                    />
                                </div>
                            ) : (
                                <div
                                    style={{
                                        textAlign: 'center',
                                        padding: '20px',
                                        color: '#6c757d',
                                        direction: 'rtl',
                                    }}
                                >
                                    {t('global.no_results_available')}
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="footer">
                        <p>
                            {t('global.report_generated_on')}: {generatedAt}
                        </p>
                        <p>{t('global.laboratory_system')}</p>

                        <div className="no-print">
                            <button
                                type="button"
                                className="print-button"
                                onClick={() => window.print()}
                            >
                                {t('global.print_report')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
