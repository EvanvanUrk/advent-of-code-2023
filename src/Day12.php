<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;

class Day12 implements Solution
{
    public function part1(string $input): string
    {
        $count = 0;
        foreach (Util::splitByLines($input) as $line) {
            $parts = explode(' ', $line);
            $lengths = array_map(
                fn(string $num) => (int) $num,
                explode(',', $parts[1])
            );

            $count += count($this->bruteForce($parts[0], $lengths));
        }

        return (string) $count;
    }

    public function part2(string $input): string
    {
        $lines = Util::splitByLines($input);
        foreach ($lines as $line) {
            $parts = explode(' ', $line);
        }

        // For each record find the lengths that are already in place. Split
        // the remaining substrings with the required lengths and recursively
        // brute force those. Cache the results by function args.

        return '';
    }

    private function bruteForce(string $record, array $lengths, array $valid = []): array
    {
        $pos = strpos($record, '?');
        if (false === $pos) {
            $contig = [];
            $cur = 0;
            foreach (str_split($record) as $c) {
                if ($c === '#') {
                    $cur += 1;
                } elseif ($cur > 0) {
                    $contig[] = $cur; $cur = 0;
                }
            }
            if ($cur > 0) { $contig[] = $cur; }

            if ($contig === $lengths) { return [$record]; }
            return [];
        }

        $recordB = $record;
        $record[$pos] = '#';
        $recordB[$pos] = '.';

        $valid = array_merge($valid, $this->bruteForce($record, $lengths));
        return array_merge($valid, $this->bruteForce($recordB, $lengths));
    }
}
