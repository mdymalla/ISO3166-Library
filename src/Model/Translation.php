<?php

namespace MJDymalla\ISO3166Data\Model;

class Translation
{
    private string $code;
    private string $locale;
    private string $translation;

    function __construct(string $code, string $locale, string $translation)
    {
        $this->code = $code;
        $this->locale = $locale;
        $this->translation = $translation;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTranslation(): string
    {
        return $this->translation;
    }
}