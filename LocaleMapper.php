<?php

require_once("Reader.php");

class LocaleMapper
{
    const LOCALEMAP = "data/locales.json";

    // Validate if provided locale is supported
    public static function validate(?string $locale = null): bool
    {
        if (empty($locale)) {
            return false;
        }

        $map = Reader::read(self::LOCALEMAP);

        foreach ($map as $key => $value) {
            if ($locale === $key || in_array($locale, $value)) {
                return true;
            }
        }

        return false;
    }

    // Provid array of fallback locales
    public static function fallback(string $locale): array
    {
        if (!self::validate($locale)) {
            return [];
        }

        $map = Reader::read(self::LOCALEMAP);

        $locales = [];
        $locales[] = $locale;

        if (2 < strlen($locale)) {
            $parent = substr($locale, 0, 2);
            $locales[] = $parent;
        }

        return $locales;
    }
}