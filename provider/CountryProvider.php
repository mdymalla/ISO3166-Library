<?php

require_once("Country.php");
require_once("Reader.php");
require_once("LocaleMapper.php");

class CountryProvider
{
    const COUNTRYPATH = "./data/iso3166-1.json";
    const LOCALEPATH = "./data/3166-1/";
    const COUNT = 249;

    /**
     * Return array of Country objects translated to provided locale
     * if no locale is provided return default names
     */
    public static function getCountries(?string $locale = null): array
    {
        $countries = [];
        $locales = LocaleMapper::fallback($locale);

        foreach (Reader::read(self::COUNTRYPATH) as $country) {
            $name = $country["name"];
            $a2 = $country["alpha-2"];
            $a3 = $country["alpha-3"];
            $numeric = $country["numeric"];

            foreach ($locales as $code) {
                $currentLocale = Reader::read(self::LOCALEPATH.$code.'.json')["Names"];

                if (array_key_exists($a2, $currentLocale)) {
                    $name = $currentLocale[$a2];
                    break;
                }
            }

            $countries[] = new Country($name, $a2, $a3, $numeric);
        }

        return $countries;
    }

    /**
     * Return of array of Country names translated to given locale
     * if locale is not provided return default country name
     * if locale is provided write over default names with translations from locale
     */
    public static function getCountryNames(?string $locale = null): array
    {
        $countries = [];
        $locales = LocaleMapper::fallback($locale);

        foreach (Reader::read(self::COUNTRYPATH) as $a2 => $country) {
            $countries[$a2] = $country["name"];

            foreach ($locales as $code) {
                $currentLocale = Reader::read(self::LOCALEPATH.$code.'.json')["Names"];

                if (array_key_exists($a2, $currentLocale)) {
                    $countries[$a2] = $currentLocale[$a2];
                    break;
                }
            }
        }

        return $countries;
    }

    /**
     * Return Country object given locale and alpha 2 code
     */
    public static function getCountry(string $locale, string $a2): Country
    {
        $countries = Reader::read(self::COUNTRYPATH);

        $country = array('name' => $countries[$a2]['name'],
            'alpha-2' => $countries[$a2]['alpha-2'],
            'alpha-3' => $countries[$a2]['alpha-3'],
            'numeric' => $countries[$a2]['numeric']
        );

        if (file_exists(self::LOCALEPATH.$locale.'.json')) {
            $localeCountry = Reader::read(self::LOCALEPATH.$locale.'.json');

            if (array_key_exists($a2, $localeCountry["Names"])) {
                $country['name'] = $localeCountry["Names"][$a2];
            }
        }

        return new Country($country['name'], $country['alpha-2'], $country['alpha-3'], $country['numeric']);
    }

    /**
     * Return country name in given locale from provided alpha 2 code
     */
    public static function getCountryName(?string $locale = null, string $a2): string
    {
        $country = empty($locale) ? Reader::read(self::COUNTRYPATH) : Reader::read(self::LOCALEPATH.$locale.'.json')["Names"];

        return array_key_exists($a2, $country) ? $country[$a2] : '';
    }
}