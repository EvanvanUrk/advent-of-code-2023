<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;

class Day4 implements Solution
{
    public function part1(string $input): string
    {
        $points = array_map(
            function(string $line) {
                $cards = explode('|', explode(':', $line)[1]);
                return (int) floor(pow(
                    2,
                    $this->countWinningCards(
                        $this->readNumbers($cards[0]),
                        $this->readNumbers($cards[1]),
                    ) - 1)
                );
            },
            Util::splitByLines($input)
        );

        return (string) array_sum($points);
    }

    public function part2(string $input): string
    {
        $winAmts = array_map(
            function(string $line) {
                $cards = explode('|', explode(':', $line)[1]);
                return $this->countWinningCards(
                    $this->readNumbers($cards[0]),
                    $this->readNumbers($cards[1]),
                );
            },
            Util::splitByLines($input)
        );

        $cardAmts = array_fill(0, count($winAmts), 1);
        foreach ($winAmts as $idx => $wins) {
            if ($wins > 0) {
                foreach (range($idx + 1, $idx + $wins) as $i) {
                    $cardAmts[$i] += $cardAmts[$idx];
                }
            }
        }

        return (string) array_sum($cardAmts);
    }

    private function countWinningCards(array $winners, array $numbers): int
    {
        $wins = array_filter(
            $numbers,
            fn($number) => in_array($number, $winners)
        );

        return count($wins);
    }

    private function readNumbers(string $numbers): array
    {
        return explode(' ', trim(str_replace('  ', ' ', $numbers)));
    }
}
