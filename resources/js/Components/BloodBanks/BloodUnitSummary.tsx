import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { BloodUnitDetail } from '../../types/bloodBank';
import {
    BLOOD_UNIT_CARD_CLASS,
    BLOOD_UNIT_HERO_AVATAR_CLASS,
    bloodGroupLabel,
    bloodRhLabel,
    bloodUnitStatusBadgeColor,
    screeningStatusBadgeColor,
} from './bloodBankUi';

interface BloodUnitSummaryProps {
    unit: BloodUnitDetail;
}

function HeroStat({ label, value, dir }: { label: string; value: string; dir?: 'ltr' | 'rtl' }) {
    return (
        <div className="rounded-xl bg-white/15 px-3 py-2 backdrop-blur-sm">
            <p className="text-[10px] font-semibold uppercase tracking-wider text-rose-100/90">{label}</p>
            <p className="truncate text-sm font-semibold" dir={dir}>
                {value}
            </p>
        </div>
    );
}

export default function BloodUnitSummary({ unit }: BloodUnitSummaryProps) {
    const { t } = useTranslation();
    const bloodType = `${bloodGroupLabel(unit.blood_group)} ${bloodRhLabel(unit.rh)}`.trim();
    const screeningLabel =
        unit.screening_status === 'passed'
            ? t('global.passed')
            : unit.screening_status === 'failed'
              ? t('global.failed')
              : t('global.pending');

    return (
        <div className={BLOOD_UNIT_CARD_CLASS}>
            <div className="bg-gradient-to-br from-rose-600 via-rose-600 to-red-700 px-5 py-5 text-white">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-4">
                        <div className={BLOOD_UNIT_HERO_AVATAR_CLASS}>
                            <i className="bx bx-droplet text-3xl" />
                        </div>
                        <div className="min-w-0">
                            <h2 className="text-lg font-bold tracking-tight">
                                {unit.bag_number ?? `#${unit.id}`}
                            </h2>
                            <p className="mt-0.5 text-sm text-rose-100/90">{bloodType}</p>
                            <p className="mt-1 text-xs text-rose-100/75">
                                #{unit.id}
                                {unit.component_type ? ` · ${unit.component_type}` : ''}
                                {unit.volume_ml != null ? ` · ${unit.volume_ml} ml` : ''}
                            </p>
                            <div className="mt-2 flex flex-wrap gap-2">
                                <Badge color={bloodUnitStatusBadgeColor(unit.status)} className="font-normal capitalize">
                                    {unit.status}
                                </Badge>
                                <Badge color={screeningStatusBadgeColor(unit.screening_status)} className="font-normal">
                                    {t('global.screening_status')}: {screeningLabel}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <HeroStat
                            label={t('global.collected_at')}
                            value={unit.collected_at ?? '—'}
                            dir="ltr"
                        />
                        <HeroStat
                            label={t('global.expires_at')}
                            value={unit.expires_at ?? '—'}
                            dir="ltr"
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}
