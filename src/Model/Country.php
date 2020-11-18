<?php

namespace MJDymalla\ISO3166Data\Model;

use MJDymalla\ISO3166Data\Model\SubDivision;
use MJDymalla\ISO3166Data\Model\Translation;

class Country
{
    private string $alpha_2;
    private string $alpha_3;
    private string $numeric;
    private array $languages;
    private array $translations;
    private array $subdivisions;

    function __construct($alpha_2, $alpha_3, $numeric, $languages, $translations, $subdivisions)
    {
        $this->alpha_2 = $alpha_2;
        $this->alpha_3 = $alpha_3;
        $this->numeric = $numeric;
        $this->languages = $languages;
        $this->translations = $translations;
        $this->subdivisions = $subdivisions;
    }

    public function getAlpha_2(): string
    {
        return $this->alpha_2;
    }

    public function getAlpha_3(): string
    {
        return $this->alpha_3;
    }

    public function getNumeric(): string
    {
        return $this->numeric;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getSubdivisions(): array
    {
        return $this->subdivisions;
    }

    public function setAlpha2(string $alpha_2)
    {
        $this->alpha_2 = $alpha_2;

        return $this;
    }

    public function setAlpha3(string $alpha_3)
    {
        $this->alpha_3 = $alpha_3;

        return $this;
    }

    public function setNumeric(string $numeric)
    {
        $this->numeric = $numeric;

        return $this;
    }

    public function setLanguages(array $languages)
    {
        $this->languages = $languages;

        return $this;
    }

    public function setTranslations(Translation $translation)
    {
        $this->translations[] = $translation;

        return $this;
    }

    public function setSubdivisions(array $subdivisions)
    {
        $this->subdivisions = $subdivisions;

        return $this;
    }
}