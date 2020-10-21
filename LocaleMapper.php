<?php

require_once("Reader.php");

class LocaleMapper
{
    /**
     * Check if provided locale is valid both, both as an official locale and library supported
     */
    private static function validate(?string $locale = null, string $iso): bool
    {
        if (empty($locale)) {
            return false;
        }

        if (!self::supported($locale, $iso)) {
            return false;
        }

        foreach (self::getDefault() as $key => $value) {
            if ($locale === $key || in_array($locale, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Provide array of locales that can be checked in order for translation
     * If 'en_AU' is provided, ordering should be ['en_AU', 'en', default]
     */
    public static function fallback(string $locale, string $iso): array
    {
        if (!self::validate($locale, $iso)) {
            return [];
        }

        $locales = array($locale);

        // if locale is larger than 2 it's probably country specific and will have a parent locale/language
        // i.e. 'en_AU' comes from 'en'
        if (2 < strlen($locale)) {
            $parent = substr($locale, 0, 2);
            $locales[] = $parent;
        }

        return $locales;
    }

    /**
     * Iterate the specified iso standard directory and check if the requested locale is supported
     */
    private static function supported(string $locale, string $iso): bool
    {
        $path = "data/$iso";
        $dir = new DirectoryIterator($path);

        foreach ($dir as $file) {
            $name = basename($file->getFilename(),".json");

            if (0 === strcmp($locale, $name)) {
                return true;
            }
        }

        return false;
    }

    private static function getDefault(): array
    {
        return Reader::read("data/locales.json");
    }
}