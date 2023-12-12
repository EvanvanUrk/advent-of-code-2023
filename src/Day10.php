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
        // Set a toggle for counting steps to FALSE.
        //
        // Walk from left to right.
        // Increment counter for every step where the toggle is TRUE.
        //
        // If toggle is TRUE, current is NOT part of route and next step is part of route
        // OR if toggle is FALSE, current is part of route and next step is NOT part of route
        // i.e. when $toggle !== $isCurPartOfRoute && $toggle === $isNextPartOfRoute
        // Set $toggle to !$toggle
        // Set toggle to TRUE when you find a part of the route that is
        // positioned LEFT of a space that IS NOT part of the route.
        // Else set toggle to FALSE when the next position to the right
        // IS part of the route.
        //
        // I.e. 1 ..F--7
        //      2 F-J..|
        //      3 L----J
        // 1: Never toggles because no part of the route is left of a
        //    space not part of the route
        // 2: Start counting after J (left of open space) and stop before |
        //    (right of open space)
        // 3: Start reading at L and stop at J, not counting any part

        $countStep = false;
        $count = 0;
        $newMap = $this->map->map(
            function($x, $y, $value) use (&$countStep, &$count) {
                $next = $this->map->get($x + 1, $y);
                if (null === $next) {
                    $countStep = false;
                }

                $isCurInRoute = $this->isPosInRoute($x, $y);
                $isNextInRoute = $this->isPosInRoute($x + 1, $y);

                $mapped = 'O';
                if ($isCurInRoute) {
                    $mapped = $value;
                }

                if ($countStep) {
                    $mapped = 'I';
                }

                if ($countStep && false === $isCurInRoute) { $count += 1; }
                if (false === $isCurInRoute && true === $isNextInRoute) {
                    $countStep = false;
                } elseif (false === $isCurInRoute && true === $isNextInRoute) {
                    $countStep = true;
                }

                return $mapped;
            }
        );

        echo $newMap . PHP_EOL;

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

        return $route;
    }

    private function isPosInRoute(int $x, int $y): bool
    {
        return in_array(Util::point($x, $y), $this->route);
    }
}
