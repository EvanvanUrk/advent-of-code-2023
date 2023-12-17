<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use AoC\Util\Map2D;
use AoC\Util\Point;
use Exception;
use SplPriorityQueue;

class Day17 implements Solution
{
    private Map2D $map;

    private Point $stop;

    public function __construct(string $input)
    {
        $this->map = Map2D::fromInput($input);
        $this->start = new Point(0, 0);
        $this->stop = new Point(
            $this->map->getW() - 1,
            $this->map->getH() - 1,
        );
    }

    public function part1(string $input): string
    {
        $seen = [];

        $queue = new SplPriorityQueue();
        foreach ([new Point(1, 0), new Point(0, 1)] as $next) {
            $cost = (int) $this->map->getPoint($next);
            $queue->insert(new Crucible(
                $next,
                $next,
                1,
                $cost,
                null
            ), $cost);
        }

        while (!$queue->isEmpty()) {
            /** @var Crucible $cur */
            $cur = $queue->extract();
            if ($cur->pos == $this->stop) {
//                $this->printRoute($cur);
                return (string) $cur->heatLoss;
            }

            foreach ($cur->possibleMoves($this->map) as $next) {
                $key = $next->getKey();
                if (!isset($seen[$key])) {
                    $from[$next->pos->getKey()] = $cur->pos;
                    $seen[$key] = true;
                    $queue->insert($next, -$next->heatLoss);
                }
            }
        }

        throw new Exception('No path found');
    }

    public function part2(string $input): string
    {
        $seen = [];

        $queue = new SplPriorityQueue();
        foreach ([new Point(1, 0), new Point(0, 1)] as $next) {
            $cost = (int) $this->map->getPoint($next);
            $queue->insert(new UltraCrucible(
                $next,
                $next,
                1,
                $cost,
                null
            ), $cost);
        }

        while (!$queue->isEmpty()) {
            /** @var UltraCrucible $cur */
            $cur = $queue->extract();
            if ($cur->pos == $this->stop) {
//                $this->printRoute($cur);
                return (string) $cur->heatLoss;
            }

            foreach ($cur->possibleMoves($this->map) as $next) {
                $key = $next->getKey();
                if (!isset($seen[$key])) {
                    $from[$next->pos->getKey()] = $cur->pos;
                    $seen[$key] = true;
                    $queue->insert($next, -$next->heatLoss);
                }
            }
        }

        throw new Exception('No path found');
    }

    public function printRoute(Crucible $crucible): void
    {
        $map = clone $this->map;

        while ($crucible->prev !== null) {
            $dir = $crucible->pos->sub($crucible->prev->pos);
            $map->setPoint(
                $crucible->pos,
                match ($dir->getKey()) {
                    '1-0' => "\033[91m>\033[0m",
                    '-1-0' => "\033[91m<\033[0m",
                    '0-1' => "\033[91mv\033[0m",
                    '0--1' => "\033[91m^\033[0m",
                    default => "\033[91m#\033[0m",
                }
            );
            $crucible = $crucible->prev;
        }

        echo $map . PHP_EOL;
        usleep(50000);
    }
}

class Crucible
{
    public function __construct(
        public readonly Point $pos,
        public readonly Point $dir,
        public readonly int $movesInDir,
        public readonly int $heatLoss,
        public readonly ?self $prev,
    ) { }

    public function possibleMoves(Map2D $map): array
    {
        $dirs = [
            new Point(1, 0),
            new Point(-1, 0),
            new Point(0, 1),
            new Point(0, -1),
        ];

        $moves = [];

        foreach ($dirs as $dir) {
            if ($dir == $this->dir->opposite()) {
                continue;
            }

            if ($dir == $this->dir && $this->movesInDir === 3) {
                continue;
            }

            $next = $this->next($dir, $map);
            if ($next !== null) { $moves[] = $next; }
        }

        return $moves;
    }

    protected function next(Point $dir, Map2D $map): ?self
    {
        $nextPos = $this->pos->add($dir);
        $nextCost = $map->getPoint($nextPos);
        if ($nextCost === null) {
            return null;
        }

        return new $this(
            $nextPos,
            $dir,
            $dir == $this->dir ? $this->movesInDir + 1 : 1,
            $this->heatLoss + (int) $nextCost,
            $this,
        );
    }

    public function getKey(): string
    {
        return sprintf(
            '%s|%s|%d',
            $this->pos->getKey(),
            $this->dir->getKey(),
            $this->movesInDir
        );
    }
}

class UltraCrucible extends Crucible
{
    public function possibleMoves(Map2D $map): array
    {
        if ($this->movesInDir < 4) {
            $next = $this->next($this->dir, $map);
            if ($next !== null) {
                return [$next];
            } else {
                throw new Exception('Could not move enough in direction');
            }
        }

        $dirs = [
            new Point(1, 0),
            new Point(-1, 0),
            new Point(0, 1),
            new Point(0, -1),
        ];

        $moves = [];

        foreach ($dirs as $dir) {
            if ($dir == $this->dir->opposite()) {
                continue;
            }

            if ($dir == $this->dir && $this->movesInDir === 10) {
                continue;
            }

            if ($dir != $this->dir) {
                $minCheckPos = $this->pos->add(new Point(
                    $dir->x * 4,
                    $dir->y * 4,
                ));
                if ($map->getPoint($minCheckPos) === null) {
                    continue;
                }
            }

            $next = $this->next($dir, $map);
            if ($next !== null) { $moves[] = $next; }
        }

        return $moves;
    }
}
