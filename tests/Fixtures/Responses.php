<?php

namespace Cielo\Tests\Fixtures;

final class Responses
{
    public static function get(string $name): string
    {
        return (string) file_get_contents(__DIR__ . "/responses/{$name}.json");
    }
}
