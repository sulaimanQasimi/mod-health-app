import { Badge } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { BloodRequestDetail } from '../../types/bloodBank';
import {
    BLOOD_UNIT_CARD_CLASS,
    BLOOD_UNIT_HERO_AVATAR_CLASS,
    bloodGroupLabel,
    bloodRhLabel,
    bloodStatusBadgeColor,
} from './bloodBankUi';

interface BloodRequestSummaryProps {
    bloodRequest: BloodRequestDetail;
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

function formatOrderQuantity(bloodRequest: BloodRequestDetail): string {
    const display = bloodRequest.order_quantity_display;
    if (display.mode === 'volume_ml' && display.ml != null) {
        return `${display.ml} ml`;
    }
    if (display.mode === 'units' && display.units != null) {
        return String(display.units);
    }
    return bloodRequest.requested_qty != null ? String(bloodRequest.requested_qty) : '—';
}

function statusLabel(status: string, t: (key: string) => string): string {
    const labels: Record<string, string> = {
        new: t('global.new_blood_requests'),
        approved: t('global.approved'),
        delivered: t('global.delivered'),
        rejected: t('global.rejected'),
    };
    return labels[status] ?? status;
}

export default function BloodRequestSummary({ bloodRequest }: BloodRequestSummaryProps) {
    const { t } = useTranslation();
    const bloodType = [bloodGroupLabel(bloodRequest.group), bloodRhLabel(bloodRequest.rh)]
        .filter((part) => part !== '—')
        .join(' ')
        .trim();

    return (
        <div className={BLOOD_UNIT_CARD_CLASS}>
            <div className="bg-gradient-to-br from-rose-600 via-rose-600 to-red-700 px-5 py-5 text-white">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-4">
                        <div className={BLOOD_UNIT_HERO_AVATAR_CLASS}>
                            <i className="bx bx-user text-3xl" />
                        </div>
                        <div className="min-w-0">
                            <h2 className="text-lg font-bold tracking-tight">
                                {bloodRequest.patient.name ?? `#${bloodRequest.id}`}
                            </h2>
                            {bloodRequest.patient.father_name && (
                                <p className="mt-0.5 text-sm text-rose-100/90">
                                    {t('global.father_name')}: {bloodRequest.patient.father_name}
                                </p>
                            )}
                            <p className="mt-1 text-xs text-rose-100/75">
                                #{bloodRequest.id}
                                {bloodRequest.patient.id_card ? ` · ${bloodRequest.patient.id_card}` : ''}
                                {bloodRequest.department_name ? ` · ${bloodRequest.department_name}` : ''}
                            </p>
                            <div className="mt-2 flex flex-wrap gap-2">
                                <Badge color={bloodStatusBadgeColor(bloodRequest.status)} className="font-normal">
                                    {statusLabel(bloodRequest.status, t)}
                                </Badge>
                                {bloodRequest.type && (
                                    <Badge color="gray" className="font-normal">
                                        {bloodRequest.type}
                                    </Badge>
                                )}
                            </div>
                        </div>
                    </div>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <HeroStat
                            label={t('global.blood_group')}
                            value={bloodType || '—'}
                        />
                        <HeroStat
                            label={t('global.requested_quantity')}
                            value={formatOrderQuantity(bloodRequest)}
                        />
                        <HeroStat
                            label={t('global.date')}
                            value={bloodRequest.created_at ?? '—'}
                            dir="ltr"
                        />
                        <HeroStat
                            label={t('global.remaining_quantity')}
                            value={String(bloodRequest.remaining_qty)}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}
