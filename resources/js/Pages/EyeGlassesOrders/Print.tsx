import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import '../../../css/ophthalmology-report-print.css';
import { useTranslation } from '../../hooks/useTranslation';
import { eyeGlassesStatusLabel } from '../../Components/EyeGlasses/status';

interface PrescriptionEye {
    sphere?: string | null;
    cylinder?: string | null;
    axis?: string | null;
    add?: string | null;
    prism_horizontal?: string | null;
    prism_vertical?: string | null;
}

interface PrintProps {
    order: {
        ref_no: string;
        request_date: string;
        status: string;
        examiner_name: string | null;
        branch_name: string | null;
        frame_type: string | null;
        lens_type: string | null;
        lens_material: string | null;
        tint: string;
        quantity: number;
        prescription: { od?: PrescriptionEye; os?: PrescriptionEye; ipd?: string | null };
        notes: string;
        amount: string | number | null;
        paid_amount: string | number | null;
        paid_at: string | null;
        payment_method: string | null;
        delivered_at: string | null;
        received_by: string;
        patient: {
            name: string;
            father_name: string | null;
            id_card: string | number | null;
            age: string | number | null;
            gender: string | number | null;
            phone: string | null;
        };
    };
    assets: { leftLogo: string; rightLogo: string };
    generatedAt: string;
}

function cell(value: string | number | null | undefined): string {
    if (value === null || value === undefined || value === '') return '—';
    return String(value);
}

export default function EyeGlassesOrderPrint({ order, assets, generatedAt }: PrintProps) {
    const { t } = useTranslation();

    useEffect(() => {
        window.print();
    }, []);

    const genderLabel =
        String(order.patient.gender) === '1'
            ? t('global.female')
            : String(order.patient.gender) === '0'
              ? t('global.male')
              : (order.patient.gender ?? '—');

    const optionLabel = (prefix: string, value: string | null | undefined) => {
        if (!value) return '—';
        const key = `global.eye_glasses_${prefix}_${value}`;
        const translated = t(key);
        return translated === key ? value : translated;
    };

    const rx = (side: 'od' | 'os', key: keyof PrescriptionEye) => cell(order.prescription?.[side]?.[key]);

    return (
        <div className="ophthalmology-report-print">
            <Head title={`${t('global.eye_glasses_order')} ${order.ref_no}`} />
            <div className="report-container">
                <div className="header">
                    <div className="header-grid">
                        <img src={assets.leftLogo} alt="" className="logo-image" />
                        <div className="text-column">
                            <h2>{t('global.system_name')}</h2>
                            <div>{t('global.ophthalmology_department')}</div>
                            <div className="report-title">{t('global.eye_glasses_order')}</div>
                        </div>
                        <img src={assets.rightLogo} alt="" className="logo-image" />
                    </div>
                </div>

                <table className="meta-table">
                    <tbody>
                        <tr>
                            <th>{t('global.ref_no')}</th>
                            <td>{order.ref_no}</td>
                            <th>{t('global.status')}</th>
                            <td>{eyeGlassesStatusLabel(order.status, t)}</td>
                        </tr>
                        <tr>
                            <th>{t('global.patient_name')}</th>
                            <td>{cell(order.patient.name)}</td>
                            <th>{t('global.father_name')}</th>
                            <td>{cell(order.patient.father_name)}</td>
                        </tr>
                        <tr>
                            <th>{t('global.id_card')}</th>
                            <td>{cell(order.patient.id_card)}</td>
                            <th>{t('global.age')}</th>
                            <td>{cell(order.patient.age)}</td>
                        </tr>
                        <tr>
                            <th>{t('global.gender')}</th>
                            <td>{genderLabel}</td>
                            <th>{t('global.phone')}</th>
                            <td>{cell(order.patient.phone)}</td>
                        </tr>
                        <tr>
                            <th>{t('global.examiner')}</th>
                            <td>{cell(order.examiner_name)}</td>
                            <th>{t('global.eye_glasses_request_date')}</th>
                            <td>{cell(order.request_date)}</td>
                        </tr>
                        <tr>
                            <th>{t('global.branch')}</th>
                            <td>{cell(order.branch_name)}</td>
                            <th>{t('global.eye_glasses_quantity')}</th>
                            <td>{cell(order.quantity)}</td>
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
                                <td>{rx(eye, 'sphere')}</td>
                                <td>{rx(eye, 'cylinder')}</td>
                                <td>{rx(eye, 'axis')}</td>
                                <td>{rx(eye, 'add')}</td>
                                <td>{rx(eye, 'prism_horizontal')}</td>
                                <td>{rx(eye, 'prism_vertical')}</td>
                            </tr>
                        ))}
                        <tr>
                            <th>IPD</th>
                            <td colSpan={6}>{cell(order.prescription?.ipd)}</td>
                        </tr>
                    </tbody>
                </table>

                <table className="data-table">
                    <tbody>
                        <tr>
                            <th>{t('global.eye_glasses_frame_type')}</th>
                            <td>{optionLabel('frame', order.frame_type)}</td>
                            <th>{t('global.eye_glasses_lens_type')}</th>
                            <td>{optionLabel('lens', order.lens_type)}</td>
                        </tr>
                        <tr>
                            <th>{t('global.eye_glasses_lens_material')}</th>
                            <td>{optionLabel('material', order.lens_material)}</td>
                            <th>{t('global.eye_glasses_tint')}</th>
                            <td>{cell(order.tint)}</td>
                        </tr>
                        <tr>
                            <th>{t('global.amount')}</th>
                            <td>{cell(order.amount)}</td>
                            <th>{t('global.eye_glasses_payment_method')}</th>
                            <td>{optionLabel('pay', order.payment_method)}</td>
                        </tr>
                        <tr>
                            <th>{t('global.eye_glasses_paid_amount')}</th>
                            <td>{cell(order.paid_amount)}</td>
                            <th>{t('global.eye_glasses_delivery')}</th>
                            <td>{cell(order.delivered_at)}</td>
                        </tr>
                        <tr>
                            <th>{t('global.eye_glasses_received_by')}</th>
                            <td>{cell(order.received_by)}</td>
                            <th>{t('global.notes')}</th>
                            <td>{cell(order.notes)}</td>
                        </tr>
                    </tbody>
                </table>

                <div className="footer">{generatedAt}</div>
            </div>
        </div>
    );
}
