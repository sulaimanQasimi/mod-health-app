import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import '../../../css/ophthalmology-report-print.css';
import {
    DIAGNOSTIC_TEST_FIELDS,
    FUNDUS_STRUCTURED_FIELDS,
    OCULAR_HISTORY_FIELDS,
    REFRACTION_FIELDS,
    SLIT_LAMP_FIELDS,
    VISUAL_FIELDS,
    nestedGet,
} from '../../Components/Ophthalmology/examFields';
import { useTranslation } from '../../hooks/useTranslation';

type JsonMap = Record<string, any>;

interface PrintProps {
    registration: {
        ref_no: string;
        registration_date: string;
        status: string;
        examiner_name: string | null;
        chief_complaint: string;
        medical_history: JsonMap;
        visual_examination: JsonMap;
        refraction: JsonMap;
        slit_lamp_examination: JsonMap;
        fundus_examination: JsonMap;
        diagnostic_tests: JsonMap;
        diagnosis: string;
        diagnosis_items: Array<{ label: string; code: string; laterality: string }>;
        treatment_plan: string;
        follow_up_date: string;
        notes: string;
        fundus_image_url: string | null;
        patient: {
            name: string;
            father_name: string | null;
            id_card: string | number | null;
            age: string | null;
            gender: string | number | null;
            phone: string | null;
        };
    };
    assets: { leftLogo: string; rightLogo: string };
    generatedAt: string;
}

function val(data: JsonMap | undefined, ...path: string[]): string {
    const value = nestedGet(data, ...path);
    if (value === null || value === undefined || value === '') return '—';
    return String(value);
}

function PrintEye({
    side,
    title,
    accent,
    visualExamination,
}: {
    side: 'od' | 'os';
    title: string;
    accent: string;
    visualExamination: JsonMap;
}) {
    const gradientId = `print-iris-${side}`;
    const isOd = side === 'od';

    return (
        <div className={`eye-card ${side}`}>
            <h3>{title}</h3>
            <div className="eye-svg-wrap">
                <svg viewBox="0 0 220 160" aria-hidden>
                    <ellipse cx="110" cy="82" rx="92" ry="52" fill={`${accent}22`} />
                    <path d="M28 78 C60 28, 160 28, 192 78" fill="none" stroke="#475569" strokeWidth="4" strokeLinecap="round" />
                    <path d="M28 78 C60 128, 160 128, 192 78" fill="none" stroke="#475569" strokeWidth="4" strokeLinecap="round" />
                    <ellipse cx="110" cy="78" rx="78" ry="38" fill="#f8fafc" stroke="#64748b" strokeWidth="2" />
                    <circle cx="110" cy="78" r="28" fill={accent} />
                    <circle cx="110" cy="78" r="28" fill={`url(#${gradientId})`} opacity="0.35" />
                    <circle cx="110" cy="78" r="12" fill="#0f172a" />
                    <circle cx={isOd ? 102 : 118} cy="70" r="4.5" fill="#fff" />
                    <defs>
                        <radialGradient id={gradientId} cx="40%" cy="35%" r="65%">
                            <stop offset="0%" stopColor="#fff" stopOpacity="0.55" />
                            <stop offset="100%" stopColor="#000" stopOpacity="0.25" />
                        </radialGradient>
                    </defs>
                </svg>
            </div>
            <div className="metrics">
                {VISUAL_FIELDS.map(([key, labelKey]) => (
                    <div key={key} className="metric">
                        <div className="label">{labelKey.replace('oph_', '').toUpperCase()}</div>
                        <div className="value">{val(visualExamination, side, key)}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default function OphthalmologyRegistrationPrint({ registration, assets, generatedAt }: PrintProps) {
    const { t } = useTranslation();

    useEffect(() => {
        window.print();
    }, []);

    const genderLabel = String(registration.patient.gender) === '1'
        ? t('global.female')
        : String(registration.patient.gender) === '0'
            ? t('global.male')
            : (registration.patient.gender ?? '—');

    const positiveHistory = OCULAR_HISTORY_FIELDS.map(([key, labelKey]) => ({
        label: t(`global.${labelKey}`),
        value: nestedGet(registration.medical_history, 'ocular', key, 'value'),
        notes: nestedGet(registration.medical_history, 'ocular', key, 'notes'),
    })).filter((item) => item.value === 'yes');

    const abnormalSlit = SLIT_LAMP_FIELDS.flatMap(([key, labelKey]) =>
        (['od', 'os'] as const)
            .filter((eye) => nestedGet(registration.slit_lamp_examination, eye, key, 'status') === 'abnormal')
            .map((eye) => ({
                label: `${t(`global.${labelKey}`)} (${eye.toUpperCase()})`,
                notes: nestedGet(registration.slit_lamp_examination, eye, key, 'notes'),
            })),
    );

    const doneTests = DIAGNOSTIC_TEST_FIELDS.filter(([key]) => {
        const item = registration.diagnostic_tests?.[key];
        return item?.done || item?.od || item?.os || item?.notes;
    });

    return (
        <>
            <Head title={`${t('global.print_eye_examination')} - ${registration.ref_no}`} />
            <div className="ophthalmology-report-print">
                <div className="report-container">
                    <div className="header">
                        <div className="header-grid">
                            <div>
                                <img src={assets.leftLogo} alt="" className="logo-image" />
                            </div>
                            <div className="text-column">
                                <h2>امارت اسلامی افغانستان</h2>
                                <div>وزارت دفاع ملی</div>
                                <div>ستـــــــــــــردرستیــــــــــــز</div>
                                <div>قوماندانیت صحیه</div>
                                <div>قوماندانی اکادمی علوم طبی</div>
                                <div>{t('global.ophthalmology_department')}</div>
                            </div>
                            <div style={{ textAlign: 'left' }}>
                                <img src={assets.rightLogo} alt="" className="logo-image" />
                            </div>
                        </div>
                        <div className="report-title">{t('global.eye_examination_report')}</div>
                    </div>

                    <table className="meta-table">
                        <tbody>
                            <tr>
                                <th>{t('global.ref_no')}</th>
                                <td>{registration.ref_no}</td>
                                <th>{t('global.registration_date')}</th>
                                <td>{registration.registration_date || '—'}</td>
                            </tr>
                            <tr>
                                <th>{t('global.patient_name')}</th>
                                <td>{registration.patient.name || '—'}</td>
                                <th>{t('global.father_name')}</th>
                                <td>{registration.patient.father_name || '—'}</td>
                            </tr>
                            <tr>
                                <th>{t('global.id_card')}</th>
                                <td>{registration.patient.id_card ?? '—'}</td>
                                <th>{t('global.age')}</th>
                                <td>{registration.patient.age ?? '—'}</td>
                            </tr>
                            <tr>
                                <th>{t('global.gender')}</th>
                                <td>{genderLabel}</td>
                                <th>{t('global.phone')}</th>
                                <td>{registration.patient.phone || '—'}</td>
                            </tr>
                            <tr>
                                <th>{t('global.examiner')}</th>
                                <td>{registration.examiner_name || '—'}</td>
                                <th>{t('global.status')}</th>
                                <td>{t(`global.status_${registration.status}`)}</td>
                            </tr>
                        </tbody>
                    </table>

                    {registration.chief_complaint && (
                        <>
                            <div className="section-title">{t('global.chief_complaint')}</div>
                            <div className="notes-box">{registration.chief_complaint}</div>
                        </>
                    )}

                    {positiveHistory.length > 0 && (
                        <>
                            <div className="section-title">{t('global.oph_ocular_history')}</div>
                            <table className="data-table">
                                <tbody>
                                    {positiveHistory.map((item) => (
                                        <tr key={item.label}>
                                            <th style={{ width: '30%' }}>{item.label}</th>
                                            <td>{item.notes || t('global.yes')}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </>
                    )}

                    <div className="section-title">{t('global.visual_examination')}</div>
                    <div className="eyes-grid">
                        <PrintEye side="od" title="OD · چشم راست" accent="#0891b2" visualExamination={registration.visual_examination} />
                        <PrintEye side="os" title="OS · چشم چپ" accent="#7c3aed" visualExamination={registration.visual_examination} />
                    </div>

                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>{t('global.oph_measurement')}</th>
                                <th>OD</th>
                                <th>OS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {VISUAL_FIELDS.map(([key, labelKey]) => (
                                <tr key={key}>
                                    <th>{t(`global.${labelKey}`)}</th>
                                    <td>{val(registration.visual_examination, 'od', key)}</td>
                                    <td>{val(registration.visual_examination, 'os', key)}</td>
                                </tr>
                            ))}
                            <tr>
                                <th>{t('global.oph_iop_method')}</th>
                                <td colSpan={2}>{val(registration.visual_examination, 'iop_method')}</td>
                            </tr>
                            <tr>
                                <th>{t('global.oph_rapd')}</th>
                                <td colSpan={2}>{val(registration.visual_examination, 'rapd')}</td>
                            </tr>
                            <tr>
                                <th>{t('global.oph_squint')}</th>
                                <td colSpan={2}>{val(registration.visual_examination, 'squint_assessment')}</td>
                            </tr>
                            <tr>
                                <th>{t('global.oph_blood_pressure')}</th>
                                <td colSpan={2}>{val(registration.visual_examination, 'blood_pressure')}</td>
                            </tr>
                            <tr>
                                <th>{t('global.oph_color_vision')}</th>
                                <td colSpan={2}>{val(registration.visual_examination, 'color_vision')}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="section-title">{t('global.refraction')}</div>
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>{t('global.oph_measurement')}</th>
                                <th>OD</th>
                                <th>OS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {REFRACTION_FIELDS.map(([key, labelKey]) => (
                                <tr key={key}>
                                    <th>{t(`global.${labelKey}`)}</th>
                                    <td>{val(registration.refraction, 'od', key)}</td>
                                    <td>{val(registration.refraction, 'os', key)}</td>
                                </tr>
                            ))}
                            <tr>
                                <th>IPD</th>
                                <td colSpan={2}>{val(registration.refraction, 'ipd')}</td>
                            </tr>
                            <tr>
                                <th>{t('global.oph_refraction_type')}</th>
                                <td colSpan={2}>{val(registration.refraction, 'type')}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="section-title">{t('global.oph_glasses_rx')}</div>
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th />
                                <th>SPH</th>
                                <th>CYL</th>
                                <th>Axis</th>
                                <th>ADD</th>
                                <th>Prism H</th>
                                <th>Prism V</th>
                            </tr>
                        </thead>
                        <tbody>
                            {(['od', 'os'] as const).map((eye) => (
                                <tr key={eye}>
                                    <th>{eye.toUpperCase()}</th>
                                    <td>{val(registration.refraction, eye, 'sphere')}</td>
                                    <td>{val(registration.refraction, eye, 'cylinder')}</td>
                                    <td>{val(registration.refraction, eye, 'axis')}</td>
                                    <td>{val(registration.refraction, eye, 'add')}</td>
                                    <td>{val(registration.refraction, eye, 'prism_horizontal')}</td>
                                    <td>{val(registration.refraction, eye, 'prism_vertical')}</td>
                                </tr>
                            ))}
                            <tr>
                                <th>IPD</th>
                                <td colSpan={6}>{val(registration.refraction, 'ipd')}</td>
                            </tr>
                        </tbody>
                    </table>

                    {abnormalSlit.length > 0 && (
                        <>
                            <div className="section-title">{t('global.slit_lamp_examination')}</div>
                            <table className="data-table">
                                <tbody>
                                    {abnormalSlit.map((item) => (
                                        <tr key={item.label}>
                                            <th style={{ width: '35%' }}>{item.label}</th>
                                            <td>{item.notes || t('global.oph_abnormal')}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </>
                    )}

                    <div className="section-title">{t('global.fundus_examination')}</div>
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>{t('global.oph_finding')}</th>
                                <th>OD</th>
                                <th>OS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {FUNDUS_STRUCTURED_FIELDS.map(([key, labelKey]) => (
                                <tr key={key}>
                                    <th>{t(`global.${labelKey}`)}</th>
                                    <td>{val(registration.fundus_examination, 'od', key)}</td>
                                    <td>{val(registration.fundus_examination, 'os', key)}</td>
                                </tr>
                            ))}
                            <tr>
                                <th>{t('global.notes')}</th>
                                <td>{val(registration.fundus_examination, 'od_findings')}</td>
                                <td>{val(registration.fundus_examination, 'os_findings')}</td>
                            </tr>
                            <tr>
                                <th>{t('global.oph_dilation_status')}</th>
                                <td colSpan={2}>{val(registration.fundus_examination, 'dilation_status')}</td>
                            </tr>
                        </tbody>
                    </table>

                    {doneTests.length > 0 && (
                        <>
                            <div className="section-title">{t('global.diagnostic_tests')}</div>
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>{t('global.oph_test')}</th>
                                        <th>OD</th>
                                        <th>OS</th>
                                        <th>{t('global.notes')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {doneTests.map(([key, labelKey]) => (
                                        <tr key={key}>
                                            <th>{t(`global.${labelKey}`)}</th>
                                            <td>{val(registration.diagnostic_tests, key, 'od')}</td>
                                            <td>{val(registration.diagnostic_tests, key, 'os')}</td>
                                            <td>{val(registration.diagnostic_tests, key, 'notes')}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </>
                    )}

                    <div className="section-title">{t('global.assessment_and_plan')}</div>
                    <table className="data-table">
                        <tbody>
                            {registration.diagnosis_items?.length > 0 && (
                                <tr>
                                    <th style={{ width: '22%' }}>{t('global.oph_diagnosis_items')}</th>
                                    <td>
                                        {registration.diagnosis_items.map((item, index) => (
                                            <div key={`${item.code}-${index}`}>
                                                {[item.code, item.label, item.laterality?.toUpperCase()].filter(Boolean).join(' · ')}
                                            </div>
                                        ))}
                                    </td>
                                </tr>
                            )}
                            <tr>
                                <th style={{ width: '22%' }}>{t('global.diagnosis')}</th>
                                <td>{registration.diagnosis || '—'}</td>
                            </tr>
                            <tr>
                                <th>{t('global.treatment_plan')}</th>
                                <td>{registration.treatment_plan || '—'}</td>
                            </tr>
                            <tr>
                                <th>{t('global.follow_up_date')}</th>
                                <td>{registration.follow_up_date || '—'}</td>
                            </tr>
                            <tr>
                                <th>{t('global.notes')}</th>
                                <td>{registration.notes || '—'}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="signatures">
                        <div>
                            <div className="signature-line">{t('global.examiner')}</div>
                        </div>
                        <div>
                            <div className="signature-line">{t('global.doctor')}</div>
                        </div>
                    </div>

                    <div className="footer">
                        <p>{t('global.report_generated_on')}: {generatedAt}</p>
                        <div className="no-print">
                            <button type="button" className="print-button" onClick={() => window.print()}>
                                {t('global.print')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
