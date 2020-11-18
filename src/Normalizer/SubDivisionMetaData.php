<?php

namespace MJDymalla\ISO3166Data\Normalizer;

use MJDymalla\ISO3166Data\Model\SubDivision;

class SubDivisionMetaData
{
    public static function normalize(SubDivision $subdivision): array
    {
        $normalized = [
            "code" => $subdivision->getCode(),
            "name" => $subdivision->getName(),
            "type" =>$subdivision->getType()
        ];

        if (!empty($subdivision->getParent())) {
            $normalized["parent"] = $subdivision->getParent();
        }

        return $normalized;
    }

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