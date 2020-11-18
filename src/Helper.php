<?php

namespace MJDymalla\ISO3166Data;

use MJDymalla\ISO3166Data\Model\Country;

class Helper
{
    public static function read(string $path)
    {
        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (null === $data) {
            return null;
        }

        return $data;
    }

    public static function merge(
        array $countries,
        array $subdivisions,
        array $countryTranslations,
        array $subdivisionTranslations
    ): array
    {
        // add subdivisions
        foreach ($subdivisions as $A2 => $subdivison) {
            if (array_key_exists($A2, $countries)) {
                $countries[$A2]->setSubdivisions($subdivison);
            }
        }

        // add country translations
        foreach ($countryTranslations as $locale => $translations) {
            foreach ($translations as $translation) {
                $countries[$translation->getCode()]->setTranslations($translation);
            }
        }

        // add subdivision translations
        foreach ($subdivisionTranslations as $locale => $translations) {
            foreach ($translations as $translation) {
                $code = $translation->getCode();
                $A2 = substr($code, 0, 2);

                $countries[$A2]->getSubdivisions()[$code]->setTranslations($translation);
            }
        }

        return $countries;
    }
}