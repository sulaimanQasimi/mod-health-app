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

export function parseAgeValue(age: string | null | undefined): {
    year: string;
    month: string;
    day: string;
} {
    if (!age) {
        return { year: '', month: '', day: '' };
    }

    const yearMatch = age.match(/(\d+)\s*ساله/u);
    if (yearMatch) {
        return { year: yearMatch[1], month: '', day: '' };
    }

    const monthMatch = age.match(/(\d+)\s*ماه/u);
    if (monthMatch) {
        return { year: '', month: monthMatch[1], day: '' };
    }

    const dayMatch = age.match(/(\d+)\s*روز/u);
    if (dayMatch) {
        return { year: '', month: '', day: dayMatch[1] };
    }

    return { year: '', month: '', day: '' };
}
