<?php

require_once("Region.php");
require_once("Reader.php");
require_once("LocaleMapper.php");

class RegionProvider
{
    /**
     * Return array of ISO3166-2 Region objects given locale, parent country, and administrative level
     * (if admin level is omitted we can probably default to 1)
     */
    public static function getRegions(?string $locale = null, string $country, ?int $adminLevel = 1): array
    {
        $regions = [];
        $locales = LocaleMapper::fallback($locale);

        foreach (self::getDefault() as $code => $region) {
            if ($region["administration-level"] !== $adminLevel) {
                continue;
            }

            if (self::getAlpha2($code) === $country) {
                $regions[] = new Region(self::getLocaleName($code, $region["name"], $locales),
                    $region["code"],
                    $region["type"],
                    $region["administration-level"]
                );
            }
        }

        return $regions;
    }

    /**
     * Return array of region names provided locale and country
     * e.g. getRegionNames('en', 'AU', 1); will return all 3166-2 names in Australia (en) at admin level 1
     */
    public static function getRegionNames(string $locale, string $country, int $adminLevel = 1): array
    {
        $regions = [];
        $locales = LocaleMapper::fallback($locale);

        foreach (self::getDefault() as $code => $region) {
            if ($region["administration-level"] !== $adminLevel) {
                continue;
            }

            if ($country === self::getAlpha2($code)) {
                $regions[] = self::getLocaleName($code, $region["name"], $locales);
            }
        }

        return $regions;
    }

    /**
     * Return Region object given provided locale and region code
     */
    public static function getRegion(string $locale, string $code): Region
    {
        $locales = LocaleMapper::fallback($locale);
        $regions = self::getDefault();

        return new Region(self::getLocaleName($code, $regions[$code]["name"], $locales),
            $regions[$code]["code"],
            $regions[$code]["type"],
            $regions[$code]["administration-level"]
        );
    }

    public static function getRegionName(string $locale, string $code): string
    {
        return self::getRegion($locale, $code)->getName();
    }

    public static function getAlpha2(string $regionCode): string
    {
        return substr($regionCode, 0, 2);
    }

    /**
     * Get array of regions (default)
     */
    public static function getDefault(): array
    {
        return Reader::read("./data/iso3166-2.json");
    }

    /**
     * Look for translation from provided locales, if none are found return default name
     */
    public static function getLocaleName(string $code, string $default, array $locales): string
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