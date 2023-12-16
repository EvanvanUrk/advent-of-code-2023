<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use Illuminate\Support\Collection;

class Day15 implements Solution
{
    private Collection $steps;

    public function __construct(string $input)
    {
        $steps = collect(explode(',', $input));
        $this->steps = $steps->map(fn(string $val) => trim($val));
    }

    public function part1(string $input): string
    {
        $values = $this->steps->map(fn(string $step) => $this->hash($step));
        return (string) $values->sum();
    }

    public function part2(string $input): string
    {
        $boxes = array_fill(0, 256, []);
        foreach ($this->steps as $step) {
            $label = preg_replace('/[^a-z]/', '', $step);
            $instruction = preg_replace('/[a-z]/', '', $step);
            $hash = $this->hash($label);
            if ($instruction[0] === '=') {
                $boxes[$hash][$label] = $instruction[1];
            } elseif ($instruction[0] === '-') {
                unset($boxes[$hash][$label]);
            }
        }

        $sum = 0;
        foreach ($boxes as $boxNr => $box) {
            $i = 1;
            foreach ($box as $lens) {
                $sum += ($boxNr + 1) * $i * $lens;
                $i += 1;
            }
        }

        return (string) $sum;
    }

    private function hash(string $value): int
    {
        return array_reduce(
            str_split($value),
            function(int $carry, string $char) {
                $carry += ord($char);
                $carry *= 17;
                $carry %= 256;
                return $carry;
            },
            0
        );
    }
}
