<?php

Class SubDivision
{
    private string $name;

    private string $code;

    private string $type;

    private int $administrationLevel;

    function __construct($name, $code, $type, $administrationLevel)
    {
        $this->name = $name;
        $this->code = $code;
        $this->type = $type;
        $this->administrationLevel = $administrationLevel;
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

    public function getAdministrationLevel(): int
    {
        return $this->administrationLevel;
    }
}