<?php

declare(strict_types=1);

namespace AoC;

use AoC\Util\Map3D;
use AoC\Util\Vec3D;

class Day22 implements Solution
{
    private array $blocks = [];
    private array $supports = [];
    private array $supportedBy = [];
    private array $removable = [];

    public function __construct(string $input)
    {
        // Collect block positions and max map dimensions
        $max = [0, 0, 0];
        $blocks = [];
        foreach (Util::splitByLines($input) as $line) {
            [$fromCoords, $toCoords] = explode('~', $line);
            $a = [2 => $zA] = explode(',', $fromCoords);
            $b = [2 => $zB] = explode(',', $toCoords);

            $blocks[] = $zB > $zA ? [$a, $b] : [$b, $a];

            if ($a[0] > $max[0]) { $max[0] = (int) $a[0]; }
            if ($b[0] > $max[0]) { $max[0] = (int) $b[0]; }
            if ($a[1] > $max[1]) { $max[1] = (int) $a[1]; }
            if ($b[1] > $max[1]) { $max[1] = (int) $b[1]; }
            if ($a[2] > $max[2]) { $max[2] = (int) $a[2]; }
            if ($b[2] > $max[2]) { $max[2] = (int) $b[2]; }
        }

        // Sort lowest blocks first
        usort(
            $blocks,
            fn(array $a, array $b) => $a[0][2] - $b[0][2]
        );

        // Create map with floor, insert blocks at lowest point above already inserted blocks
        $map = new Map3D($max[0] + 1, $max[1] + 1, $max[2] + 1);
        $map->setRange(new Vec3D(0, 0, 0), new Vec3D($max[0] + 1, $max[1] + 1, 0), '-');

        $checkBelow = function(array $points) use ($map): bool
        {
            foreach ($points as $point) {
                if ($map->get($point->sub(new Vec3D(0, 0, 1))) !== null) {
                    return false;
                }
            }

            return true;
        };

        $blockMap = [];
        foreach ($blocks as $i => [$zLow, $zHigh]) {
            $zDrop = 0;
            $from = new Vec3D((int) $zLow[0], (int) $zLow[1], (int) $zLow[2] - $zDrop);
            $to = new Vec3D((int) $zHigh[0], (int) $zHigh[1], (int) $zHigh[2] - $zDrop);
            while ($checkBelow(Vec3D::range($from, $to)) && $zLow[2] - $zDrop > 1) {
                $zDrop += 1;
                $from = new Vec3D((int) $zLow[0], (int) $zLow[1], (int) $zLow[2] - $zDrop);
                $to = new Vec3D((int) $zHigh[0], (int) $zHigh[1], (int) $zHigh[2] - $zDrop);
            }

            $this->blocks[$i] ??= true;
            $blockMap[$i] = Vec3D::range($from, $to);
            $map->setRange($from, $to, (string) $i);
        }

        // Check that no blocks are missing
        foreach ($blockMap as $i => $blockRange) {
            foreach ($blockRange as $point) {
                if ($map->get($point) != $i) {
                    die('oops...');
                }
            }
        }

        // Check which block supports which
        $get = function(int $i, array $blockRange) use ($map)
        {
            $above = [];
            $below = [];
            $add = function(?string $cell, array &$vals) use ($i, $map) {
                if ($cell === null) { return; }
                $cell = (int) $cell;
                if ($cell !== $i) {
                    $vals[$cell] ??= true;
                }
            };

            $offset = new Vec3D(0, 0, 1);
            foreach ($blockRange as $point) {
                $add($map->get($point->add($offset)), $above);
                $add($map->get($point->sub($offset)), $below);
            }

            return [array_keys($above), array_keys($below)];
        };

        foreach ($blockMap as $i => $blockRange) {
            [$above, $below] = $get($i, $blockRange);
            $this->supports[$i] = $above;
            $this->supportedBy[$i] = $below;
        }
    }

    public function part1(string $input): string
    {
        // Any blocks not supporting any other blocks can be removed
        foreach ($this->supports as $i => $support) {
            if (count($support) === 0) {
                $this->removable[$i] ??= true;
            }
        }

        // Any blocks sharing support for the same block can be individually removed
        // But ONLY if they are not the sole supporting block for any other block
        $this->soleSupports = [];
        foreach ($this->supportedBy as $support) {
            if (count($support) === 1) {
                $this->soleSupports[$support[0]] ??= true;
            }
        }

        foreach ($this->supportedBy as $support) {
            if (count($support) > 1) {
                foreach ($support as $block) {
                    if (!isset($this->soleSupports[$block])) {
                        $this->removable[$block] ??= true;
                    }
                }
            }
        }

        return (string) count($this->removable);
    }

    public function part2(string $input): string
    {
        $wouldFall = function(int $block, array $fallen): bool
        {
            $intersection = array_intersect($fallen, $this->supportedBy[$block]);
            return count($intersection) === count($this->supportedBy[$block]);
        };

        $falls = [];
        $starters = array_reverse(array_diff(array_keys($this->blocks), array_keys($this->removable)));
        foreach ($starters as $starter) {
            $falling = [$starter => true];
            $queue = [$starter];
            while (($cur = array_shift($queue)) !== null) {
                foreach ($this->supports[$cur] as $supported) {
                    if ($wouldFall($supported, array_keys($falling))) {
                        $falling[$supported] ??= true;
                        $queue[] = $supported;
                    }
                }
            }

            unset($falling[$starter]);
            $falls[$starter] = $falling;
        }

        return (string) array_sum(array_map(fn(array $falls) => count($falls), $falls));
    }
}
