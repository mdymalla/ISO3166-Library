<?php

require 'vendor/autoload.php';

use CharlesRumley\PoToJson;

require_once("Reader.php");

echo "Building 3166-1 & 3166-2 data\n\n";

echo "Generating most current ICU data via Symfony\Intl, might take awhile...";

//exec('php -f vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/bin/update-data.php');

echo "Complete...\n\n";

if (!file_exists('iso-codes')) {
    echo "Cloning Debian iso-codes resource...\n";

    exec('git clone https://salsa.debian.org/iso-codes-team/iso-codes.git');

    echo "Clone complete...\n\n";
}

echo "Creating initial country objects structure...\n\n";

if (!file_exists('data')) {
    mkdir('data');
}

// Create initial 3166-1 json file to maintain alpha-2, alpha-3, and numeric mapping
$countries = [];

$decoded = Reader::read('vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/data/regions/en.json');

foreach ($decoded["Names"] as $key => $value) {
    $countries[$key] = ['alpha-2' => $key,
        'alpha-3' => null,
        'numeric' => null,
        'name' => $value
    ];
}

$decoded = Reader::read('iso-codes/data/iso_3166-1.json');

foreach ($decoded["3166-1"] as $country) {
    $code = $country["alpha_2"];

    if (array_key_exists($code, $countries)) {
        $countries[$code]["alpha-3"] = $country["alpha_3"];
        $countries[$code]["numeric"] = $country["numeric"];
    }
}

file_put_contents(__DIR__.'/data/iso3166-1.json', json_encode($countries, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// Create intial 3166-2 json file to maintain type and admin level
$regions = [];

$decoded = Reader::read('iso-codes/data/iso_3166-2.json');

foreach ($decoded["3166-2"] as $region) {
    $admin = array_key_exists("parent", $region) ? 2 : 1;

    $regions[$region['code']] = [
        'name' => $region['name'],
        'code' => $region['code'],
        'type' => $region['type'],
        'administration-level' => $admin
    ];

    if (array_key_exists("parent", $region)) {
        $regions[$region['code']]['parent'] = $region['parent'];
    }
}

file_put_contents(__DIR__.'/data/iso3166-2.json', json_encode($regions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// Create initial 3166-1 dir with locale based translations
if (!file_exists("data/3166-1")) {
    mkdir("data/3166-1");
}

$path = 'vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/data/regions';
$dir = new DirectoryIterator($path);

foreach ($dir as $file) {
    if (0 !== strcmp("json", substr(strrchr($file,'.'), 1))) {
        continue;
    }

    $name = $file->getFilename();
    copy($path."/".$name, "data/3166-1/".$name);
}

// Create 3166-2 ordering array
// All locale based po files are built from iso_3166-2.pot file so they all have the same ordering
// We can create an array to hold what that ordering looks like for mapping region codes to translations
$ordering = [];

$pot = file('iso-codes/iso_3166-2/iso_3166-2.pot');

foreach ($pot as $line) {
    if (strpos($line, '#. Name for ') === false) {
        continue;
    }

    $code = [];

    foreach (explode(" ", $line) as $word) {
        if (strpos($word, "-") !== false) {
            $var = str_replace("\n","", $word);
            $code[] = str_replace(",", "", $var);
        }
    }

    $ordering[] = $code;
}

echo "Creating 3166-2 locale based translaions...";

// Create initial 3166-2 dir with locale based translations
if (!file_exists("data/3166-2")) {
    mkdir("data/3166-2");
}

$path = 'iso-codes/iso_3166-2';
$dir = new DirectoryIterator($path);
$poToJson = new PoToJson();
$subdivisions = [];

foreach ($dir as $file) {
    $type = substr(strrchr($file,'.'), 1);

    if (0 === strcmp("po", $type)) {
        $name = $file->getFilename();
        $locale = basename($name,".po");

        $subdivision = [];

        // To maintain ordering of subdivisions we need to initially include both fuzzy translations and empty ones
        // toRawJson(fuzzy = true)
        $rawJson = $poToJson->withPoFile($path.'/'.$name)->toRawJson(true);
        $translations = json_decode($rawJson, true);

        $i = 0;
        foreach ($translations as $translation) {
            if (count($translation) > 2) {
                continue;
            }

            if (count($ordering[$i]) > 1) {
                foreach ($ordering[$i] as $code) {
                    $subdivision["Names"][$code] = $translation[1];
                }
            } else {
                $subdivision["Names"][$ordering[$i][0]] = $translation[1];
            }

            $i++;
        }

        ksort($subdivision["Names"]);
        $subdivisions[$locale] = $subdivision;
    }
}

echo "Complete...\n\n";
echo "Removing fuzzy, duplicate, and empty translations...";

// Clean locale based data (remove fuzzy translations, duplicates, and empty)
$path = 'iso-codes/iso_3166-2';
$dir = new DirectoryIterator($path);

$fuzzy = [];

foreach ($dir as $file) {
    $type = substr(strrchr($file,'.'), 1);

    if (0 === strcmp("po", $type)) {
        $name = $file->getFilename();
        $locale = basename($name,".po");
        $contents = file($path.'/'.$name);

        $code = [];

        // All po files follow same structure, if a translation is fuzzy it will be tagged underneath region code
        // #. Name for AU-QLD
        // #, fuzzy
        // if a line provides a region code and the proceeding line is tagged as fuzzy we map the locale to that region
        // so it can be removed from dataset
        for ($i = 0; $i < count($contents); $i++) {
            if (strpos($contents[$i], "#. Name for ") !== false && strpos($contents[$i + 1], "fuzzy") !== false) {
                foreach (explode(" ", $contents[$i]) as $word) {
                    if (strpos($word, "-") !== false) {
                        $var = str_replace("\n","", $word);
                        $code[] = str_replace(",", "", $var);
                    }
                }
            }
        }

        if (!empty($code)) {
            $fuzzy[$locale] = $code;
        }
    }
}

$path = 'data/3166-2';
$dir = new DirectoryIterator($path);

foreach ($subdivisions as $locale => $subdivision) {
    foreach ($subdivision["Names"] as $code => $translation) {
        if (empty($translation) || 0 === strcmp($regions[$code]["name"], $translation)) {
            // remove empty translation or translation that is the same as default
            unset($subdivision["Names"][$code]);
        } elseif (array_key_exists($locale, $fuzzy) && in_array($code, $fuzzy[$locale])) {
            // remove translation that is found to be fuzzy
            unset($subdivision["Names"][$code]);
        }
    }

    $encoded = json_encode($subdivision, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    file_put_contents('data/3166-2/'.$locale.'.json', $encoded);
}

echo "Complete...\n\n";

// Create locale mapping file
$decoded = Reader::read('vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/data/locales/en.json');

$locales = [];

foreach ($decoded["Names"] as $locale => $name) {
    if (strlen($locale) === 2) {
        $locales[$locale] = [];
    } else {
        $parent = substr($locale, 0, 2);

        if (array_key_exists($parent, $locales)) {
            $locales[$parent][] = $locale;
        }
    }
}

file_put_contents(__DIR__.'/data/locales.json', json_encode($locales, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "Build finished\n";
