<?php

declare(strict_types=1);

namespace AoC;

class Day2 implements Solution
{
    private ?array $games = null;

    public function part1(string $input): string
    {
        $lines = Util::splitByLines($input);
        $this->games = $this->parseGames($lines);

        $constraints = [
            'red' => 12,
            'green' => 13,
            'blue' => 14,
        ];

        $validGames = array_filter(
            $this->games,
            function(array $rounds) use ($constraints) {
                return count(
                    array_filter(
                        $rounds,
                        function(array $round) use ($constraints) {
                            foreach ($constraints as $color => $limit) {
                                if (isset($round[$color]) && $round[$color] > $limit) {
                                    return true;
                                }
                            }
                            return false;
                        }
                    )
                ) === 0;
            }
        );

        return (string) array_sum(array_keys($validGames));
    }

    public function part2(string $input): string
    {
        return (string) array_sum(
            array_map(
                function(array $game) {
                    return array_product(
                        array_reduce(
                            $game,
                            function(array $acc, array $round) {
                                foreach ($round as $color => $amt) {
                                    if (!isset($acc[$color]) || $amt > $acc[$color]) {
                                        $acc[$color] = $amt;
                                    }
                                }
                                return $acc;
                            },
                            []
                        )
                    );
                },
                $this->games
            )
        );
    }

    private function parseGames(array $games): array
    {
        return array_reduce(
            $games,
            function(array $acc, string $game) {
                return $acc + $this->parseGame($game);
            },
            []
        );
    }

    private function parseGame(string $game): array
    {
        $parts = explode(': ', $game);
        $id = (int) preg_replace('/[^0-9]+/', '', $parts[0]);
        $rounds = array_map(
            function(string $round) { return $this->parseRound($round); },
            explode('; ', $parts[1])
        );
        return [$id => $rounds];
    }

    function parseRound(string $round): array
    {
        return array_reduce(
            explode(', ', $round),
            function(array $acc, string $color) {
                $parts = explode(' ', $color);
                return $acc + [$parts[1] => (int) $parts[0]];
            },
            []
        );
    }
}
