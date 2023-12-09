<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use Illuminate\Support\Collection;

class Day9 implements Solution
{
    public function part1(string $input): string
    {
        $lines = collect(Util::splitByLines($input));
        $readings = $lines->map(fn(string $line) => explode(' ', $line));
        $extrapolated = $this
            ->extrapolate($readings)
            ->map(fn (array $readings) => $readings[array_key_last($readings)])
        ;

        return (string) $extrapolated->sum();
    }

    public function part2(string $input): string
    {
        $lines = collect(Util::splitByLines($input));
        $readings = $lines->map(fn(string $line) => array_reverse(explode(' ', $line)));
        $extrapolated = $this
            ->extrapolate($readings)
            ->map(fn (array $readings) => $readings[array_key_last($readings)])
        ;

        return (string) $extrapolated->sum();
    }

    /**
     * @param array<int> $values
     * @return array<int>
     */
    private function numDiffs(array $values): array
    {
        $diffs = [];
        foreach ($values as $i => $value) {
            $next = $values[$i + 1] ?? null;
            if (null === $next) { break; }
            $diffs[] = $next - $value;
        }

        return $diffs;
    }

    private function extrapolate(Collection $readings): Collection
    {
        return $readings->map(function (array $currentTrend) {
            $trends = [$currentTrend];
            do {
                $trends[] = $currentTrend = $this->numDiffs($currentTrend);
            } while (array_sum($currentTrend) !== 0);
            array_pop($trends);

            while (count($trends) > 1) {
                $popped = array_pop($trends);
                $last = array_key_last($trends);
                $trends[$last][] = end($trends[$last]) + array_pop($popped);
            }

            return $trends[0];
        });
    }
}
