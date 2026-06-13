import { Head, Link, router, useForm } from '@inertiajs/react';
import { Alert, Badge, Button, Label, Spinner, Textarea, TextInput } from 'flowbite-react';
import { FormEvent, ReactNode, useEffect, useMemo, useState } from 'react';
import BloodBankNavTabs from '../../Components/BloodBanks/BloodBankNavTabs';
import BloodFormSegmented from '../../Components/BloodBanks/BloodFormSegmented';
import BloodTestResultSegmented from '../../Components/BloodBanks/BloodTestResultSegmented';
import BloodUnitDetailTile from '../../Components/BloodBanks/BloodUnitDetailTile';
import BloodUnitSummary from '../../Components/BloodBanks/BloodUnitSummary';
import BloodUnitTestResultBadge from '../../Components/BloodBanks/BloodUnitTestResultBadge';
import {
    BLOOD_BANK_PANEL_ICON_CLASS,
    BLOOD_BANK_PRIMARY_BTN_CLASS,
    bloodGroupLabel,
    bloodRhLabel,
    bloodUnitStatusBadgeColor,
    movementTypeBadgeColor,
    screeningStatusBadgeColor,
} from '../../Components/BloodBanks/bloodBankUi';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import IcuPanel from '../../Components/Icus/IcuPanel';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '../../Components/ui/Table';
import { useTranslation } from '../../hooks/useTranslation';
import {
    BloodBankListUrls,
    BloodUnitDetail,
    BloodUnitShowPermissions,
    BloodUnitTestForm,
    BloodUnitTestRecord,
} from '../../types/bloodBank';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';

interface InventoryShowProps {
    unit: BloodUnitDetail;
    filterOptions: {
        bloodGroups: string[];
        testResults: string[];
    };
    permissions: BloodUnitShowPermissions;
    flash?: {
        success?: string | null;
        error?: string | null;
    };
    urls: BloodBankListUrls & {
        back: string;
        saveTests: string;
        approveAfterTests: string;
        quarantine: string;
        releaseQuarantine: string;
        discard: string;
    };
}

function buildTestForm(
    test: BloodUnitTestRecord | null,
    unit: Pick<BloodUnitDetail, 'blood_group' | 'rh'>,
): BloodUnitTestForm {
    return {
        abo_result: test?.abo_result ?? unit.blood_group ?? '',
        rh_result: test?.rh_result ?? unit.rh ?? '',
        dct_result: test?.dct_result ?? 'pending',
        ict_result: test?.ict_result ?? 'pending',
        hbs_result: test?.hbs_result ?? 'pending',
        hiv_result: test?.hiv_result ?? 'pending',
        hcv_result: test?.hcv_result ?? 'pending',
        vdrl_result: test?.vdrl_result ?? 'pending',
        remarks: test?.remarks ?? '',
    };
}

function ScreeningBadge({ status, t }: { status: string; t: (key: string) => string }) {
    const label =
        status === 'passed'
            ? t('global.passed')
            : status === 'failed'
              ? t('global.failed')
              : t('global.pending');

    return (
        <Badge color={screeningStatusBadgeColor(status)} className="w-fit font-normal">
            {label}
        </Badge>
    );
}

const ACTION_BTN_BASE =
    'inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-60';

const ACTION_BTN_VARIANTS = {
    warning: `${ACTION_BTN_BASE} bg-gradient-to-b from-amber-500 to-amber-600 text-white hover:from-amber-600 hover:to-amber-700 focus:ring-amber-400/40`,
    success: `${ACTION_BTN_BASE} bg-gradient-to-b from-emerald-500 to-emerald-600 text-white hover:from-emerald-600 hover:to-emerald-700 focus:ring-emerald-400/40`,
    danger: `${ACTION_BTN_BASE} border border-red-200 bg-white text-red-700 hover:bg-red-50 focus:ring-red-400/30 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-300 dark:hover:bg-red-950/40`,
} as const;

function ActionSubmitButton({
    variant,
    disabled,
    onClick,
    icon,
    children,
}: {
    variant: keyof typeof ACTION_BTN_VARIANTS;
    disabled?: boolean;
    onClick?: () => void;
    icon: string;
    children: ReactNode;
}) {
    return (
        <button type="button" className={ACTION_BTN_VARIANTS[variant]} disabled={disabled} onClick={onClick}>
            {disabled ? <Spinner size="sm" /> : <i className={`bx ${icon}`} />}
            {children}
        </button>
    );
}

function TestFormField({
    label,
    icon,
    error,
    children,
}: {
    label: string;
    icon?: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <div>
            <Label className="mb-2 flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                {icon && <i className={`bx ${icon} text-base text-rose-500`} />}
                {label}
            </Label>
            {children}
            {error && <p className="mt-1.5 text-xs text-red-600">{error}</p>}
        </div>
    );
}

const PANEL_BODY_CLASS = 'p-5';

const SCREENING_TEST_FIELDS = [
    { field: 'dct_result', labelKey: 'global.dct', icon: 'bx-test-tube' },
    { field: 'ict_result', labelKey: 'global.ict', icon: 'bx-test-tube' },
    { field: 'hbs_result', labelKey: 'global.hbs', icon: 'bx-virus' },
    { field: 'hcv_result', labelKey: 'global.hcv', icon: 'bx-virus' },
    { field: 'hiv_result', labelKey: 'global.hiv', icon: 'bx-virus' },
    { field: 'vdrl_result', labelKey: 'global.vdrl', icon: 'bx-virus' },
] as const;

export default function BloodBanksInventoryShow({
    unit,
    filterOptions,
    permissions,
    flash,
    urls,
}: InventoryShowProps) {
    const { t } = useTranslation();
    const [processing, setProcessing] = useState(false);
    const [quarantineReason, setQuarantineReason] = useState('');
    const [releaseReason, setReleaseReason] = useState('');
    const [discardReason, setDiscardReason] = useState('');

    const { data, setData, post, processing: savingTests, errors } = useForm<BloodUnitTestForm>(
        buildTestForm(unit.test, unit),
    );

    useEffect(() => {
        setData(buildTestForm(unit.test, unit));
    }, [unit.id, unit.test, unit.blood_group, unit.rh]);

    const displayUnit = useMemo<BloodUnitDetail>(
        () => ({
            ...unit,
            blood_group: data.abo_result || unit.blood_group,
            rh: data.rh_result || unit.rh,
        }),
        [unit, data.abo_result, data.rh_result],
    );

    const postAction = (url: string, payload: Record<string, string> = {}) => {
        setProcessing(true);
        router.post(url, payload, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const saveTests = (event: FormEvent) => {
        event.preventDefault();
        post(urls.saveTests, { preserveScroll: true });
    };

    const testResultOptions = filterOptions.testResults;

    const hasSidebar = permissions.manage;
    const showActionsPanel = permissions.manage && permissions.canQuarantine;

    return (
        <DashboardLayout>
            <Head title={`${t('global.blood_unit_details')} — ${unit.bag_number ?? unit.id}`} />

            <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <SettingsPageHeader
                    title={t('global.blood_unit_details')}
                    subtitle={unit.bag_number ?? String(unit.id)}
                    icon="bx-donate-blood"
                    accent="from-rose-600 to-red-700"
                    backHref={urls.back}
                    backLabel={t('global.back')}
                />

                <BloodBankNavTabs active="inventory" urls={urls} />

                {flash?.success && (
                    <Alert color="success" className="rounded-xl">
                        <span className="text-sm font-medium">{flash.success}</span>
                    </Alert>
                )}
                {flash?.error && (
                    <Alert color="failure" className="rounded-xl">
                        <span className="text-sm font-medium">{flash.error}</span>
                    </Alert>
                )}

                {unit.is_expired && unit.status !== 'expired' && (
                    <Alert color="failure" className="rounded-xl">
                        <div className="flex items-start gap-2">
                            <i className="bx bx-error-circle mt-0.5 text-lg" />
                            <div>
                                <p className="font-semibold">{t('global.blood_unit_expiry_alarm')}</p>
                                <p className="mt-0.5 text-sm opacity-90">
                                    {t('global.expires_at')}: <span dir="ltr">{unit.expires_at}</span>
                                </p>
                            </div>
                        </div>
                    </Alert>
                )}

                {unit.is_expiring_soon && !unit.is_expired && (
                    <Alert color="warning" className="rounded-xl">
                        <div className="flex items-start gap-2">
                            <i className="bx bx-time-five mt-0.5 text-lg" />
                            <div>
                                <p className="font-semibold">{t('global.blood_unit_expiry_alarm')}</p>
                                <p className="mt-0.5 text-sm opacity-90">
                                    {t('global.expires_at')}: <span dir="ltr">{unit.expires_at}</span>
                                    {unit.days_until_expiry != null && (
                                        <span> · {unit.days_until_expiry} {t('global.days')}</span>
                                    )}
                                </p>
                            </div>
                        </div>
                    </Alert>
                )}

                <BloodUnitSummary unit={displayUnit} />

                <div className={`grid gap-5 ${hasSidebar ? 'xl:grid-cols-3' : ''}`}>
                    <div className={`space-y-5 ${hasSidebar ? 'xl:col-span-2' : ''}`}>
                        <IcuPanel
                            variant="table"
                            contentClassName={PANEL_BODY_CLASS}
                            title={t('global.unit_information')}
                            icon="bx-box"
                            iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                            action={
                                <Badge color={bloodUnitStatusBadgeColor(unit.status)} className="font-normal capitalize">
                                    {unit.status}
                                </Badge>
                            }
                        >
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <BloodUnitDetailTile icon="bx-droplet" label={t('global.blood_group')} value={bloodGroupLabel(displayUnit.blood_group)} />
                                <BloodUnitDetailTile icon="bx-plus-medical" label={t('global.rh')} value={bloodRhLabel(displayUnit.rh)} />
                                <BloodUnitDetailTile icon="bx-category" label={t('global.component_type')} value={unit.component_type ?? '—'} />
                                <BloodUnitDetailTile icon="bx-check-shield" label={t('global.status')}>
                                    <Badge color={bloodUnitStatusBadgeColor(unit.status)} className="w-fit font-normal capitalize">
                                        {unit.status}
                                    </Badge>
                                </BloodUnitDetailTile>
                                <BloodUnitDetailTile icon="bx-test-tube" label={t('global.screening_status')}>
                                    <ScreeningBadge status={unit.screening_status} t={t} />
                                </BloodUnitDetailTile>
                                <BloodUnitDetailTile icon="bx-flask" label={t('global.volume_ml')} value={unit.volume_ml ?? '—'} />
                                <BloodUnitDetailTile icon="bx-calendar" label={t('global.collected_at')}>
                                    <span dir="ltr">{unit.collected_at ?? '—'}</span>
                                </BloodUnitDetailTile>
                                <BloodUnitDetailTile icon="bx-time-five" label={t('global.expires_at')}>
                                    <span
                                        dir="ltr"
                                        className={
                                            unit.is_expired
                                                ? 'text-red-600 dark:text-red-400'
                                                : unit.is_expiring_soon
                                                  ? 'text-amber-600 dark:text-amber-400'
                                                  : undefined
                                        }
                                    >
                                        {unit.expires_at ?? '—'}
                                    </span>
                                </BloodUnitDetailTile>
                                {unit.branch_name && (
                                    <BloodUnitDetailTile icon="bx-buildings" label={t('global.branch')} value={unit.branch_name} />
                                )}
                            </div>
                        </IcuPanel>

                        <IcuPanel
                            variant="table"
                            contentClassName={PANEL_BODY_CLASS}
                            title={t('global.blood_unit_screening_results')}
                            icon="bx-test-tube"
                            iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                            action={<ScreeningBadge status={unit.screening_status} t={t} />}
                        >
                            {unit.tests.length === 0 ? (
                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                    {t('global.blood_unit_no_screening_record')}
                                </p>
                            ) : (
                                <div className="space-y-6">
                                    {unit.tests.map((test, index) => {
                                        const isCurrentTest = test.id === unit.test?.id;
                                        const aboDisplay =
                                            permissions.manage && isCurrentTest
                                                ? data.abo_result || test.abo_result
                                                : test.abo_result;
                                        const rhDisplay =
                                            permissions.manage && isCurrentTest
                                                ? data.rh_result || test.rh_result
                                                : test.rh_result;

                                        return (
                                            <div
                                                key={test.id}
                                                className={index > 0 ? 'border-t border-gray-200 pt-6 dark:border-gray-700' : ''}
                                            >
                                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                                    <BloodUnitDetailTile label={t('global.abo_result')} value={aboDisplay ?? '—'} />
                                                    <BloodUnitDetailTile label={t('global.rh_result')} value={rhDisplay ?? '—'} />
                                                    <BloodUnitDetailTile label={t('global.dct')}>
                                                        <BloodUnitTestResultBadge result={test.dct_result} />
                                                    </BloodUnitDetailTile>
                                                    <BloodUnitDetailTile label={t('global.ict')}>
                                                        <BloodUnitTestResultBadge result={test.ict_result} />
                                                    </BloodUnitDetailTile>
                                                    <BloodUnitDetailTile label={t('global.hbs')}>
                                                        <BloodUnitTestResultBadge result={test.hbs_result} />
                                                    </BloodUnitDetailTile>
                                                    <BloodUnitDetailTile label={t('global.hcv')}>
                                                        <BloodUnitTestResultBadge result={test.hcv_result} />
                                                    </BloodUnitDetailTile>
                                                    <BloodUnitDetailTile label={t('global.hiv')}>
                                                        <BloodUnitTestResultBadge result={test.hiv_result} />
                                                    </BloodUnitDetailTile>
                                                    <BloodUnitDetailTile label={t('global.vdrl')}>
                                                        <BloodUnitTestResultBadge result={test.vdrl_result} />
                                                    </BloodUnitDetailTile>
                                                    <BloodUnitDetailTile label={t('global.status')}>
                                                        <ScreeningBadge status={test.overall_status} t={t} />
                                                    </BloodUnitDetailTile>
                                                    {test.remarks && (
                                                        <div className="sm:col-span-2 lg:col-span-3">
                                                            <BloodUnitDetailTile label={t('global.remarks')} value={test.remarks} />
                                                        </div>
                                                    )}
                                                    {test.tested_at && (
                                                        <div className="col-span-full">
                                                            <BloodUnitDetailTile label={t('global.last_tested_at')}>
                                                                <span dir="ltr">
                                                                    {test.tested_at}
                                                                    {test.tested_by_name ? ` — ${test.tested_by_name}` : ''}
                                                                </span>
                                                            </BloodUnitDetailTile>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </IcuPanel>

                        <IcuPanel
                            variant="table"
                            contentClassName={PANEL_BODY_CLASS}
                            title={t('global.donor_and_sample')}
                            icon="bx-user"
                            iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                        >
                            {!unit.donation ? (
                                <p className="text-sm text-gray-500 dark:text-gray-400">{t('global.no_donation_linked')}</p>
                            ) : (
                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <BloodUnitDetailTile label={t('global.donor')} value={unit.donation.donor_name ?? '—'} />
                                    <BloodUnitDetailTile label={t('global.patient')}>
                                        {unit.donation.patient ? (
                                            <Link
                                                href={unit.donation.patient.urls.show}
                                                className="text-rose-600 hover:underline dark:text-rose-400"
                                            >
                                                {unit.donation.patient.name}
                                            </Link>
                                        ) : (
                                            '—'
                                        )}
                                    </BloodUnitDetailTile>
                                    <BloodUnitDetailTile label={t('global.department')} value={unit.donation.department_name ?? '—'} />
                                    <BloodUnitDetailTile label={t('global.phlebotomy_at')}>
                                        <span dir="ltr">{unit.donation.phlebotomy_at ?? '—'}</span>
                                    </BloodUnitDetailTile>
                                    <BloodUnitDetailTile label={t('global.samples')} value={unit.donation.samples_count} />
                                </div>
                            )}
                        </IcuPanel>
                    </div>

                    {hasSidebar && (
                        <div className="space-y-5 xl:sticky xl:top-4 xl:self-start">
                            {showActionsPanel && (
                                <IcuPanel
                                    variant="table"
                                    contentClassName={PANEL_BODY_CLASS}
                                    title={t('global.actions')}
                                    icon="bx-cog"
                                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                                >
                                    <div className="space-y-5">
                                        {unit.status === 'available' && (
                                            <div className="space-y-4">
                                                <p className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                                    <i className="bx bx-lock-alt text-lg text-amber-500" />
                                                    {t('global.blood_quarantine_action')}
                                                </p>
                                                <TestFormField label={t('global.reason')} icon="bx-edit">
                                                    <TextInput
                                                        value={quarantineReason}
                                                        onChange={(e) => setQuarantineReason(e.target.value)}
                                                        className="rounded-xl"
                                                        placeholder={t('global.reason')}
                                                    />
                                                </TestFormField>
                                                <ActionSubmitButton
                                                    variant="warning"
                                                    icon="bx-lock-alt"
                                                    disabled={processing}
                                                    onClick={() => postAction(urls.quarantine, { reason: quarantineReason })}
                                                >
                                                    {t('global.blood_quarantine_action')}
                                                </ActionSubmitButton>
                                            </div>
                                        )}

                                        {unit.status === 'quarantine' && (
                                            <div className="space-y-4">
                                                <p className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                                    <i className="bx bx-lock-open text-lg text-emerald-500" />
                                                    {t('global.blood_release_quarantine')}
                                                </p>
                                                <TestFormField label={t('global.reason')} icon="bx-edit">
                                                    <TextInput
                                                        value={releaseReason}
                                                        onChange={(e) => setReleaseReason(e.target.value)}
                                                        className="rounded-xl"
                                                        placeholder={t('global.reason')}
                                                    />
                                                </TestFormField>
                                                <ActionSubmitButton
                                                    variant="success"
                                                    icon="bx-lock-open"
                                                    disabled={processing}
                                                    onClick={() => postAction(urls.releaseQuarantine, { reason: releaseReason })}
                                                >
                                                    {t('global.blood_release_quarantine')}
                                                </ActionSubmitButton>
                                            </div>
                                        )}

                                        {permissions.canDiscard && (
                                            <div
                                                className={`space-y-4 ${
                                                    unit.status === 'available' || unit.status === 'quarantine'
                                                        ? 'border-t border-gray-100 pt-5 dark:border-gray-800'
                                                        : ''
                                                }`}
                                            >
                                                <p className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                                    <i className="bx bx-trash text-lg text-red-500" />
                                                    {t('global.discard_unit')}
                                                </p>
                                                <TestFormField label={t('global.discard_reason')} icon="bx-note">
                                                    <TextInput
                                                        value={discardReason}
                                                        onChange={(e) => setDiscardReason(e.target.value)}
                                                        className="rounded-xl"
                                                        placeholder={t('global.discard_reason')}
                                                    />
                                                </TestFormField>
                                                <ActionSubmitButton
                                                    variant="danger"
                                                    icon="bx-trash"
                                                    disabled={processing}
                                                    onClick={() => {
                                                        if (!window.confirm(t('global.are_you_sure'))) return;
                                                        postAction(urls.discard, { reason: discardReason });
                                                    }}
                                                >
                                                    {t('global.discard_unit')}
                                                </ActionSubmitButton>
                                            </div>
                                        )}
                                    </div>
                                </IcuPanel>
                            )}

                            <IcuPanel
                                variant="table"
                                contentClassName={PANEL_BODY_CLASS}
                                title={t('global.screening_and_tests')}
                                icon="bx-flask"
                                iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                            >
                                <form onSubmit={saveTests} className="space-y-5">
                                    <div className="space-y-4">
                                        <TestFormField label={t('global.abo_result')} icon="bx-droplet">
                                            <BloodFormSegmented
                                                value={data.abo_result}
                                                onChange={(value) => setData('abo_result', value)}
                                                columns={4}
                                                allowEmpty
                                                options={filterOptions.bloodGroups.map((group) => ({
                                                    value: group,
                                                    label: group,
                                                    icon: 'bx-droplet',
                                                }))}
                                            />
                                        </TestFormField>
                                        <TestFormField label={t('global.rh_result')} icon="bx-plus-medical">
                                            <BloodFormSegmented
                                                value={data.rh_result}
                                                onChange={(value) => setData('rh_result', value)}
                                                allowEmpty
                                                options={[
                                                    { value: '+', label: 'Rh+', icon: 'bx-plus-medical' },
                                                    { value: '-', label: 'Rh−', icon: 'bx-minus' },
                                                ]}
                                            />
                                        </TestFormField>
                                    </div>

                                    <div className="border-t border-gray-100 pt-5 dark:border-gray-800">
                                        <p className="mb-4 text-sm font-semibold text-gray-900 dark:text-white">
                                            {t('global.blood_unit_screening_results')}
                                        </p>
                                        <div className="grid gap-4 sm:grid-cols-2">
                                            {SCREENING_TEST_FIELDS.map(({ field, labelKey, icon }) => (
                                                <TestFormField
                                                    key={field}
                                                    label={t(labelKey)}
                                                    icon={icon}
                                                    error={errors[field]}
                                                >
                                                    <BloodTestResultSegmented
                                                        value={data[field]}
                                                        onChange={(value) => setData(field, value)}
                                                        options={testResultOptions}
                                                    />
                                                </TestFormField>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="border-t border-gray-100 pt-5 dark:border-gray-800">
                                        <TestFormField label={t('global.remarks')} icon="bx-note">
                                            <Textarea
                                                rows={2}
                                                value={data.remarks}
                                                onChange={(e) => setData('remarks', e.target.value)}
                                                className="rounded-xl"
                                                placeholder={t('global.remarks')}
                                            />
                                        </TestFormField>
                                    </div>

                                    <button type="submit" className={`${BLOOD_BANK_PRIMARY_BTN_CLASS} w-full`} disabled={savingTests}>
                                        {savingTests ? <Spinner size="sm" /> : <i className="bx bx-save" />}
                                        {t('global.save_tests')}
                                    </button>
                                </form>

                                {permissions.canReleaseAfterTests && (
                                    <Button
                                        color="success"
                                        className="mt-3 w-full rounded-xl"
                                        disabled={processing}
                                        onClick={() => postAction(urls.approveAfterTests)}
                                    >
                                        <i className="bx bx-check-circle me-1.5" />
                                        {t('global.release_to_stock')}
                                    </Button>
                                )}

                                {unit.test?.tested_at && (
                                    <p className="mt-3 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                                        {t('global.last_tested_at')}:{' '}
                                        <span dir="ltr" className="font-medium text-gray-700 dark:text-gray-300">
                                            {unit.test.tested_at}
                                        </span>
                                        {unit.test.tested_by_name ? ` — ${unit.test.tested_by_name}` : ''}
                                    </p>
                                )}
                            </IcuPanel>
                        </div>
                    )}
                </div>

                <IcuPanel
                    variant="table"
                    contentClassName={PANEL_BODY_CLASS}
                    title={t('global.stock_movement_history')}
                    icon="bx-list-ul"
                    iconClassName={BLOOD_BANK_PANEL_ICON_CLASS}
                >
                    <Table embedded>
                        <TableHead>
                            <TableRow variant="header">
                                <TableHeader>{t('global.date')}</TableHeader>
                                <TableHeader>{t('global.movement_type')}</TableHeader>
                                <TableHeader>{t('global.user')}</TableHeader>
                                <TableHeader>{t('global.notes')}</TableHeader>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {unit.stock_movements.length === 0 ? (
                                <TableEmpty colSpan={4} title={t('global.no_item_is_found')} />
                            ) : (
                                unit.stock_movements.map((movement) => (
                                    <TableRow key={movement.id}>
                                        <TableCell muted dir="ltr">
                                            {movement.created_at ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                color={movementTypeBadgeColor(movement.movement_type)}
                                                className="w-fit font-normal capitalize"
                                            >
                                                {movement.movement_type}
                                            </Badge>
                                        </TableCell>
                                        <TableCell muted>{movement.user_name ?? '—'}</TableCell>
                                        <TableCell muted className="max-w-md whitespace-normal">
                                            {movement.notes ?? '—'}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </IcuPanel>
            </div>
        </DashboardLayout>
    );
}
