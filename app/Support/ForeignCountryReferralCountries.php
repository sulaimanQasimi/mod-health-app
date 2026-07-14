<?php

namespace App\Support;

class ForeignCountryReferralCountries
{
    /**
     * @return list<array{value: string, name_en: string, name_dr: string, name_ps: string}>
     */
    public static function options(): array
    {
        return [
            ['value' => 'india', 'name_en' => 'India', 'name_dr' => 'هند', 'name_ps' => 'هند'],
            ['value' => 'pakistan', 'name_en' => 'Pakistan', 'name_dr' => 'پاکستان', 'name_ps' => 'پاکستان'],
            ['value' => 'iran', 'name_en' => 'Iran', 'name_dr' => 'ایران', 'name_ps' => 'ایران'],
            ['value' => 'turkey', 'name_en' => 'Turkey', 'name_dr' => 'ترکیه', 'name_ps' => 'ترکیه'],
            ['value' => 'germany', 'name_en' => 'Germany', 'name_dr' => 'آلمان', 'name_ps' => 'المان'],
            ['value' => 'uae', 'name_en' => 'United Arab Emirates', 'name_dr' => 'امارات متحده عربی', 'name_ps' => 'متحده عرب امارات'],
            ['value' => 'saudi_arabia', 'name_en' => 'Saudi Arabia', 'name_dr' => 'عربستان سعودی', 'name_ps' => 'سعودی عربستان'],
            ['value' => 'usa', 'name_en' => 'United States', 'name_dr' => 'ایالات متحده آمریکا', 'name_ps' => 'متحده ایالات'],
            ['value' => 'uk', 'name_en' => 'United Kingdom', 'name_dr' => 'بریتانیا', 'name_ps' => 'برتانیه'],
            ['value' => 'china', 'name_en' => 'China', 'name_dr' => 'چین', 'name_ps' => 'چین'],
            ['value' => 'russia', 'name_en' => 'Russia', 'name_dr' => 'روسیه', 'name_ps' => 'روسیه'],
            ['value' => 'other', 'name_en' => 'Other', 'name_dr' => 'سایر', 'name_ps' => 'نور'],
        ];
    }

    public static function label(?string $value, ?string $locale = null): ?string
    {
        if (! $value) {
            return null;
        }

        $locale = $locale ?? app()->getLocale();
        $option = collect(self::options())->firstWhere('value', $value);

        if (! $option) {
            return $value;
        }

        return match ($locale) {
            'dr' => $option['name_dr'],
            'ps' => $option['name_ps'],
            default => $option['name_en'],
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::options(), 'value');
    }
}
