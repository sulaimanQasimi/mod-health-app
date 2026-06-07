export function buildAgeValue(
    year: string | number,
    month: string | number,
    day: string | number,
): string {
    const yearValue = year !== '' && year !== null && year !== undefined ? String(year) : '';
    const monthValue = month !== '' && month !== null && month !== undefined ? String(month) : '';
    const dayValue = day !== '' && day !== null && day !== undefined ? String(day) : '';

    if (yearValue !== '') {
        return `${yearValue} ساله`;
    }

    if (monthValue !== '') {
        return `${monthValue} ماه`;
    }

    if (dayValue !== '') {
        return `${dayValue} روز`;
    }

    return '';
}
