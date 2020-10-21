<?php

require "classes/SubDivision.php";
require_once("Reader.php");
require_once("LocaleMapper.php");

class SubDivisionProvider
{
    /**
     * Return array of ISO3166-2 SubDivision objects given locale, parent country, and administrative level
     * (if admin level is omitted we can probably default to 1)
     */
    public static function getSubDivisions(?string $locale = null, string $country, ?int $adminLevel = 1): array
    {
        $subdivisions = [];
        $locales = LocaleMapper::fallback($locale, '3166-2');

        foreach (self::getDefault() as $code => $subdivision) {
            if ($subdivision["administration-level"] !== $adminLevel) {
                continue;
            }

            if (self::getAlpha2($code) === $country) {
                $subdivisions[] = new SubDivision(self::getLocaleName($code, $subdivision["name"], $locales),
                    $subdivision["code"],
                    $subdivision["type"],
                    $subdivision["administration-level"]
                );
            }
        }

        return $subdivisions;
    }

    /**
     * Return array of sub division names provided locale and country
     * e.g. getSubDivisionNames('en', 'AU', 1); will return all 3166-2 names in Australia (en) at admin level 1
     */
    public static function getSubDivisionNames(string $locale, string $country, int $adminLevel = 1): array
    {
        $subdivisions = [];
        $locales = LocaleMapper::fallback($locale, '3166-2');

        foreach (self::getDefault() as $code => $subdivision) {
            if ($subdivision["administration-level"] !== $adminLevel) {
                continue;
            }

            if ($country === self::getAlpha2($code)) {
                $subdivisions[] = self::getLocaleName($code, $subdivision["name"], $locales);
            }
        }

        return $subdivisions;
    }

    /**
     * Return SubDivision object given provided locale and code
     */
    public static function getSubDivision(string $locale, string $code): SubDivision
    {
        $locales = LocaleMapper::fallback($locale, '3166-2');
        $subdivisions = self::getDefault();

        return new SubDivision(self::getLocaleName($code, $subdivisions[$code]["name"], $locales),
            $subdivisions[$code]["code"],
            $subdivisions[$code]["type"],
            $subdivisions[$code]["administration-level"]
        );
    }

    public static function getSubDivisionName(string $locale, string $code): string
    {
        return self::getSubDivision($locale, $code)->getName();
    }

    private static function getAlpha2(string $regionCode): string
    {
        return substr($regionCode, 0, 2);
    }

    /**
     * Get array of regions (default)
     */
    private static function getDefault(): array
    {
        return Reader::read("./data/iso3166-2.json");
    }

    /**
     * Look for translation from provided locales, if none are found return default name
     */
    private static function getLocaleName(string $code, string $default, array $locales): string
    {
        foreach ($locales as $locale) {
            $current = Reader::read("./data/3166-2/".$locale.'.json')["Names"];

            if (array_key_exists($code, $current)) {
                $default = $current[$code];
                break;
            }
        }

        return $default;
    }
}