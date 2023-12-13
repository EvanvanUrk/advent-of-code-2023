<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use AoC\Util\Map2D;

class Day13 implements Solution
{
    private array $maps;

    public function __construct(string $input)
    {
        $this->maps = array_map(
            fn(string $map) => Map2DWithXYMap::fromInput($map),
            explode(PHP_EOL . PHP_EOL, $input)
        );
    }

    public function part1(string $input): string
    {
        $leftOfVerticalMirrors = [];
        $aboveHorizonalMirrors = [];

        foreach ($this->maps as $map) {
            foreach (range(0, $map->getW() - 1) as $x) {
                if ($map->isMirrorLineVertical($x)) {
                    $leftOfVerticalMirrors[] = $x;
                    break;
                }
            }

            foreach (range(0, $map->getH() - 1) as $y) {
                if ($map->isMirrorLineHorizontal($y)) {
                    $aboveHorizonalMirrors[] = $y;
                    break;
                }
            }
        }

        return (string) (array_sum($leftOfVerticalMirrors) + 100 * array_sum($aboveHorizonalMirrors));
    }

    public function part2(string $input): string
    {
        $leftOfVerticalMirrors = [];
        $aboveHorizonalMirrors = [];

        foreach ($this->maps as $map) {
            foreach (range(0, $map->getH() - 1) as $y) {
                $smudgeFixed = false;
                if ($map->isMirrorLineHorizontal($y, true, $smudgeFixed) && $smudgeFixed) {
                    $aboveHorizonalMirrors[] = $y;
                    break;
                }
            }

            foreach (range(0, $map->getW() - 1) as $x) {
                $smudgeFixed = false;
                if ($map->isMirrorLineVertical($x, true, $smudgeFixed) && $smudgeFixed) {
                    $leftOfVerticalMirrors[] = $x;
                    break;
                }
            }
        }

        return (string) (array_sum($leftOfVerticalMirrors) + 100 * array_sum($aboveHorizonalMirrors));
    }
}

class Map2DWithXYMap extends Map2D
{
    private array $xyMap;

    public function __construct(array $map)
    {
        parent::__construct($map);

        $this->xyMap = [];
        foreach (range(0, $this->w - 1) as $x) {
            $this->xyMap[$x] = parent::getCol($x);
        }
    }

    public static function fromInput(string $input): self
    {
        return new Map2DWithXYMap(self::parseInput($input));
    }

    public function getCol(int $x): ?array
    {
        return $this->xyMap[$x] ?? null;
    }

    public function isMirrorLineHorizontal(int $y, bool $fixSmudge = false, bool &$smudgeFixed = false): bool
    {
        return $this->isMirrorLine($this->map, $y, $fixSmudge, $smudgeFixed);
    }

    public function isMirrorLineVertical(int $x, bool $fixSmudge = false, bool &$smudgeFixed = false): bool
    {
        return $this->isMirrorLine($this->xyMap, $x, $fixSmudge, $smudgeFixed);
    }

    private function isMirrorLine(
        array $map,
        int $offset,
        bool $fixSmudge = false,
        bool &$smudgeFixed = false
    ): bool {
        $len = count($map);
        if ($offset <= 0 || $offset >= $len) { return false; }
        $rangeBefore = array_reverse(range(0, $offset - 1));
        $rangeAfter = range($offset, $len - 1);

        $zipped = array_map(null, $rangeBefore, $rangeAfter);
        foreach ($zipped as $zip) {
            if (null === $zip[0] || null === $zip[1]) { continue; }
            if ($map[$zip[0]] !== $map[$zip[1]]) {
                if ($fixSmudge && !$smudgeFixed) {
                    $diffCount = count(array_filter(
                        array_map(null, $map[$zip[0]], $map[$zip[1]]),
                        fn(array $vals) => $vals[0] !== $vals[1]
                    ));
                    if (1 === $diffCount) {
                        $smudgeFixed = true;
                        continue;
                    }
                }

                return false;
            }
        }

        return true;
    }
}
