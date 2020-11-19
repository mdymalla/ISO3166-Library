<?php

namespace MJDymalla\ISO3166Data\Validator;

class LocaleValidator
{
    public static function isValid(string $locale): bool
    {
        if (preg_match("/[^a-zA-Z_]/", $locale)) {
            return false;
        }

        return true;
    }

    // needs work
    public static function standardize(string $invalidLocale): string
    {
        $standardizedScripts = [
            "latin" => "Latn"
        ];

        $validLocale = str_replace("@", "_", $invalidLocale);
        $script = explode("_", $validLocale)[1];

        if (array_key_exists($script, $standardizedScripts)) {
            $validLocale = str_replace($script, $standardizedScripts[$script], $validLocale);
        }

        return $validLocale;
    }
}