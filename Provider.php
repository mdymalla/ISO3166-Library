<?php

interface Provider
{
    /**
     * Lookup ISO3166 data by name identifier
     */
    public function name($name);

    /**
     * Lookup ISO3166 data by alpha2 identifier
     */
    public function alpha2($alpha2);

    /**
     * Lookup ISO3166 data by alpha3 identifier
     */
    public function alpha3($alpha3);

    /**
     * Lookup ISO3166 data by numeric identifier
     */
    public function numeric($numeric);
}