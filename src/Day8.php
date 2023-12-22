<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use Illuminate\Support\Collection;

class Day8 implements Solution
{
    private Collection $instructions;
    private array $map;

    public function __construct(string $input)
    {
        $parts = explode(PHP_EOL . PHP_EOL, trim($input));
        $this->instructions = collect(mb_str_split($parts[0]));
        $this->map = collect(Util::splitByLines($parts[1]))
            ->reduce(function(array $carry, string $line) {
                $lineParts = explode(' = ', $line);
                $lr = explode(', ', substr($lineParts[1], 1, -1));
                return array_merge($carry, [$lineParts[0] => $lr]);
            }, [])
        ;
    }

    public function part1(string $input): string
    {
        return (string) $this->countSteps('AAA', 'ZZZ');
    }

    public function part2(string $input): string
    {
        $keys = collect(array_keys($this->map));
        $locations = $keys->filter(fn (string $location) => substr($location, -1) === 'A');
        $stepCounts = $locations->map(fn (string $location) => $this->countSteps($location, 'Z'));
        $factors = $stepCounts->map(fn (int $count) => $this->primeFactors($count));
        return (string) array_product($factors->flatten()->unique()->toArray());
    }

    private function countSteps(string $from, string $to): int
    {
        $location = $from;
        $i = 0;
        while (substr($location, -strlen($to)) !== $to) {
            $this->instructions->each(function(string $direction) use (&$location, &$i) {
                $next = $this->map[$location];
                $location = $direction === 'L' ? $next[0] : $next[1];
                $i += 1;
            });
        }
        return $i;
    }

    /**
     * @return array<int>
     */
    private function primeFactors(int $number): array
    {
        $factors = [];
        $sqrt = (int) ceil(sqrt((float) $number)) + 1;
        if ($sqrt - 3 < 2) {
            $ns = [3];
        } else {
            $ns = range(3, $sqrt, 2);
        }

        array_unshift($ns, 2);
        foreach ($ns as $n) {
            while ($number % $n === 0) {
                $number /= $n;
                $factors[] = $n;
            }
        }

        if ($number > 2) { $factors[] = $number; }

        return $factors;
    }
}
