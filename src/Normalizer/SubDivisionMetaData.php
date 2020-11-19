<?php

namespace MJDymalla\ISO3166Data\Normalizer;

use MJDymalla\ISO3166Data\Model\SubDivision;

class SubDivisionMetaData
{
    /**
     * Normalizes a SubDivision object into an associated array of subdivision meta data
     * @param SubDivision $subdivision  subdivision to be normalized
     *
     * @return array
     */
    public static function normalize(SubDivision $subdivision): array
    {
        $normalized = [
            "code" => $subdivision->getCode(),
            "type" =>$subdivision->getType()
        ];

        if (!empty($subdivision->getParent())) {
            $normalized["parent"] = $subdivision->getParent();
        }

        return $normalized;
    }

    /**
     * Denormalizes array of subdivision meta data into SubDivision object
     * @param array $subdivision  meta data array to be denormalized
     *
     * @return SubDivision
     */
    public static function denormalize(array $subdivision): SubDivision
    {
        $parent = array_key_exists("parent", $subdivision) ? $subdivision["parent"] : "";

        return new SubDivision(
            $subdivision["name"],
            $subdivision["code"],
            $subdivision["type"],
            $parent
        );
    }
}