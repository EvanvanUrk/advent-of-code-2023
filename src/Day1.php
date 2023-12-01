<?php

namespace AoC;

class Day1 implements Solution
{
    public function part1(string $input): string
    {
        $lines = Util::splitByLines($input);
        return (string)array_reduce(
            $lines,
            function(int $sum, string $line) {
                $nums = preg_replace('/[^0-9]*/', '', $line);
                if ($nums !== '') {
                    $sum += (int) ($nums[0] . substr($nums, -1));
                }
                return $sum;
            },
            0
        );
    }

    public function part2(string $input): string
    {
        $lines = Util::splitByLines($input);
        return (string)array_reduce(
            $lines,
            function(int $sum, string $line) {
                $terms = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
                $nums = [];
                for ($i = 0; $i < strlen($line); $i += 1) {
                    foreach ($terms as $n => $term) {
                        if (strpos($line, $term, $i) === $i
                            || strpos($line, (string)($n+1), $i) === $i) {
                            $nums[] = $n+1;
                        }
                    }
                }

                $sum += $nums[0] * 10 + end($nums);
                return $sum;
            },
            0
        );
    }
}
