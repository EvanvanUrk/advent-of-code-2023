<?php

declare(strict_types=1);

namespace AoC;

use AoC\Util\Map3D;

class Day22 implements Solution
{
    public function __construct(string $input)
    {
        $map = new Map3D(3, 3, 10);
        dump($map);
        echo $map . PHP_EOL;
    }

    public function part1(string $input): string
    {
        return '';
    }

    public function part2(string $input): string
    {
        return '';
    }
}
