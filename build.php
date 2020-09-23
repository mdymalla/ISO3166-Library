<?php

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

$countries = [];

$country_codes = file_get_contents('vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/data/regions/en.json');
$decoded = json_decode($country_codes, true);

// create initial country object - locale agnostic
foreach ($decoded["Names"] as $key => $value) {
    $countries[$key] = ['alpha-2' => $key,
        'alpha-3' => null,
        'numeric' => null,
        'names' => ['en' => $value],
        '3166-2' => []
    ];
}

echo "Countries created with alpha-2 mapping and name...\n";
echo "Adding alpha-3 and numeric mapping...\n";

// get alpha-3 and numeric mapping for 3166-1
$path = 'iso-codes/data/iso_3166-1.json';
$content = file_get_contents($path);
$decoded = json_decode($content, true);

foreach ($decoded["3166-1"] as $country) {
    $code = $country["alpha_2"];

    if (array_key_exists($code, $countries)) {
        $countries[$code]["alpha-3"] = $country["alpha_3"];
        $countries[$code]["numeric"] = $country["numeric"];
    } else {
        // should show if there is any disparity between ICU and Debian source
        echo "Missing ".$country["name"]."...\n";
    }
}

echo "Starting 3166-2...\n";

// add 3166-2 codes
$path = 'iso-codes/data/iso_3166-2.json';
$content = file_get_contents($path);
$decoded = json_decode($content, true);

foreach ($decoded["3166-2"] as $region) {
    $code = substr($region["code"], 0, 2);

    if (array_key_exists($code, $countries)) {
        $obj = ['code' => $region['code'],
            'type' => $region['type'],
            'names' => ['en' => $region['name']]
        ];

        $countries[$code]["3166-2"][$region['code']] = $obj;
    }
}

echo "3166-2 codes added...\n";
echo "Adding 3166-1 translations, might take awhile...";

// add 3166-1 translations
$path = 'iso-codes/iso_3166-1';
$dir = new DirectoryIterator($path);

foreach ($dir as $file) {
    $type = substr(strrchr($file,'.'), 1);

    if (0 === strcmp("po", $type)) {
        $language = basename($file->getFilename(),".po");
        $translations = file($path.'/'.$file->getFilename());

        for ($i = 0; $i < count($translations); $i++) {
            $line = "msgid ";

            if (false !== strpos($translations[$i], $line)) {
                $country = str_replace('"', '', substr($translations[$i], strpos($translations[$i], '"')));
                $translation = str_replace('"', '', substr($translations[$i + 1], strpos($translations[$i], '"')));
                $alpha2 = getAlpha2($countries, trim(preg_replace('/\s\s+/', '', $country)));

                if (!empty($alpha2)) {
                    $countries[$alpha2]["names"][$language] = trim(preg_replace('/\s\s+/', '', $translation));
                }
            }
        }
    }
}

echo "complete...\n";
echo "Adding 3166-2 translations, might take awhile...";

// add 3166-2 translations
$path = 'iso-codes/iso_3166-2';
$dir = new DirectoryIterator($path);

foreach ($dir as $file) {
    $type = substr(strrchr($file,'.'), 1);

    if (0 === strcmp("po", $type)) {
        $language = basename($file->getFilename(),".po");
        $translations = file($path.'/'.$file->getFilename());

        for ($i = 0; $i < count($translations); $i++) {
            $line = "msgid ";

            if (false !== strpos($translations[$i], $line)) {
                $region = str_replace('"', '', substr($translations[$i], strpos($translations[$i], '"')));
                $translation = str_replace('"', '', substr($translations[$i + 1], strpos($translations[$i], '"')));

                $regionCode = trim($translations[$i - 1], '#. Name for ');
                $trimmed = trim(preg_replace('/\s\s+/', '', $regionCode));
                $countryCode = substr($regionCode, 0, 2);

                if (array_key_exists($countryCode, $countries)) {
                    $countries[$countryCode]["3166-2"][$trimmed]["names"][$language] = trim(preg_replace('/\s\s+/', '', $translation));
                }
            }
        }
    }
}

echo "complete...\n";

function getAlpha2($objects, $name) {
    foreach ($objects as $code) {
        if (array_key_exists('en', $code['names'])) {
            if (0 === strcmp($code['names']['en'], $name)) {
                return $code["alpha-2"];
            }
        }
    }
    return null;
}

echo "Creating file structure...\n";

// create files
foreach ($countries as $key => $value) {
    $path = 'data/'.$key.'.json';
    file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT));
}

echo "Finished 3166-2...\n";

echo "Cleaning...\n";
exec('rm -rf iso-codes');
echo "Complete\n";


