<?php

require_once("Reader.php");

echo "Building 3166-1 & 3166-2 data\n";

echo "Generating most current ICU data via Symfony\Intl, might take awhile...";

//exec('php -f vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/bin/update-data.php');

echo "Complete...\n";

if (!file_exists('iso-codes')) {
    echo "Cloning Debian iso-codes resource...\n";

    exec('git clone https://salsa.debian.org/iso-codes-team/iso-codes.git');

    echo "Clone complete...\n";
}

echo "Creating initial country objects structure...\n";

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

file_put_contents('data/iso3166-1.json', json_encode($countries, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

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
}

file_put_contents('data/iso3166-2.json', json_encode($regions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

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

// Create initial 3166-2 dir with locale based translations
if (!file_exists("data/3166-2")) {
    mkdir("data/3166-2");
}

$path = 'iso-codes/iso_3166-2';
$dir = new DirectoryIterator($path);

foreach ($dir as $file) {
    $type = substr(strrchr($file,'.'), 1);

    if (0 === strcmp("po", $type)) {
        $name = $file->getFilename();
        $locale = basename($name,".po");

        exec("po2txt -t iso-codes/iso_3166-2/ordering.txt iso-codes/iso_3166-2/$name data/3166-2/$locale.txt");
    }
}

$path = 'data/3166-2';
$dir = new DirectoryIterator($path);

foreach ($dir as $file) {
    $type = substr(strrchr($file,'.'), 1);

    if (0 === strcmp("txt", $type)) {
        $name = $file->getFilename();
        $contents = file('data/3166-2/'.$name);
        $locale = basename($name,".txt");

        $subdivision = [];
        $codes = array_keys($regions);

        for ($i = 0; $i < count($regions); $i++) {
            $translation = str_replace("\n","", $contents[$i]);
            $subdivision["Names"][$codes[$i]] = $translation;
        }

        $encoded = json_encode($subdivision, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents('data/3166-2/'.$locale.'.json', $encoded);
        exec("rm -rf data/3166-2/$name");
    }
}

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

file_put_contents('data/locales.json', json_encode($locales, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "Build complete\n";
