<?php
require_once "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

$client = new Client();
$response = $client->request('GET', 'https://api.github.com/repos/alexander-schranz/iso-3166-2/contents/subdivisions',
    [ 'headers' => [ 'Accept' => 'application/vnd.github.v3+json' ]]
);

$body = $response->getBody();
$country_codes = json_decode($body, true);

foreach($country_codes as $country_code) {
    if ($country_code["type"] == "file") {
        $file_name = $country_code["name"];
        $path = './subdivisions/'.$file_name;

        if (!$new_file = fopen($path, "w")) {
            echo "Cannot open file: $file_name";
            continue;
        }

        $contents = file_get_contents($country_code["download_url"]);

        if (fwrite($new_file, $contents) === FALSE) {
            echo "Cannot write to file: $file_name";
            continue;
        }

        fclose($new_file);
        echo "Wrote to file: $file_name\n";
    }
}
