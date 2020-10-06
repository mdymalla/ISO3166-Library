<?php

require_once("Region.php");
require_once("Reader.php");

class RegionProvider
{
    const REGIONPATH = "./data/iso3166-2.json";
    const LOCALEPATH = "./data/3166-2/";

    /**
     * Return array of ISO3166-2 Region objects given locale, parent country, and administrative level
     * (if admin level is omitted we can probably default to 1)
     */
    public static function getRegions(string $locale, string $country, ?int $adminLevel = 1): array
    {
        $regions = [];

        $translations = Reader::read(self::LOCALEPATH.$locale.'.json');

        foreach (Reader::read(self::REGIONPATH) as $code => $region) {
            if ($region["administration-level"] !== $adminLevel) {
                continue;
            }

            if (self::getAlpha2($code) === $country) {
                $name = array_key_exists($code, $translations["Names"]) ? $translations["Names"][$code] : $region["name"];
                $code = $region["code"];
                $type = $region["type"];
                $admin = $region["administration-level"];

                $regions[] = new Region($name, $code, $type, $admin);
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

        foreach (Reader::read(self::LOCALEPATH.$locale.'.json')["Names"] as $code => $name) {
            $parent = self::getAlpha2($code);

            if ($country === $parent) {
                $regions[] = $name;
            }
        }

        return $regions;
    }

    /**
     * Return Region object given provided locale and region code
     */
    public static function getRegion(string $locale, string $code): Region
    {
        $regions = Reader::read(self::REGIONPATH);
        $translation = Reader::read(self::LOCALEPATH.$locale.'.json');

        $name = array_key_exists($code, $translation["Names"]) ? $translation["Names"][$code] : $regions[$code]["name"];
        $code = $regions[$code]["code"];
        $type = $regions[$code]["type"];
        $admin = $regions[$code]["administration-level"];

        return new Region($name, $code, $type, $admin);
    }

    public static function getAlpha2(string $regionCode): string
    {
        return substr($regionCode, 0, 2);
    }
}