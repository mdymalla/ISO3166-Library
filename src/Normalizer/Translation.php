<?php

namespace MJDymalla\ISO3166Data\Normalizer;

use MJDymalla\ISO3166Data\Model;

class Translation
{
    /**
     * Normalizes a Translation object into a single translation string
     * @param Translation $translation  Translation object to normalize
     *
     * @return string
     */
    public static function normalize(Model\Translation $translation): string
    {
        return $translation->getTranslation();
    }

    /**
     * Denormalizes an array into a Translation object
     * @param string $A2  Alpha-2 code of the country
     * @param string $locale  Locale of the translation
     * @param string $translation  Country name translated into specific locale
     *
     * @return Model\Translation
     */
    public static function denormalize(string $A2, string $locale, string $translation): Model\Translation
    {
        return new Model\Translation($A2, $locale, $translation);
    }

    /**
     * Denormalizes an array of country translations into Model\Translation objects
     * @param string $locale  locale of translations given
     * @param array $translations  array of translation strings to be denormalized
     *
     * @return array
     */
    public static function denormalizeCountryArray(string $locale, array $translations): ?array
    {
        if (!array_key_exists("Names", $translations)) {
            return null;
        }

        $denormalized = [];

        foreach ($translations["Names"] as $A2 => $translation) {
            $denormalized[] = self::denormalize($A2, $locale, $translation);
        }

        return $denormalized;
    }

    /**
     * Denormalizes an array subdivision translations into Model\Translation objects
     * @param string $locale  locale of translations given
     * @param array $subDivisionNameIndex  index to map translations to there corresponding code
     * @param array $translations  array of translation strings to be denormalized
     *
     * @return array
     */
    public static function denormalizeSubDivArray(
        string $locale,
        array $subDivisionNameIndex,
        array $translations
    ): array
    {
        $denormalized = [];

        foreach ($translations as $key => $pluralities) {
            if (empty($pluralities[1])) {
                continue;
            }

            $translation = $pluralities[1];

            $codes = array_keys($subDivisionNameIndex, $key, true);

            foreach ($codes as $code) {
                $denormalized[] = self::denormalize($code, $locale, $translation);
            }
        }

        return $denormalized;
    }
}