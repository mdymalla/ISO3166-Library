<?php
require_once "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

$client = new Client(['auth' => ['MJDymalla']]);

// ISO3166-1 Create initial language based directories (ICU source)
// missing heaps of codes?
$response = $client->request('GET', 'https://api.github.com/repos/unicode-org/icu/contents/icu4c/source/data/region', [
    'headers' => ['Accept' => 'application/vnd.github.v3+json'],
]);

$body = $response->getBody();
$country_codes = json_decode($body, true);

foreach ($country_codes as $country_code) {
    $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $country_code["name"]);
    $path = './ISO3166/'.$name;
    mkdir($path);
}
echo "ISO3166-1 ICU Directories made\n\n";

// ISO3166-1 Create files for each directory containing language and country specific data
// will check if directory exists based on ICU data
// (need to change how this works - will send 300+ requests)
$response = $client->request('GET', 'https://api.github.com/repos/umpirsky/country-list/contents/data', [
    'headers' => ['Accept' => 'application/vnd.github.v3+json'],
]);

$body = $response->getBody();
$country_codes = json_decode($body, true);


foreach ($country_codes as $country_code) {

    $res = $client->request('GET', $country_code["url"], [
        'headers' => ['Accept' => 'application/vnd.github.v3+json']
    ]);

    $bod = $res->getBody();
    $codes = json_decode($bod, true);

    foreach ($codes as $code) {

        if ($code["name"] == "country.json") {
            $countries = json_decode(file_get_contents($code["download_url"]), true);
            $path = 'ISO3166/'.$country_code["name"];

            if (!file_exists($path)) {
                echo "Directory doesn't exist: ".$country_code["name"]."\n";
                continue;
            }

            foreach ($countries as $key => $value) {
                $file_name = strtolower($key);

                $country = ['name' => $value,
                    'alpha-2' => $key,
                    'alpha-3' => null,
                    'country-code' => null,
                    'region' => null,
                    'ISO3166-2' => []
                ];

                file_put_contents($path.'/'.$file_name.'.json', json_encode($country, JSON_PRETTY_PRINT));
            }
        }
    }
}
echo "ISO3166-1 Files made with country objects\n\n";

//ISO3166-1 country codes (name, alpha-2, alpha-3 code, region)
$response = $client->request('GET', 'https://api.github.com/repos/lukes/ISO-3166-Countries-with-Regional-Codes/contents/all/all.json', [
    'headers' => ['Accept' => 'application/vnd.github.v3+json'],
]);

$body = $response->getBody();
$country_codes = json_decode($body, true);

$json = file_get_contents($country_codes["download_url"]);
$countries = json_decode($json, true);

foreach ($countries as $country) {
    foreach(new DirectoryIterator('./ISO3166') as $directory) {
        $name = strtolower($country["alpha-2"]);
        $path = './ISO3166/'.$directory.'/'.$name.'.json';

        if (file_exists($path)) {
            $existing_contents = file_get_contents($path);
            $decoded = json_decode($existing_contents, true);

            $decoded["alpha-3"] = $country["alpha-3"];
            $decoded["region"] = $country["region"];
            $decoded["country-code"] = $country["country-code"];
            file_put_contents($path, json_encode($decoded, JSON_PRETTY_PRINT));
        }
    }
}
echo "ISO3166-1 Extra country data added\n\n";

//// ISO3166-2: Set initial subdivisions
//$response = $client->request('GET', 'https://api.github.com/repos/alexander-schranz/iso-3166-2/contents/subdivisions',
//    [ 'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]]
//);
//
//$body = $response->getBody();
//$country_codes = json_decode($body, true);
//
//foreach($country_codes as $country_code) {
//    if ($country_code["type"] == "file") {
//        $file_name = $country_code["name"];
//        $path = './ISO3166/en_AU/'.$file_name;
//
//        $existing_contents = file_get_contents($path);
//        $decoded = json_decode($existing_contents, true);
//
//        $contents = file_get_contents($country_code["download_url"]);
//        $decode = json_decode($contents, true);
//
//        $decoded["ISO3166-2"] = $decode;
//        file_put_contents($path, json_encode($decoded, JSON_PRETTY_PRINT));
//    }
//}

// ISO3166-2: Source 2
$response = $client->request('GET', 'https://api.github.com/repos/oodavid/iso-3166-2/contents/iso_3166_2.js', [
    'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]
]);

$body = $response->getBody();
$data = json_decode($body, true);

$javascriptLines = file($data["download_url"]);
$json = implode("\n", array_map(function (string $line) {
    if (0 === strpos($line, 'var iso_3166_2 = {')) {
        return '{';
    }
    if (0 === strpos($line, '};')) {
        return '}';
    }
    if (false !== strpos($line, '//')) {
        return '';
    }
    return $line;
}, $javascriptLines));

$codes = json_decode($json, true);

foreach ($codes as $code) {
    // needs substring of country code in lower case alpha-2 to compare to files
    $country_code = substr($code["code"], 0, 2);
    $file_name = strtolower($country_code);

    $path = './ISO3166/en_AU/'.$file_name.'.json';

    if (file_exists($path) && false !== strpos($code["code"], "-")) {
        $existing_contents = file_get_contents($path);
        $decoded = json_decode($existing_contents, true);

        array_push($decoded["ISO3166-2"], $code);
        file_put_contents($path, json_encode($decoded, JSON_PRETTY_PRINT));
    }
}
echo "ISO3166-2 Codes added to objects in en_AU\n\n";




