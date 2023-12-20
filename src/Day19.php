<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;
use Exception;

class Day19 implements Solution
{
    private array $workflows;
    private array $parts;
    private int $counter = 0;

    public function __construct(string $input)
    {
        $split = explode(PHP_EOL . PHP_EOL, $input);

        $this->workflows = [];
        foreach (Util::splitByLines($split[0]) as $line) {
            preg_match('/(\w+)\{(.+)}/', $line, $matches);
            $key = $matches[1];
            $this->workflows = array_merge(
                $this->workflows,
                $this->parseWorkflow($key, $matches[2]),
            );
        }

        $this->parts = array_map(function(string $line) {
            preg_match_all('/(\w+)=(\d+)/', $line, $matches);
            return array_combine($matches[1], $matches[2]);
        }, Util::splitByLines($split[1]));
    }

    public function part1(string $input): string
    {
        $accepted = [];

        foreach ($this->parts as $part) {
            if ($this->runWorkflow($part) === 'A') {
                $accepted[] = $part;
            }
        }

        return (string) array_sum(array_map(
            fn(array $part) => array_sum($part),
            $accepted
        ));
    }

    public function part2(string $input): string
    {
        $default = new Range(1, 4000);

//        $default->set(1, 838, false);
//        dump($default->count());
//
//        die;

        $possible = [];

        /**
         * @param array<string, Range> $bounds
         */
        $traverse = function(array $bounds, string $key = 'in')
            use (&$possible, &$traverse)
        {
            $w = $this->workflows[$key];

            $pTrue = [
                'x' => clone $bounds['x'],
                'm' => clone $bounds['m'],
                'a' => clone $bounds['a'],
                's' => clone $bounds['s'],
            ];

            $pFalse = [
                'x' => clone $bounds['x'],
                'm' => clone $bounds['m'],
                'a' => clone $bounds['a'],
                's' => clone $bounds['s'],
            ];

            $v = (int) $w['val'];
            if ($w['cond'] === '>') {
                $pTrue[$w['prop']]->set(1, $v, false);
                $pFalse[$w['prop']]->set($v + 1, 4000 - $v, false);
            } else {
                $pTrue[$w['prop']]->set($v + 1, 4000 - $v, false);
                $pFalse[$w['prop']]->set(1, $v, false);
            }

            $ps = [
                'true' => $pTrue,
                'false' => $pFalse,
            ];
            dump(
                $key,
                'true:::',
                array_map(
                    fn(Range $r) => $r->count(),
                    $pTrue
                ),
                'false:::',
                array_map(
                    fn(Range $r) => $r->count(),
                    $pFalse
                )
            );

            foreach ($w['res'] as $p => $res) {
                if ($res === 'A') {
                    $possible[$key . $this->counter++] =
                        array_product(array_map(
                            fn(Range $r) => $r->count(),
                            $ps[$p]
                        ))
                    ;
                } elseif ($res !== 'R') {
                    $traverse($ps[$p], $res);
                }
            }
        };

        $traverse([
            'x' => clone $default,
            'm' => clone $default,
            'a' => clone $default,
            's' => clone $default,
        ]);

        dump($possible);
        dump(array_sum($possible));

        return '';
    }

    public function runWorkflow(array $part, string $start = 'in'): string
    {
        $w = $this->workflows[$start];
        while (is_array($w)) {
            $w = match ($w['cond']) {
                '>' => $part[$w['prop']] > $w['val']
                    ? $w['res']['true']
                    : $w['res']['false'],
                '<' => $part[$w['prop']] < $w['val']
                    ? $w['res']['true']
                    : $w['res']['false'],
                default => throw new Exception('Unsupported condition ' . $w['cond']),
            };

            if (is_array($w)) { continue; }

            if (isset($this->workflows[$w])){ $w = $this->workflows[$w]; }
        }

        return $w;
    }

    public function parseWorkflow(string $key, string $workflow): array
    {
        $split = explode(':', $workflow, 2);
        preg_match('/(\w+)([<>])(\d+)/', $split[0], $matches);

        $workflows = [];
        $results = [];
        foreach (explode(',', $split[1], 2) as $res) {
            if (!strpos($res, ':')) {
                $results[] = $res;
                continue;
            }

            $_key = '_' . $this->counter;
            $this->counter += 1;
            $workflows = array_merge(
                $workflows,
                $this->parseWorkflow($_key, $res)
            );
            $results[] = $_key;
        }

        $workflows[$key] = [
            'prop' => $matches[1],
            'cond' => $matches[2],
            'val' => $matches[3],
            'res' => [
                'true' => $results[0],
                'false' => $results[1],
            ],
        ];

        return $workflows;
    }

    public function removeRedundant(array $workflows): array
    {
        $redundant = [];
        foreach ($workflows as $key => $w) {
            if ($w['res']['true'] === $w['res']['false']) {
                $redundant[$key] = $w['res']['true'];
            }
        }

        foreach ($redundant as $key => $res) {
            unset($workflows[$key]);
            foreach ($workflows as &$w) {
                if ($w['res']['true'] === $key) { $w['res']['true'] = $res; }
                if ($w['res']['false'] === $key) { $w['res']['false'] = $res; }
            }
        }

        return $workflows;
    }
}

class Range
{
    private array $set;

    public function __construct(
        int $start,
        int $count,
    ) {
        $this->set = array_fill($start, $count, true);
    }

    public function bitAnd(Range $range): void
    {
        $this->set = array_combine(array_keys($this->set), array_map(
            fn(bool $a, bool $b) => $a && $b,
            $this->set,
            $range->set
        ));
    }

    public function getOffset(int $offset): bool
    {
        return $this->set[$offset] ?? false;
    }

    public function set(int $start, int $count, bool $val): void
    {
        for ($i = 0; $i < $count; $i += 1) {
            if (isset($this->set[$i + $start])) {
                $this->set[$i + $start] = $val;
            }
        }
    }

    public function count(): int
    {
        return count(array_filter($this->set));
    }

    public function not(): Range
    {
        $inverse = clone $this;
        foreach ($inverse->set as &$val) {
            $val = !$val;
        }
        return $inverse;
    }
}
