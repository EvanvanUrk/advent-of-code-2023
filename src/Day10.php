<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use AoC\Util\Map2D;

class Day10 implements Solution
{
    private Map2D $map;
    private array $start;

    private array $moves;
    private array $route;

    public function __construct(string $input)
    {
        $this->moves = [
            'S' => [Util::point(1, 0), Util::point(-1, 0), Util::point(0, 1), Util::point(0, -1)],
            '-' => [Util::point( 1, 0), Util::point(-1,  0)],
            '|' => [Util::point( 0, 1), Util::point( 0, -1)],
            'F' => [Util::point( 1, 0), Util::point( 0,  1)],
            '7' => [Util::point(-1, 0), Util::point( 0,  1)],
            'L' => [Util::point( 1, 0), Util::point( 0, -1)],
            'J' => [Util::point(-1, 0), Util::point( 0, -1)]
        ];
        $this->map = new Map2D($input);
        $this->start = $this->map->find('S');
        $this->route = $this->findRoute();
    }

    public function part1(string $input): string
    {
        return (string) floor(count($this->route) / 2);
    }

    public function part2(string $input): string
    {
        // For each position in the route store whether the pos was entered or
        // left upwards or downwards. It's impossible for a position to be
        // entered upwards and left downwards or vice versa.
        //
        // Walk from left to right counting between DOWN and UP or UP and DOWN,
        // depending on which one you encounter first.
        //
        // Keep walking until the first downward part of the route is found.
        // Walk through upwards parts of the map but don't count them.
        //
        // I.e. 1 v ..F--7 ^
        //      2 v F-J..| ^
        //      2 v L----J ^
        // 1: Start reading at F and stop at 7, not counting any part
        // 2: Start reading at F and stop at |, but don't count the "-J"
        // section of pipe.
        // 3: Start reading at L and stop at J, not counting any part

        return '';
    }

    private function findRoute(): array
    {
        $currentVal = 'S';
        $route = [];
        $route[] = $current = $this->start;
        do {
            $this->map->walkRegion(
                $current['x'] - 1,
                $current['x'] + 1,
                $current['y'] - 1,
                $current['y'] + 1,
                function(int $x, int $y, ?string $value) use (&$route, &$current, &$currentVal) {
                    // skip empty spaces and diagonal moves
                    if ($value === '.' || $value === null) { return false; }
                    if ($x !== $current['x'] && $y !== $current['y']) { return false; }

                    // skip if checked pos is previous in route
                    if (count($route) > 1) {
                        $last = $route[array_key_last($route) - 1];
                        if ($x === $last['x'] && $y === $last['y']) { return false; }
                    }

                    // skip if check x,y is not in valid moves for current value
                    $validMoves = $this->moves[$currentVal];
                    $move = Util::point($x - $current['x'], $y - $current['y']);
                    if (false === in_array($move, $validMoves)) { return false; }

                    // skip if current position is not in valid moves for next value
                    $validMovesBack = $this->moves[$value];
                    $moveBack = Util::point($current['x'] - $x, $current['y'] - $y);
                    if (false === in_array($moveBack, $validMovesBack)) { return false; }

                    // valid move found
                    $route[] = $current = Util::point($x, $y);
                    $currentVal = $value;
                    return true;
                }
            );
        } while ($current !== $this->start);

        return $route;
    }
}
