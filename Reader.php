<?php

class Reader
{
    public static function read(string $path)
    {
        $json = @file_get_contents(__DIR__.'/'.$path);
        $data = json_decode($json, true);

        if (null === $data) {
            return null;
        }

        return $data;
    }
}