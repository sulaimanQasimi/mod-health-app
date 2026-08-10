export type EyeSide = 'od' | 'os';

export const OCULAR_HISTORY_FIELDS = [
    ['previous_surgery', 'oph_ocular_previous_surgery'],
    ['trauma', 'oph_ocular_trauma'],
    ['contact_lenses', 'oph_ocular_contact_lenses'],
    ['family_glaucoma', 'oph_ocular_family_glaucoma'],
    ['family_blindness', 'oph_ocular_family_blindness'],
    ['current_eye_drops', 'oph_ocular_current_eye_drops'],
] as const;

export const VISUAL_FIELDS = [
    ['visual_acuity', 'oph_va'],
    ['best_corrected_acuity', 'oph_bcva'],
    ['pinhole_vision', 'oph_pinhole'],
    ['vision_with_glasses', 'oph_vision_glasses'],
    ['near_vision', 'oph_near_vision'],
    ['intraocular_pressure', 'oph_iop'],
] as const;

export const REFRACTION_FIELDS = [
    ['sphere', 'oph_sphere'],
    ['cylinder', 'oph_cylinder'],
    ['axis', 'oph_axis'],
    ['add', 'oph_add'],
    ['prism_horizontal', 'oph_prism_h'],
    ['prism_vertical', 'oph_prism_v'],
    ['distance_vision', 'oph_distance_vision'],
    ['near_vision', 'oph_near_vision'],
    ['present_glasses', 'oph_present_glasses'],
    ['recommended_prescription', 'oph_recommended_rx'],
] as const;

export const SLIT_LAMP_FIELDS = [
    ['lids', 'oph_lids'],
    ['conjunctiva', 'oph_conjunctiva'],
    ['cornea', 'oph_cornea'],
    ['sclera', 'oph_sclera'],
    ['anterior_chamber', 'oph_anterior_chamber'],
    ['iris', 'oph_iris'],
    ['pupil', 'oph_pupil'],
    ['lens', 'oph_lens'],
    ['gonioscopy', 'oph_gonioscopy'],
    ['extraocular_movement', 'oph_eom'],
] as const;

export const FUNDUS_STRUCTURED_FIELDS = [
    ['disc', 'oph_fundus_disc'],
    ['cdr', 'oph_fundus_cdr'],
    ['macula', 'oph_fundus_macula'],
    ['vessels', 'oph_fundus_vessels'],
    ['periphery', 'oph_fundus_periphery'],
    ['vitreous', 'oph_fundus_vitreous'],
] as const;

export const DIAGNOSTIC_TEST_FIELDS = [
    ['autorefraction', 'oph_test_autorefraction'],
    ['retinoscopy', 'oph_test_retinoscopy'],
    ['keratometry', 'oph_test_keratometry'],
    ['oct', 'oph_test_oct'],
    ['b_scan', 'oph_test_bscan'],
    ['a_scan', 'oph_test_ascan'],
    ['visual_fields', 'oph_test_vf'],
    ['schirmer', 'oph_test_schirmer'],
    ['tbut', 'oph_test_tbut'],
    ['fluorescein', 'oph_test_fluorescein'],
    ['biometry', 'oph_test_biometry'],
] as const;

export const VA_PRESETS = [
    '6/6', '6/9', '6/12', '6/18', '6/24', '6/36', '6/60',
    'CF', 'HM', 'PL+', 'PL-', 'NPL',
    '1.0', '0.8', '0.6', '0.5', '0.4', '0.3', '0.2', '0.1',
];

export const IOP_METHODS = [
    ['goldmann', 'oph_iop_goldmann'],
    ['nct', 'oph_iop_nct'],
    ['tonopen', 'oph_iop_tonopen'],
    ['other', 'oph_iop_other'],
] as const;

export const SLIT_PHRASES = ['طبیعی', 'واضح', 'کدر', 'ملتهب', 'خشک', 'اسکار'];

export const ATTACHMENT_LABELS = [
    ['anterior', 'oph_attach_anterior'],
    ['fundus_od', 'oph_attach_fundus_od'],
    ['fundus_os', 'oph_attach_fundus_os'],
    ['oct', 'oph_attach_oct'],
    ['vf', 'oph_attach_vf'],
    ['other', 'oph_attach_other'],
] as const;

export const inputClass =
    'block w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white';
export const tableShellClass = 'overflow-hidden rounded-2xl border border-gray-200/80 dark:border-gray-700';
export const tableHeaderClass =
    'bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-gray-800/80 dark:text-gray-300';
export const tableRowClass =
    'border-t border-gray-100 text-gray-700 transition hover:bg-cyan-50/40 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-cyan-950/20';
export const tableCellClass = 'p-3 text-start font-medium text-gray-700 dark:text-gray-200';

export function nestedGet(data: Record<string, any> | undefined, ...path: string[]): any {
    let value: any = data;
    for (const part of path) {
        value = value?.[part];
    }
    return value ?? '';
}

export function nestedSet(root: Record<string, any>, path: string[], value: any): Record<string, any> {
    const next = { ...root };
    let cursor: Record<string, any> = next;
    path.slice(0, -1).forEach((part) => {
        cursor[part] = { ...(cursor[part] ?? {}) };
        cursor = cursor[part];
    });
    cursor[path[path.length - 1]] = value;
    return next;
}

export function copyEyeData(section: Record<string, any>, from: EyeSide, to: EyeSide): Record<string, any> {
    return {
        ...section,
        [to]: JSON.parse(JSON.stringify(section?.[from] ?? {})),
    };
}

export function swapEyeData(section: Record<string, any>): Record<string, any> {
    return {
        ...section,
        od: JSON.parse(JSON.stringify(section?.os ?? {})),
        os: JSON.parse(JSON.stringify(section?.od ?? {})),
    };
}

export function markSlitLampAllNormal(section: Record<string, any>, eye: EyeSide): Record<string, any> {
    const eyeData = { ...(section?.[eye] ?? {}) };
    SLIT_LAMP_FIELDS.forEach(([key]) => {
        eyeData[key] = { ...(eyeData[key] ?? {}), status: 'normal', notes: eyeData[key]?.notes ?? '' };
    });
    return { ...section, [eye]: eyeData };
}

export function iopAlert(value: string | number | undefined): boolean {
    const n = Number(value);
    return Number.isFinite(n) && n > 21;
}

export function axisValid(value: string | number | undefined): boolean {
    if (value === '' || value === null || value === undefined) return true;
    const n = Number(value);
    return Number.isFinite(n) && n >= 0 && n <= 180;
}
