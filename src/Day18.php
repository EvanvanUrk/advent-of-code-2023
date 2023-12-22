<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use AoC\Util\Map2D;
use AoC\Util\Vec2D;
use AoC\Util\Route;
use Illuminate\Support\Collection;

class Day18 implements Solution
{
    public function part1(string $input): string
    {
        $steps = collect(Util::splitByLines($input))
            ->map(fn(string $line) => explode(' ', $line))
            ->map(fn(array $parts) => [
                'dir' => Direction::from($parts[0]),
                'len' => (int) $parts[1],
                'clr' => Util::rgbHexToIntArray(substr($parts[2], 1, -1)),
            ])
        ;

        $minX = $maxX = $x = 0;
        $minY = $maxY = $y = 0;
        foreach ($steps as $cur) {
            $move = $cur['dir']->getMove();
            foreach (range(0, $cur['len'] - 1) as $i) {
                $x += $move->x;
                $y += $move->y;
            }

            if ($x < $minX) { $minX = $x; }
            if ($x > $maxX) { $maxX = $x; }
            if ($y < $minY) { $minY = $y; }
            if ($y > $maxY) { $maxY = $y; }
        }

        $map = Map2D::fromFill(
            abs($minX - $maxX) + 1,
            abs($minY - $maxY) + 1,
            '.',
        );

        $route = new Route();
        $x = 0;
        $y = 0;
        foreach ($steps as $cur) {
            $move = $cur['dir']->getMove();
            foreach (range(0, $cur['len'] - 1) as $i) {
                $x += $move->x;
                $y += $move->y;
                $cur['pos'] = new Vec2D($x + -$minX, $y + -$minY);
                $route->add($cur['pos']);
                $map->set(
                    $x,
                    $y,
                    Util::asRgbOutput(
                        '#',
                        $cur['clr']['r'],
                        $cur['clr']['g'],
                        $cur['clr']['b']
                    )
                );
            }
        }

        return (string) ($route->count() + Util::countCellsInsideRoute(
            $route,
            $map,
        ));
    }

    public function part2(string $input): string
    {
        $steps = collect(Util::splitByLines($input))
            ->map(fn(string $line) => explode(' ', $line))
            ->map(function(array $parts) {
                $hex = substr($parts[2], 2, -1);
                $dir = match (substr($hex, -1)) {
                    '0' => Direction::Right,
                    '1' => Direction::Down,
                    '2' => Direction::Left,
                    '3' => Direction::Up,
                };
                $len = hexdec(substr($hex, 0, -1));

                return [
                    'dir' => $dir,
                    'len' => $len,
                    'clr' => Util::rgbHexToIntArray(substr($parts[2], 1, -1)),
                ];
            })
        ;

        $minX = $maxX = $x = 0;
        $minY = $maxY = $y = 0;
        foreach ($steps as $cur) {
            $move = $cur['dir']->getMove();
            foreach (range(0, $cur['len'] - 1) as $i) {
                $x += $move->x;
                $y += $move->y;
            }

            if ($x < $minX) { $minX = $x; }
            if ($x > $maxX) { $maxX = $x; }
            if ($y < $minY) { $minY = $y; }
            if ($y > $maxY) { $maxY = $y; }
        }

        $map = Map2D::fromFill(
            abs($minX - $maxX) + 1,
            abs($minY - $maxY) + 1,
            '.',
        );

        $route = new Route();
        $x = 0;
        $y = 0;
        foreach ($steps as $cur) {
            $move = $cur['dir']->getMove();
            foreach (range(0, $cur['len'] - 1) as $i) {
                $x += $move->x;
                $y += $move->y;
                $cur['pos'] = new Vec2D($x + -$minX, $y + -$minY);
                $route->add($cur['pos']);
                $map->set(
                    $x,
                    $y,
                    Util::asRgbOutput(
                        '#',
                        $cur['clr']['r'],
                        $cur['clr']['g'],
                        $cur['clr']['b']
                    )
                );
            }
        }

        return (string) ($route->count() + Util::countCellsInsideRoute(
            $route,
            $map,
        ));
    }
}

class Instruction
{
    public function __construct(
        public readonly Direction $dir,
        public readonly int $len,
        public readonly string $clr
    ) { }
}

enum Direction: string
{
    case Up = 'U';
    case Down = 'D';
    case Right = 'R';
    case Left = 'L';

    public function getMove(): Vec2D
    {
        return match ($this) {
            Direction::Up => new Vec2D(0, -1),
            Direction::Down => new Vec2D(0, 1),
            Direction::Right => new Vec2D(1, 0),
            Direction::Left => new Vec2D(-1, 0),
        };
    }
}
