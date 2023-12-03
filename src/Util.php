<?php

namespace AoC;

class Util
{
    public static function getInput(int $day, string $prefix = 'day'): string
    {
        return file_get_contents(__DIR__ . sprintf('/../input/%s%d.txt', $prefix, $day));
    }

    /**
     * @param string $input
     * @return string[]
     */
    public static function splitByLines(string $input): array
    {
        return explode(PHP_EOL, trim($input));
    }

    public static function getTime(): int
    {
        return round(microtime(true) * 1000);
    }
}
