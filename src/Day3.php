<?php

declare(strict_types=1);

namespace AoC;

use AoC\Util\Map2D;

class Day3 implements Solution
{
    private Map2D $map;

    public function __construct(string $input) {
        $this->map = Map2D::fromInput($input);
    }

    public function part1(string $input): string
    {
        $numbers = [];
        $currentNumber = '';
        $this->map->walk(
            function(int $x, int $y, ?string $val)
                use (&$numbers, &$currentNumber) {
                if (is_numeric($val)) { $currentNumber .= $val; }

                $next = $this->map->get($x + 1, $y);
                if (!is_numeric($next)) {
                    if ($currentNumber === '') { return; }
                    $hasAdjacentSymbol = $this->map->findInRegion(
                        $x - strlen($currentNumber),
                        $x + 1,
                        $y - 1,
                        $y + 1,
                        '/[^0-9.]/',
                        true
                    ) !== null;

                    if ($hasAdjacentSymbol) { $numbers[] = $currentNumber; }

                    $currentNumber = '';
                }
            }
        );

        return (string) array_sum($numbers);
    }

    public function part2(string $input): string
    {
        $ratios = [];
        $currentNumber = '';

        $this->map->walk(
            function(int $x, int $y, ?string $val)
                use (&$ratios, &$currentNumber) {
                if (is_numeric($val)) { $currentNumber .= $val; }

                $next = $this->map->get($x + 1, $y);
                if (!is_numeric($next)) {
                    if ($currentNumber === '') { return; }
                    $pos = $this->map->findInRegion(
                        $x - strlen($currentNumber),
                        $x + 1,
                        $y - 1,
                        $y + 1,
                        '*'
                    );

                    if ($pos !== null) {
                        $ratioKey = $pos['x'] . '-' . $pos['y'];
                        if (!isset($ratios[$ratioKey])) { $ratios[$ratioKey] = []; }
                        $ratios[$ratioKey][] = (int) $currentNumber;
                    }

                    $currentNumber = '';
                }
            }
        );

        return (string) array_sum(
            array_map(
                function(array $idx) {
                    return array_product($idx);
                },
                array_filter(
                    $ratios,
                    function(array $numbers) {
                        return count($numbers) === 2;
                    }
                )
            )
        );
    }
}
