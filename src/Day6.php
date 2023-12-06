<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;

class Day6 implements Solution
{
    public function part1(string $input): string
    {
        return (string) array_product(
            array_map(
                fn(int $time, int $record) => count(
                    array_filter(
                        array_map(
                            fn(int $i) => ($time - $i) * $i,
                            range(1, $time - 1)
                        ),
                        fn(int $distance) => $distance > $record
                    )
                ),
                ...array_map(
                    function(string $line) {
                        preg_match_all('/\d+/', $line, $matches);
                        return array_map(fn (string $val) => (int) $val, $matches[0]);
                    },
                    Util::splitByLines($input)
                )
            )
        );
    }

    public function part2(string $input): string
    {
        [$time, $record] = array_map(
            null,
            ...array_map(
                function(string $line) {
                    preg_match_all('/\d+/', str_replace(' ', '', $line), $matches);
                    return array_map(fn (string $val) => (int) $val, $matches[0]);
                },
                Util::splitByLines($input)
            )
        )[0];

        $start = 0;
        $distance = 0;
        while ($distance < $record) {
            $start += 1;
            $distance = ($time - $start) * $start;
        }

        $end = $time;
        $distance = 0;
        while ($distance < $record) {
            $end -= 1;
            $distance = ($time - $end) * $end;
        }

        return (string) ($end - $start + 1);
    }
}
