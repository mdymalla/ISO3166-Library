<?php

namespace MJDymalla\ISO3166Data\Normalizer;

use MJDymalla\ISO3166Data\Model\Country;

class CountryMetaData
{
    /**
     * Normalizes a Country object into an associative array of meta data
     * @param Country $country  Country object to be normalized
     *
     * @return array
     */
    public static function normalize(Country $country): array
    {
        return array(
            "alpha_2" => $country->getAlpha_2(),
            "alpha_3" => $country->getAlpha_3(),
            "numeric" => $country->getNumeric()
        );
    }

    /**
     * Denormalizes a country meta data array into a Country object
     * @param array $country  Country array to be denormalized
     *
     * @return Country
     */
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