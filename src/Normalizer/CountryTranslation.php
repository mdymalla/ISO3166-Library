<?php

namespace MJDymalla\ISO3166Data\Normalizer;

use MJDymalla\ISO3166Data\Model\Translation;

class CountryTranslation
{
    public static function normalize(Translation $translation): string
    {
        return $translation->getTranslation();
    }

    public static function denormalize(string $A2, string $locale, string $translation): Translation
    {
        return new Translation($A2, $locale, $translation);
    }

    public static function denormalizeArray(string $locale, array $translations): ?array
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
}