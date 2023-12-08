<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use Illuminate\Support\Collection;

class Day5 implements Solution
{
    private readonly Collection $seedsToPlant;
    private readonly Collection $maps;

    public function __construct(string $input)
    {
        $parts = explode(PHP_EOL . PHP_EOL, trim($input));
        $this->seedsToPlant = collect(explode(' ', explode(': ', array_shift($parts))[1]));

        $this->maps = collect($parts)
            ->map(fn(string $part) => explode('map:' . PHP_EOL, $part)[1])
            ->map(fn(string $lines) => Util::splitByLines($lines))
            ->map(fn(array $ranges) => new MappedRanges(array_map(
                function(string $range) {
                    $nums = explode(' ', $range);
                    return new MappedRange(
                        (int) $nums[1],
                        (int) $nums[0],
                        (int) $nums[2]
                    );
                },
                $ranges
            )))
        ;
    }

    public function part1(string $input): string
    {
        $closestSeed = $this->seedsToPlant
            ->map(function(string $seed) {
                foreach ($this->maps as $map) {
                    $seed = $map->get((int) $seed);
                }
                return $seed;
            })
            ->min()
        ;

        return (string) $closestSeed;
    }

    public function part2(string $input): string
    {
        $isInSeeds = function(int $seed): bool {
            $ranges = $this->seedsToPlant
                ->chunk(2)
                ->map(fn(Collection $collection) => $collection->values())
            ;

            foreach ($ranges as $range) {
                if ($seed >= $range[0] && $seed < $range[0] + $range[1]) {
                    return true;
                }
            }

            return false;
        };

        $loopWithStep = function(int $i, int $step = 1) use ($isInSeeds): int {
            while (true) {
                $seed = $i;
                foreach ($this->maps->reverse() as $map) {
                    $seed = $map->get($seed, true);
                }
                if ($isInSeeds($seed)) { return $i; }

                $i += $step;
            }
        };

        $i = $loopWithStep(0, 10000);
        $i = $loopWithStep($i - 10000, 1);

        return (string) $i;
    }
}

class MappedRanges
{
    /**
     * @param array<MappedRange> $ranges
     */
    public function __construct(
        private readonly array $ranges
    ) { }

    public function get(int $num, bool $reverse = false): int
    {
        foreach ($this->ranges as $range) {
            $mapped = $reverse ? $range->getReverse($num) : $range->get($num);
            if (null !== $mapped) {
                return $mapped;
            }
        }
        return $num;
    }
}

class MappedRange
{
    private readonly int $srcTo;
    private readonly int $destTo;

    public function __construct(
        private readonly int $srcFrom,
        private readonly int $destFrom,
        int $len
    ) {
        $this->srcTo = $srcFrom + $len;
        $this->destTo = $destFrom + $len;
    }

    function get(int $num): ?int
    {
        if ($num >= $this->srcFrom && $num < $this->srcTo) {
            return $this->destFrom + ($num - $this->srcFrom);
        }
        return null;
    }

    function getReverse(int $num): ?int
    {
        if ($num >= $this->destFrom && $num < $this->destTo) {
            return $this->srcFrom + ($num - $this->destFrom);
        }
        return null;
    }
}
