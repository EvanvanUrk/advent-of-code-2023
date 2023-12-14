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
        $this->map = Map2D::fromInput($input);
        $this->start = $this->map->find('S');
        $this->route = $this->findRoute();
    }

    public function part1(string $input): string
    {
        return (string) floor(count($this->route) / 2);
    }

    public function part2(string $input): string
    {
        $direction = [];

        $routeLen = count($this->route);
        foreach ($this->route as $idx => $cur) {
            $prev = array_slice($this->route, $idx - 1, 1)[0];
            $next = array_slice($this->route, ($idx + 1) % $routeLen, 1)[0];

            $key = $cur['x'] . '-' . $cur['y'];
            if ($prev['y'] > $cur['y'] || $next['y'] < $cur['y']) {
                $direction[$key] = true; // up
            } else if ($prev['y'] < $cur['y'] || $next['y'] > $cur['y']) {
                $direction[$key] = false; // down
            }
        }

        $countStep = false;
        $count = 0;
        $clockwise = null;
        $newMap = $this->map->map(
            function($x, $y, $value) use ($direction, &$countStep, &$count, &$clockwise) {
                $key = $x . '-' . $y;
                if (isset($direction[$key])) {
                    if (null === $clockwise) {
                        $clockwise = $direction[$key];
                    }
                    $countStep = $direction[$key] === $clockwise;
                }

                if ($this->isPosInRoute($x, $y)) {
                    if ($x >= 35 && $x < 105 && $y >= 35 && $y < 105) {
                        return '.';
                    }
                    return ' ';
                }

                if ($countStep) { $count += 1; }

                return $countStep ? 'I': 'O';
            }
        );

//        echo $newMap . PHP_EOL;

        return (string) $count;
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

        array_pop($route);

        return $route;
    }

    private function isPosInRoute(int $x, int $y): bool
    {
        return in_array(Util::point($x, $y), $this->route);
    }
}
