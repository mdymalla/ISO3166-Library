<?php

namespace MJDymalla\ISO3166Data\Normalizer;

use MJDymalla\ISO3166Data\Model\Country;


class CountryMetaData
{
    public static function normalize(Country $country): array
    {
        return array(
            "alpha_2" => $country->getAlpha_2(),
            "alpha_3" => $country->getAlpha_3(),
            "numeric" => $country->getNumeric()
        );
    }

    public static function denormalize(array $country): Country
    {
        return new Country(
            $country["alpha_2"],
            $country["alpha_3"],
            $country["numeric"],
            [],
            [],
            []
        );
    }
}