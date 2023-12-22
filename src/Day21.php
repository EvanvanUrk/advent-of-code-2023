<?php

namespace AoC;

use AoC\Solution;
use AoC\Util\Map2D;
use AoC\Util\Vec2D;

class Day21 implements Solution
{
    private Map2D $map;
    private array $start;
    private array $stepCache = [];

    public function __construct(string $input)
    {
        $this->map = Map2D::fromInput($input);
        $start = $this->map->find('S');
        if ($start === null) {
            throw new \Exception('No starting position');
        }
        $this->start = $start;
        $this->map->set($this->start['x'], $this->start['y'], '.');
    }

    public function part1(string $input): string
    {
        $queue = [$this->start['x'] . '-' . $this->start['y'] => $this->start];
        foreach (range(1, 64) as $i) {
            $next = [];
            foreach ($queue as $cur) {
                foreach ($this->getSteps($cur['x'], $cur['y']) as $step) {
                    $key = $step['x'] . '-' . $step['y'];
                    $next[$key] = $step;
                }
            }
            $queue = $next;
        }

        return (string) count($queue);
    }

    private function getSteps(int $x, int $y): array
    {
        $key = $x . '-' . $y;
        if (isset($this->stepCache[$key])) {
            return $this->stepCache[$key];
        }

        $steps = [];
        $this->map->walkRegion(
            $x - 1,
            $x + 1,
            $y - 1,
            $y + 1,
            function(int $_x, int $_y, ?string $value)
            use ($x, $y, &$steps)
            {
                if ($value === null) {
                    return;
                }

                $dist = abs($x - $_x) + abs($y - $_y);
                if ($dist !== 1) {
                    return;
                }

                if ($value === '.') {
                    $steps[] = ['x' => $_x, 'y' => $_y];
                }
            }
        );

        $this->stepCache[$key] = $steps;

        return $steps;
    }

    public function part2(string $input): string
    {
        dump($this->getStepsWrapped(-5, -5));
        return '';

//        $this->stepCache = ['default' => [], 'wrapped' => []];
//        $queue = [$this->start['x'] . '-' . $this->start['y'] => $this->start];
//        foreach (range(1, 10) as $i) {
//            $next = [];
//            foreach ($queue as $cur) {
//                foreach ($this->getStepsWrapped($cur['x'], $cur['y']) as $step) {
//                    $key = $step['x'] . '-' . $step['y'];
//                    $next[$key] = $step;
//                }
//            }
//            $queue = $next;
//        }
//
//        return (string) count($queue);
    }

    private function getStepsWrapped(int $x, int $y): array
    {
        $steps = [];
        $this->map->walkRegion(
            $x - 1,
            $x + 1,
            $y - 1,
            $y + 1,
            function(int $_x, int $_y, ?string $value)
                use ($x, $y, &$steps)
            {
                if ($value === null) {
                    dump($_x, $this->map->getW());
                    $xWrapped = Util::mod($_x, $this->map->getW());
                    $yWrapped = Util::mod($_y, $this->map->getW());
                    dump($xWrapped, $yWrapped);
                    dump('-------');
                    $value = $this->map->get($xWrapped, $yWrapped);
                }

                $dist = abs($x - $_x) + abs($y - $_y);
                if ($dist !== 1) {
                    return;
                }

                if ($value === '.') {
                    $steps[] = ['x' => $_x, 'y' => $_y];
                }
            }
        );

        return $steps;
    }
}
