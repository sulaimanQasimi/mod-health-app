import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import '../../../css/ophthalmology-report-print.css';
import { useTranslation } from '../../hooks/useTranslation';

type JsonMap = Record<string, any>;

interface PrintProps {
    registration: {
        ref_no: string;
        registration_date: string;
        status: string;
        examiner_name: string | null;
        chief_complaint: string;
        visual_examination: JsonMap;
        refraction: JsonMap;
        diagnosis: string;
        treatment_plan: string;
        follow_up_date: string;
        notes: string;
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

const VISUAL_FIELDS: Array<[string, string]> = [
    ['visual_acuity', 'حدت بینایی (VA)'],
    ['pinhole_vision', 'دید با سوراخ سوزنی (PH)'],
    ['vision_with_glasses', 'دید با عینک'],
    ['near_vision', 'دید نزدیک'],
    ['intraocular_pressure', 'فشار داخل چشم (IOP)'],
];

const REFRACTION_FIELDS: Array<[string, string]> = [
    ['sphere', 'SPH'],
    ['cylinder', 'CYL'],
    ['axis', 'Axis'],
    ['distance_vision', 'دید دور'],
    ['near_vision', 'دید نزدیک'],
    ['recommended_prescription', 'نسخه پیشنهادی'],
];

function val(data: JsonMap | undefined, ...path: string[]): string {
    let cursor: any = data;
    for (const part of path) {
        cursor = cursor?.[part];
    }
    if (cursor === null || cursor === undefined || cursor === '') return '—';
    return String(cursor);
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
                {VISUAL_FIELDS.map(([key, label]) => (
                    <div key={key} className="metric">
                        <div className="label">{label}</div>
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

                    <div className="section-title">{t('global.visual_examination')}</div>
                    <div className="eyes-grid">
                        <PrintEye
                            side="od"
                            title="OD · چشم راست"
                            accent="#0891b2"
                            visualExamination={registration.visual_examination}
                        />
                        <PrintEye
                            side="os"
                            title="OS · چشم چپ"
                            accent="#7c3aed"
                            visualExamination={registration.visual_examination}
                        />
                    </div>

                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>اندازه‌گیری</th>
                                <th>OD</th>
                                <th>OS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {VISUAL_FIELDS.map(([key, label]) => (
                                <tr key={key}>
                                    <th>{label}</th>
                                    <td>{val(registration.visual_examination, 'od', key)}</td>
                                    <td>{val(registration.visual_examination, 'os', key)}</td>
                                </tr>
                            ))}
                            <tr>
                                <th>انحراف چشم</th>
                                <td colSpan={2}>{val(registration.visual_examination, 'squint_assessment')}</td>
                            </tr>
                            <tr>
                                <th>فشار خون</th>
                                <td colSpan={2}>{val(registration.visual_examination, 'blood_pressure')}</td>
                            </tr>
                            <tr>
                                <th>دید رنگی</th>
                                <td colSpan={2}>{val(registration.visual_examination, 'color_vision')}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="section-title">{t('global.refraction')}</div>
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>اندازه‌گیری</th>
                                <th>OD</th>
                                <th>OS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {REFRACTION_FIELDS.map(([key, label]) => (
                                <tr key={key}>
                                    <th>{label}</th>
                                    <td>{val(registration.refraction, 'od', key)}</td>
                                    <td>{val(registration.refraction, 'os', key)}</td>
                                </tr>
                            ))}
                            <tr>
                                <th>IPD</th>
                                <td colSpan={2}>{val(registration.refraction, 'ipd')}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="section-title">{t('global.assessment_and_plan')}</div>
                    <table className="data-table">
                        <tbody>
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
