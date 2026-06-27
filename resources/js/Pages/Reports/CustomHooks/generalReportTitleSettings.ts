import type { CSSProperties } from 'react';

export type TitleAlignment = 'left' | 'center' | 'right';
export type TitleFontWeight = 'normal' | 'bold';

export interface ReportTitleSettings {
    text: string;
    alignment: TitleAlignment;
    fontSize: number;
    fontWeight: TitleFontWeight;
    textColor: string;
    backgroundColor: string;
    paddingTop: number;
    paddingBottom: number;
    paddingInline: number;
    marginTop: number;
    marginBottom: number;
}

export const TITLE_FONT_SIZE_OPTIONS = [12, 14, 16, 18, 20, 24, 28, 32, 36, 42];

export const TITLE_SPACING_OPTIONS = [0, 4, 8, 12, 16, 20, 24, 32, 40, 48];

export function createDefaultTitleSettings(): ReportTitleSettings {
    return {
        text: 'عنوان گزارش',
        alignment: 'center',
        fontSize: 20,
        fontWeight: 'bold',
        textColor: '#000000',
        backgroundColor: '#ffffff',
        paddingTop: 8,
        paddingBottom: 8,
        paddingInline: 12,
        marginTop: 0,
        marginBottom: 8,
    };
}

export function countTitleSettingChanges(settings: ReportTitleSettings): number {
    const defaults = createDefaultTitleSettings();
    return (Object.keys(defaults) as Array<keyof ReportTitleSettings>).filter(
        (key) => settings[key] !== defaults[key],
    ).length;
}

export function buildTitleStyle(settings: ReportTitleSettings): CSSProperties {
    return {
        textAlign: settings.alignment,
        fontSize: `${settings.fontSize}px`,
        fontWeight: settings.fontWeight,
        color: settings.textColor,
        backgroundColor:
            settings.backgroundColor === 'transparent' ? 'transparent' : settings.backgroundColor,
        paddingTop: `${settings.paddingTop}px`,
        paddingBottom: `${settings.paddingBottom}px`,
        paddingInline: `${settings.paddingInline}px`,
        marginTop: `${settings.marginTop}px`,
        marginBottom: `${settings.marginBottom}px`,
    };
}
