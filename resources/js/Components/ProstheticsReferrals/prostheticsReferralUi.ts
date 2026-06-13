export function prostheticReferralStatusLabel(status: string, t: (key: string) => string): string {
    const key = `global.prosthetics_referral_status_${status}`;
    const label = t(key);

    return label === key ? status.replace(/_/g, ' ') : label;
}
