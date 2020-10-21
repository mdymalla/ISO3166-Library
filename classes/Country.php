<?php

Class Country
{
    private string $name;

    private string $alpha_2;

    private string $alpha_3;

    private string $numeric;

    private $subDivisions = [];

    function __construct($name, $alpha_2, $alpha_3, $numeric)
    {
        $this->name = $name;
        $this->alpha_2 = $alpha_2;
        $this->alpha_3 = $alpha_3;
        $this->numeric = $numeric;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function getSubDivisions(): array
    {
        return $this->subDivisions;
    }
}