<?php
require_once "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

$client = new Client();

// ISO3166: Initial files with Country and Region codes
$response = $client->request('GET', 'https://api.github.com/repos/esosedi/3166/contents/data', [
    'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]
]);

$body = $response->getBody();
$data = json_decode($body, true);

$countries = json_decode(file_get_contents($data[0]["download_url"]), true);

foreach ($countries as $country) {
    $country_code = strtolower($country["iso"]);
    $file_name = $country_code.'.json';
    $path = 'data/'.$file_name;

    $country_object = ['alpha-2' => $country["iso"],
        'alpha-3' => $country["iso3"],
        'numeric' => $country["numeric"],
        'capital' => null,
        'currency' => null,
        'languages' => null,
        'flag-icon' => null,
        'names' => $country["names"],
        'ISO3166-2' => null
    ];

    foreach ($country["regions"] as $region) {
        $country_object['ISO3166-2'][$region['iso']] = ['name' => $region["name"],
            'code' => $region["iso"],
            'names' => $region["names"],
            'division' => null,
            'parent' => null,
            'lat' => null,
            'lng' => null
        ];
    }

    file_put_contents($path, json_encode($country_object, JSON_PRETTY_PRINT));
}
echo "ISO3166: Initial files created with Country and Region data\n\n";

// ISO3166-2: Additional region data (division, lat, lng)
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
    $country_code = substr($code["code"], 0, 2);
    $region_code = substr($code["code"], 3);

    $file_name = strtolower($country_code);
    $path = './data/'.$file_name.'.json';

    if (file_exists($path) && false !== strpos($code["code"], "-")) {
        $existing_contents = file_get_contents($path);
        $decoded = json_decode($existing_contents, true);

        if (!isset($decoded['ISO3166-2'][$region_code]['name'])) {
            $decoded['ISO3166-2'][$region_code]['name'] = $code['name'];
            $decoded['ISO3166-2'][$region_code]['code'] = $region_code;
        }

        $decoded['ISO3166-2'][$region_code]['division'] = $code['division'];
        $decoded['ISO3166-2'][$region_code]['parent'] = $code['parent'];

        if (isset($code['lat']) && isset($code['lng'])) {
            $decoded['ISO3166-2'][$region_code]['lat'] = $code['lat'];
            $decoded['ISO3166-2'][$region_code]['lng'] = $code['lng'];
        }

        // sort ISO3166-2 array - will cut search time
        // what if code is numeric?
        if (is_array($decoded['ISO3166-2'])) {
            ksort($decoded['ISO3166-2']);
        }
        file_put_contents($path, json_encode($decoded, JSON_PRETTY_PRINT));
    }
}
echo "ISO3166-2: Division, lat, and lng data added\n\n";

// ISO3166-1: Additional Country data (capital, currency, languages)
$response = $client->request('GET', 'https://api.github.com/repos/annexare/Countries/contents/data/countries.json', [
    'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]
]);

$body = $response->getBody();
$data = json_decode($body, true);
$countries = json_decode(file_get_contents($data["download_url"]), true);

foreach ($countries as $key => $value) {
    $country_code = strtolower($key);
    $path = './data/'.$country_code.'.json';

    if (file_exists($path)) {
        $existing_contents = file_get_contents($path);
        $decoded = json_decode($existing_contents, true);

        $decoded['capital'] = $value['capital'];
        $decoded['currency'] = $value['currency'];
        $decoded['languages'] = $value['languages'];

        file_put_contents($path, json_encode($decoded, JSON_PRETTY_PRINT));
    }
}
echo "ISO3166-1: Country capital, currencies, and languages data added\n\n";

// ISO3166-1: Country Flags
$response = $client->request('GET', 'https://api.github.com/repos/annexare/Countries/contents/dist/countries.emoji.json', [
    'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]
]);

$body = $response->getBody();
$data = json_decode($body, true);
$countries = json_decode(file_get_contents($data["download_url"]), true);

foreach ($countries as $key => $value) {
    $country_code = strtolower($key);
    $path = './data/'.$country_code.'.json';

    if (file_exists($path)) {
        $existing_contents = file_get_contents($path);
        $decoded = json_decode($existing_contents, true);

        $decoded['flag-icon'] = $value['emoji'];
        file_put_contents($path, json_encode($decoded, JSON_PRETTY_PRINT));
    }
}
echo "ISO3166-1: Country flags added\n\n";

// ISO3166-2: Extra source - check if anything is different or missing
// Source should fix a few old codes
$response = $client->request('GET', 'https://api.github.com/repos/sokil/php-isocodes/contents/databases/iso_3166-2.json', [
    'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]
]);

$body = $response->getBody();
$data = json_decode($body, true);
$countries = json_decode(file_get_contents($data["download_url"]), true);

foreach ($countries as $country) {
    foreach ($country as $region) {
        $country_code = substr($region["code"], 0, 2);
        $region_code = substr($region["code"], 3);

        $file_name = strtolower($country_code);
        $path = './data/'.$file_name.'.json';

        if (file_exists($path)) {
            $existing_contents = file_get_contents($path);
            $decoded = json_decode($existing_contents, true);

            // Check if any code does not exist in data already
            if (!array_key_exists($region_code, $decoded['ISO3166-2'])) {
                foreach ($decoded['ISO3166-2'] as $key => $value) {
                    // If name matches and existing name it must have an different/outdated code
                    // Change deprecated code to new one and replace old key
                    if (in_array($region['name'], $value)) {
                        echo "*\n";
                        echo "** Changing region code on ".$value['name'];
                        echo " - from ".$value['code']." to ".$region_code."\n";

                        $decoded['ISO3166-2'][$value['code']]['code'] = $region_code;
                        $decoded['ISO3166-2'][$region_code] = $decoded['ISO3166-2'][$value['code']];
                        unset($decoded['ISO3166-2'][$key]);

                        if (is_array($decoded['ISO3166-2'])) {
                            ksort($decoded['ISO3166-2']);
                        }

                        file_put_contents($path, json_encode($decoded, JSON_PRETTY_PRINT));
                    } else {
                        print_r($region);
                    }
                }
            }
        }
    }
}

// Number of countries check
//$official_count = 249;
//$path = './data/';
//$files = count(glob($path.'*'));
//echo "Official ISO3166-1 Country code count: ".$official_count."\n";
//echo "Count of files by Country code: ".$files."\n";
//if ($official_count !== $files) {
//    $missing = $official_count - $files;
//    echo "Missing Countries: ".$missing."\n\n";
//} else {
//    echo "Data contains all official countries\n\n";
//}