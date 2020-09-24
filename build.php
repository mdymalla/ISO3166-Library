<?php

echo "Building 3166-1 & 3166-2 data\n";

echo "Generating most current ICU data via Symfony\Intl, might take awhile...";

exec('php -f vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/bin/update-data.php');

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

// create initial country object
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
        $countries[$code]["3166-2"][$region['code']] = array(
            'code' => $region['code'],
            'type' => $region['type'],
            'names' => ['en' => $region['name']]
        );
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
            if (false !== strpos($translations[$i], "name for") || false !== strpos($translations[$i], "Name for")) {
                $alpha3 = stripRegion($translations[$i]);
                $alpha2 = alpha3to2($countries, $alpha3);
                $translation = "";

                $j = $i;
                while ($j < $i + 4) {
                    if (false !== strpos($translations[$j], "msgstr ")) {
                        $translation = stripLine($translations[$j], 'msgstr ');
                        break;
                    } else {
                        $j++;
                    }
                }

                if (array_key_exists($alpha2, $countries)) {
                    if (empty($translation)) {
                        $translation = "No translation";
                    }

                    $countries[$alpha2]["names"][$language] = $translation;
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
            if (false !== strpos($translations[$i], "#. Name for")) {
                $translation = "";
                $regionCode = stripRegion($translations[$i]);
                $countryCode = substr($regionCode, 0, 2);

                $j = $i;
                while ($j < $i + 4) {
                    if (false !== strpos($translations[$j], "msgstr ")) {
                        $translation = stripLine($translations[$j], 'msgstr ');
                        break;
                    } else {
                        $j++;
                    }
                }

                if (array_key_exists($countryCode, $countries)) {
                    if (empty($translation)) {
                        $translation = "No translation";
                    }

                    $countries[$countryCode]["3166-2"][$regionCode]["names"][$language] = $translation;
                }
            }
        }
    }
}

function stripRegion($region) {
    $exploded = explode(' ', $region);
    $region = $exploded[count($exploded) - 1];
    $region = trim(preg_replace('/\s\s+/', '', $region));
    return $region;
}

function stripLine($translation, $strip) {
    $translation = trim($translation, $strip);
    $translation = str_replace('"', '', $translation);
    $translation = trim(preg_replace('/\s\s+/', '', $translation));
    return $translation;
}

function alpha3to2($objects, $alpha3) {
    foreach ($objects as $code) {
        if (0 === strcmp($code['alpha-3'], $alpha3)) {
            return $code["alpha-2"];
        }
    }
    return null;
}

echo "Creating file structure...\n";

// create files
foreach ($countries as $key => $value) {
    $path = 'data/'.$key.'.json';
    file_put_contents($path, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

echo "Finished 3166-2...\n";

echo "Cleaning...\n";
exec('rm -rf iso-codes');
echo "Complete\n";


