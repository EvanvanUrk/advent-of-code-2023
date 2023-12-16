<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use AoC\Util\Map2D;

class Day14 implements Solution
{
    private Map2D $map;

    public function __construct(string $input)
    {
        $this->map = Map2D::fromInput($input);
    }

    public function part1(string $input): string
    {
        $this->roll(0, -1);

        return (string) $this->calculateLoad((string) $this->map);
    }

    public function part2(string $input): string
    {
        // Not strictly necessary to parse the input again, but keeps the
        // actual input as the first step in our array, should it matter
        $this->map = Map2D::fromInput($input);
        $totalSteps = 1000000000;

        $loopOffset = null;
        $loopSize = null;
        $steps = [(string) $this->map => 0];
        for ($i = 1; $i <= $totalSteps; $i += 1) {
            $this->spinCycle();
            $strMap = (string) $this->map;
            if (isset($steps[$strMap])) {
                $loopOffset = $steps[$strMap];
                $loopSize = $i - $steps[$strMap];
                break;
            }
            $steps[$strMap] = $i;
        }


        if (null !== $loopSize && null !== $loopOffset) {
            $stepsFlipped = array_flip($steps);
            $offset = ($totalSteps - $loopOffset) % $loopSize;
            $endMap = $stepsFlipped[$loopOffset + $offset];
        }

        return (string) $this->calculateLoad($endMap);
    }

    private function spinCycle(): void
    {
        $this->rollNorth();
        $this->rollWest();
        $this->rollSouth();
        $this->rollEast();
    }

    private function rollNorth(): void { $this->roll(0, -1); }
    private function rollEast(): void { $this->roll(1, 0); }
    private function rollSouth(): void { $this->roll(0, 1); }
    private function rollWest(): void { $this->roll(-1, 0); }

    private function roll(int $stepX, int $stepY): void
    {
        $this->map->walk(
            function($x, $y, $value) use ($stepX, $stepY) {
                if ($value === 'O') {
                    $offsetX = 0;
                    $offsetY = 0;
                    while ($this->map->get($x + $offsetX + $stepX, $y + $offsetY + $stepY) === '.') {
                        $offsetX += $stepX;
                        $offsetY += $stepY;
                    }

                    if ($offsetX !== 0 || $offsetY !== 0) {
                        $this->map->set($x, $y, '.');
                        $this->map->set($x + $offsetX, $y + $offsetY, 'O');
                    }
                }
            },
            $stepX > 0,
            $stepY > 0
        );
    }

    private function calculateLoad(string $map): int
    {
        $sum = 0;
        $lines = Util::splitByLines($map);
        $count = count($lines);
        foreach ($lines as $y => $line) {
            $sum += substr_count($line, 'O') * ($count - $y);
        }

        return $sum;
    }
}
