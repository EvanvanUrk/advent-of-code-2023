<?php

declare(strict_types=1);

namespace AoC;

use AoC\Util\Map2D;

class Day11 implements Solution
{
    private Map2D $map;

    public function part1(string $input): string
    {
        $this->map = new Map2D($input);

        $x = 0;
        while ($x < $this->map->getW()) {
            $col = $this->map->getCol($x);
            $col = array_filter($col, fn(string $val) => $val !== '.');
            if (count($col) === 0) {
                $this->map->insertCol($x, array_fill(0, $this->map->getH(), '.'));
                $x += 1;
            }
            $x += 1;
        }

        $y = 0;
        while ($y < $this->map->getH()) {
            $row = $this->map->getRow($y);
            $row = array_filter($row, fn(string $val) => $val !== '.');
            if (count($row) === 0) {
                $this->map->insertRow($y, array_fill(9, $this->map->getW(), '.'));
                $y += 1;
            }
            $y += 1;
        }

        return (string) $this->shortestPathsSum();
    }

    public function part2(string $input): string
    {
        $this->map = new Map2D($input);

        $emptyCols = [];
        foreach (range(0, $this->map->getW() - 1) as $x) {
            $col = $this->map->getCol($x);
            $col = array_filter($col, fn(string $val) => $val !== '.');
            if (count($col) === 0) {
                $emptyCols[] = $x;
            }
        }

        $emptyRows = [];
        foreach (range(0, $this->map->getH() - 1) as $y) {
            $row = $this->map->getRow($y);
            $row = array_filter($row, fn(string $val) => $val !== '.');
            if (count($row) === 0) {
                $emptyRows[] = $y;
            }
        }

        return (string) $this->shortestPathsSum($emptyCols, $emptyRows, 1000000);
    }

    private function shortestPathsSum(array $emptyCols = [], array $emptyRows = [], int $gapSize = 1): int
    {
        $galaxies = $this->map->findAll('#')['#'];
        $idxs = array_keys($galaxies);

        $sum = 0;
        $idxsAdded = [];
        foreach ($idxs as $idxA) {
            foreach ($idxs as $idxB) {
                if ($idxA === $idxB) { continue; }
                if (isset($idxsAdded[$idxA . '-' . $idxB])
                    || isset($idxsAdded[$idxB . '-' . $idxA])) {
                    continue;
                }

                $galaxyA = $galaxies[$idxA];
                $galaxyB = $galaxies[$idxB];

                $xMin = min($galaxyA['x'], $galaxyB['x']);
                $xMax = max($galaxyA['x'], $galaxyB['x']);
                $yMin = min($galaxyA['y'], $galaxyB['y']);
                $yMax = max($galaxyA['y'], $galaxyB['y']);

                $gapsX = array_filter(
                    $emptyCols,
                    fn(int $x) => $x > $xMin && $x < $xMax
                );

                $gapsY = array_filter(
                    $emptyRows,
                    fn(int $y) => $y > $yMin && $y < $yMax
                );

                $sum += abs($galaxyB['x'] - $galaxyA['x']) + count($gapsX) * ($gapSize - 1);
                $sum += abs($galaxyB['y'] - $galaxyA['y']) + count($gapsY) * ($gapSize - 1);

                $idxsAdded[$idxA . '-' . $idxB] = true;
            }
        }

        return $sum;
    }
}
