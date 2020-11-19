<?php

namespace MJDymalla\ISO3166Data\Normalizer;

use MJDymalla\ISO3166Data\Model\Translation;

class SubDivisionTranslation
{
    public static function normalize(Translation $translation): string
    {
        return $translation->getTranslation();
    }

    public static function denormalize(string $A2, string $locale, string $translation): Translation
    {
        return new Translation($A2, $locale, $translation);
    }

    public static function denormalizeArray(
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