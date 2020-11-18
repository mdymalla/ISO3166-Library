<?php

namespace MJDymalla\ISO3166Data\Model;

use MJDymalla\ISO3166Data\Model\Translation;

Class SubDivision
{
    private string $name;

    private string $code;

    private string $type;

    private string $parent;

    private array $translations;

    function __construct($name, $code, $type, $parent)
    {
        $this->name = $name;
        $this->code = $code;
        $this->type = $type;
        $this->parent = $parent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getParent(): string
    {
        return $this->parent;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function setTranslations(Translation $translation)
    {
        $this->translations[] = $translation;

        return $this;
    }
}