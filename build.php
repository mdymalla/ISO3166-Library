<?php

require_once "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

$client = new Client();

//chdir('vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/bin');
//system('php -f update-data.php');

//chdir('~/ISO3166-Library');

echo "Creating initial file structure...\n";

if (!file_exists('data')) {
    mkdir('data');
}

$country_codes = file_get_contents('vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/data/regions/en.json');
$decoded = json_decode($country_codes, true);

// create initial file structure - using default en
foreach ($decoded["Names"] as $key => $value) {
    $filename = strtolower($key).'.json';
    $path = 'data/'.$filename;

    $obj = ['alpha-2' => $key,
        'alpha-3' => null,
        'numeric' => null,
        'names' => ['en' => $value],
        '3166-2' => []
    ];

    file_put_contents($path, json_encode($obj, JSON_PRETTY_PRINT));
}

echo "Adding languages to country names...\n";

// add languages
// could remove duplicates, i.e. if name is the same as en it can be removed and fallback to default (en)
$intl = 'vendor/symfony/symfony/src/Symfony/Component/Intl/Resources/data/regions';
$dir = new DirectoryIterator($intl);
foreach ($dir as $fileinfo) {
    if ($fileinfo->isDot()) {
        continue;
    }

    $contents = file_get_contents($intl.'/'.$fileinfo->getFilename());
    $decoded = json_decode($contents, true);

    $language = substr($fileinfo->getFilename(), 0, 2);

    if (!array_key_exists("Names", $decoded)) {
        continue;
    }

    foreach ($decoded["Names"] as $key => $value) {
        $country = strtolower($key);

        if (file_exists('data/'.$country.'.json')) {
            $obj = json_decode(file_get_contents('data/'.$country.'.json'), true);

            $obj["names"][$language] = $value;
            file_put_contents('data/'.$country.'.json', json_encode($obj, JSON_PRETTY_PRINT));
        }
    }
}

echo "Checking ICU data against deb source...\n";

// check ICU data against debian source
$response = $client->request('GET', 'https://salsa.debian.org/api/v4/projects/2957/repository/files/data%2Fiso_3166-1%2Ejson/raw?ref=main', [
    'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]
]);

$body = $response->getBody();
$countries = json_decode($body, true);

foreach ($countries["3166-1"] as $country) {
    $filename = strtolower($country["alpha_2"]).".json";

    if (file_exists('data/'.$filename)) {
        $contents = file_get_contents('data/'.$filename);
        $decoded = json_decode($contents, true);

        $decoded["alpha-3"] = $country["alpha_3"];
        $decoded["numeric"] = $country["numeric"];

        file_put_contents('data/'.$filename, json_encode($decoded, JSON_PRETTY_PRINT));
    } else {
        echo "cant find ".$country["name"]."...\n";
    }
}

echo "Added alpha-3 mapping to countries...\n";
echo "Finished 3166-1...\n";

// add intial 3166-2 data
echo "Starting 3166-2...\n";
$response = $client->request('GET', 'https://salsa.debian.org/api/v4/projects/2957/repository/files/data%2Fiso_3166-2%2Ejson/raw?ref=main', [
    'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]
]);

$body = $response->getBody();
$countries = json_decode($body, true);

foreach ($countries["3166-2"] as $region) {
    $country = strtolower(substr($region["code"], 0, 2));
    $filename = $country.'.json';

    if (file_exists('data/'.$filename)) {
        $contents = file_get_contents('data/'.$filename);
        $decoded = json_decode($contents, true);

        $obj = ["code" => $region["code"], "names" => $region["name"], "type" => $region["type"]];

        $decoded["3166-2"][] = $obj;

        file_put_contents('data/'.$filename, json_encode($decoded, JSON_PRETTY_PRINT));
    }
}

echo "Finished 3166-2...\n";
echo "Build complete\n";




