<?php

require 'vendor/autoload.php';

use CharlesRumley\PoToJson;
use MJDymalla\ISO3166Data\Helper;
use MJDymalla\ISO3166Data\Normalizer;
use MJDymalla\ISO3166Data\Validator\LocaleValidator;

echo "Building 3166-1 & 3166-2 data\n\n";

echo "Generating most current ICU data via Symfony\Intl, might take awhile...";

//exec('php -f vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/bin/update-data.php');

echo "Complete...\n\n";

if (!file_exists('iso-codes')) {
    echo "Cloning Debian iso-codes resource...\n";

    exec('git clone https://salsa.debian.org/iso-codes-team/iso-codes.git');

    echo "Clone complete...\n\n";
}

/**
 * @var array $ISOData - will eventually hold all relevant data merged in singular denormalized Country objects by hierarchy
 *
 * this singular hierarchy will allow data to be written to files depending on use case
 */
$ISOData = [];

/**
 * @var array $denormalizedCountryMetadata - holds denormalized Model\Country meta data objects
 */
$denormalizedCountryMetadata = [];

$countryMetaData = Helper::read(__DIR__.'/iso-codes/data/iso_3166-1.json');

foreach ($countryMetaData["3166-1"] as $country) {
    $A2 = $country["alpha_2"];
    $denormalizedCountryMetadata[$A2] = Normalizer\CountryMetaData::denormalize($country);
}

/**
 * @var array $denormalizedCountryTranslations - holds denormalized Country Model\Translation objects
 */
$denormalizedCountryTranslations = [];

$path = 'vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/data/regions';
$dir = new \DirectoryIterator($path);

foreach ($dir as $file) {
    if ($file->isDot()) {
        continue;
    }

    $locale = basename($path."/".$file->getFilename(), ".json");
    $translations = Helper::read($path."/".$file->getFilename());

    $denormalized = Normalizer\CountryTranslation::denormalizeArray($locale, $translations);

    if (null !== $denormalized) {
        $denormalizedCountryTranslations[$locale] = $denormalized;
    }
}

/**
 * @var array $denormalizedSubDivisionMetaData - holds denormalized Model\SubDivision meta data objects
 */
$denormalizedSubDivisionMetaData = [];

$subDivisionMetaData = Helper::read('iso-codes/data/iso_3166-2.json');

foreach ($subDivisionMetaData["3166-2"] as $subdivision) {
    $code = $subdivision["code"];
    $A2 = substr($code, 0, 2);

    $denormalizedSubDivisionMetaData[$A2][$code] = Normalizer\SubDivisionMetaData::denormalize($subdivision);
}

/**
 * @var array $subDivisionNameIndex - holds mapping between iso3166-2 subdivision code and name
 */
$subDivisionNameIndex = [];

foreach ($denormalizedSubDivisionMetaData as $country) {
    foreach ($country as $subdivision) {
        $subDivisionNameIndex[$subdivision->getCode()] = $subdivision->getName();
    }
}

echo "Creating 3166-2 locale based translations, might take awhile...";

/**
 * @var array $denormalizedSubDivisionTranslations - holds denormalized SubDivision Model\Translation objects
 */
$denormalizedSubDivisionTranslations = [];

$path = 'iso-codes/iso_3166-2';
$dir = new \DirectoryIterator($path);
$poToJson = new PoToJson();

foreach ($dir as $file) {
    $type = substr(strrchr($file,'.'), 1);

    if (0 === strcmp("po", $type)) {
        $filename = $file->getFilename();
        $locale = basename($filename,".po");

        if (!LocaleValidator::isValid($locale)) {
            $locale = LocaleValidator::standardize($locale);
        }

        $rawJson = $poToJson->withPoFile($path.'/'.$filename)->toRawJson();
        $translations = json_decode($rawJson, true);

        $localeBasedTranslations = Normalizer\SubDivisionTranslation::denormalizeArray($locale, $subDivisionNameIndex, $translations);
        $denormalizedSubDivisionTranslations[$locale] = $localeBasedTranslations;
    }
}

echo "Complete...\n\n";

// Merge denormalized arrays into complete hierarchal structure
$ISOData = Helper::merge(
    $denormalizedCountryMetadata,
    $denormalizedSubDivisionMetaData,
    $denormalizedCountryTranslations,
    $denormalizedSubDivisionTranslations
);

echo "Writing files...";

// Write files
if (file_exists('data')) {
    exec('rm -rf data');
}

mkdir('data');

if (file_exists("data/3166-1")) {
    exec('rm -rf data/3166-1');
}

mkdir('data/3166-1');

if (file_exists("data/3166-2")) {
    exec('rm -rf data/3166-2');
}

mkdir('data/3166-2');

// Write Country meta data to single file
$normalizedCountryMetaData = [];

foreach ($denormalizedCountryMetadata as $A2 => $country) {
    $normalizedCountryMetaData[$A2] = Normalizer\CountryMetaData::normalize($country);
}

ksort($normalizedCountryMetaData);

file_put_contents(__DIR__."/data/3166-1/meta.json", json_encode($normalizedCountryMetaData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// Write SubDivision meta data to country separated files
foreach ($denormalizedSubDivisionMetaData as $A2 => $subdivisions) {
    $normalizedSubDivisions = [];

    mkdir("data/3166-2/$A2");

    foreach ($subdivisions as $subdivision) {
        $normalizedSubDivisions[$subdivision->getCode()] = Normalizer\SubDivisionMetaData::normalize($subdivision);
    }

    file_put_contents(__DIR__."/data/3166-2/$A2/meta.json", json_encode($normalizedSubDivisions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// Write Country translations to locale separated files
foreach ($denormalizedCountryTranslations as $locale => $translations) {
    $normalizedCountryTranslations = [];

    foreach ($translations as $translation) {
        $normalizedCountryTranslations[$translation->getCode()] = Normalizer\CountryTranslation::normalize($translation);
    }

    file_put_contents(__DIR__."/data/3166-1/$locale.json", json_encode($normalizedCountryTranslations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// Write SubDivision translations to country and locale separated files
$normalizedSubDivisionTranslations = [];

foreach ($denormalizedSubDivisionTranslations as $locale => $translations) {
    foreach ($translations as $translation) {
        $A2 = substr($translation->getCode(), 0 , 2);

        $normalizedSubDivisionTranslations[$A2][$locale][$translation->getCode()] = Normalizer\CountryTranslation::normalize($translation);
    }
}

foreach ($normalizedSubDivisionTranslations as $A2 => $localeSeparated) {
    foreach ($localeSeparated as $locale => $translations) {
        file_put_contents(__DIR__."/data/3166-2/$A2/$locale.json", json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}

echo "Complete...\n\n";

echo "Build finished\n";
