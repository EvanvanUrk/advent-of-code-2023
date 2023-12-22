<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use AoC\Util\Map2D;
use AoC\Util\Vec2D;

class Day16 implements Solution
{
    private Map2D $map;

    public function __construct(string $input)
    {
        $this->map = Map2D::fromInput($input);
    }

    public function part1(string $input): string
    {
        return (string) $this->energize(new Vec2D(0, 0), new Vec2D(1, 0));
    }

    public function part2(string $input): string
    {
        $dirs = [
            new Vec2D( 1,  0),
            new Vec2D( 0,  1),
            new Vec2D(-1,  0),
            new Vec2D( 0, -1)
        ];

        $energies = [];
        $pos = new Vec2D(0, 0);
        foreach ($dirs as $i => $dir) {
            $beamDir = $dirs[($i + 1) % 4];
            while (null !== $this->map->getPoint($pos)) {
                $energies[] = $this->energize($pos, $beamDir);
                $pos = $pos->add($dir);
            }
            $pos = $pos->sub($dir);
        }

        return (string) max($energies);
    }

    private function energize(Vec2D $startPos, Vec2D $startDir): int
    {
        $lightMap = $this->beam($startPos, $startDir);

        return collect(Util::splitByLines((string) $lightMap))
            ->map(fn(string $line) => strlen($line) - substr_count($line, '.'))
            ->sum()
        ;
    }

    private function beam(Vec2D $startPos, Vec2D $startDir): Map2D
    {
        $lightMap = Map2D::fromFill(
            $this->map->getW(),
            $this->map->getH(),
            '.'
        );

        $setStrength = function(Vec2D $pos) use ($lightMap) {
            $posStrength = $lightMap->getPoint($pos);
            $lightMap->setPoint(
                $pos,
                $posStrength === '.'
                    ? '1'
                    : (string) ((int) $posStrength + 1)
            );
        };

        $splittersEncountered = [];
        $beams = [
            [$startPos, $startDir]
        ];

        while (count($beams) > 0) {
            [$pos, $dir] = array_pop($beams);

            while (($cur = $this->map->getPoint($pos)) === '.') {
                $setStrength($pos);
                $pos = $pos->add($dir);
            }
            if (null === $this->map->getPoint($pos)) { continue; }

            if ($lightMap->getPoint($pos) === '.') { $setStrength($pos); }

            $newDirs = $this->mirror($cur, $dir);
            if (count($newDirs) === 2) {
                $key = $pos->getKey();
                if (isset($splittersEncountered[$key])) {
                    continue;
                } else {
                    $splittersEncountered[$key] = true;
                }
            }

            foreach ($newDirs as $newDir) {
                $beams[] = [$pos->add($newDir), $newDir];
            }
        }

        return $lightMap;
    }

    /**
     * @return array<Vec2D>
     */
    private function mirror(string $mirror, Vec2D $dir): array
    {
        if ($dir->x === 0 && $dir->y === 0) {
            return [$dir];
        }

        if ($dir->x !== 0 && $dir->y !== 0) {
            throw new \Exception('Only cardinal directions allowed for mirrors');
        }

        return match ($mirror) {
            '/' => [new Vec2D($dir->y * -1, $dir->x * -1)],
            '\\' => [new Vec2D($dir->y, $dir->x)],
            '-' => $dir->x === 0 ? [new Vec2D($dir->y, 0), new Vec2D(-$dir->y, 0)] : [$dir],
            '|' => $dir->y === 0 ? [new Vec2D(0, $dir->x), new Vec2D(0, -$dir->x)] : [$dir],
            default => [$dir],
        };
    }
}
