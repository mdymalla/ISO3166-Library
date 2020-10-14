<?php

require_once("classes/Country.php");
require_once("Reader.php");
require_once("LocaleMapper.php");

class CountryProvider
{
    /**
     * Return array of Country objects translated to provided locale
     * if no locale is provided return default names
     */
    public static function getCountries(?string $locale = null): array
    {
        $countries = [];
        $locales = LocaleMapper::fallback($locale);

        foreach (self::getDefault() as $country) {
            $countries[] = new Country(self::getLocaleName($a2, $country["name"], $locales),
                $country["alpha-2"],
                $country["alpha-3"],
                $country["numeric"]
            );
        }

        return $countries;
    }

    /**
     * Return of array of Country names translated to provided locale
     */
    public static function getCountryNames(?string $locale = null): array
    {
        $countries = [];
        $locales = LocaleMapper::fallback($locale);

        foreach (self::getDefault() as $a2 => $country) {
            $countries[] = self::getLocaleName($a2, $country['name'], $locales);
        }

        return $countries;
    }

    /**
     * Return Country object given locale and alpha 2 code
     */
    public static function getCountry(?string $locale = null, string $a2): Country
    {
        $locales = LocaleMapper::fallback($locale);
        $countries = self::getDefault();

        return new Country(self::getLocaleName($a2, $countries[$a2]['name'], $locales),
            $countries[$a2]['alpha-2'],
            $countries[$a2]['alpha-3'],
            $countries[$a2]['numeric']
        );
    }

    /**
     * Return country name in given locale from provided alpha 2 code
     */
    public static function getCountryName(?string $locale = null, string $a2): string
    {
        return self::getCountry($locale, $a2)->getName();
    }

    /**
     * Get array of countries (default)
     */
    public static function getDefault(): array
    {
        return Reader::read("./data/iso3166-1.json");
    }

    /**
     * Look for translation from provided locales, if none are found return default name
     */
    public static function getLocaleName(string $a2, string $default, array $locales): string
    {
        foreach ($locales as $locale) {
            $current = Reader::read("./data/3166-1/".$locale.'.json')["Names"];

            if (array_key_exists($a2, $current)) {
                $default = $current[$a2];
                break;
            }
        }

        return $default;
    }
}