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

    /**
     * @param string|int|float $start
     * @param string|int|float $end
     * @return array<int, string|int|float>
     */
    public static function range(
        mixed $start,
        mixed $end,
        bool $reverse = false,
        int $step = 1,
    ): array {
        $range = range($start, $end, $step);
        return $reverse ? array_reverse($range) : $range;
    }

    public static function point(int $x, int $y): array
    {
        return ['x' => $x, 'y' => $y];
    }

    public static function cartesianProduct(array $a, array $b): array
    {
        $product = [];
        $added = [];
        foreach ($a as $itemA) {
            foreach ($b as $itemB) {
                if (false === in_array($itemA . '-' . $itemB, $added)
                    && false === in_array($itemB . '-' . $itemA, $added)) {
                    $product[] = [$itemA, $itemB];
                    $added[] = $itemA . '-' . $itemB;
                }
            }
        }

        return $product;
    }
}
