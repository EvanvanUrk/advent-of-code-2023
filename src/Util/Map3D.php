<?php

declare(strict_types=1);

namespace AoC\Util;

use Stringable;

class Map3D
{
    /** @var array<int, array<int, array<int, null|string|Stringable>>> */
    private array $map;
    private array $set = [];

    private int $printWidth = 2;

    public function __construct(
        private int $w,
        private int $h,
        private int $d,
    ) {
        $this->map = array_fill(
            0, $w,
            array_fill(
                0, $h,
                array_fill(
                    0, $d,
                    null
                )
            )
        );
    }

    public function get(Vec3D $point): null|string|Stringable
    {
        if (!$this->inBounds($point)) {
            return null;
        }

        [$x, $y, $z] = $point->asArray();

        return $this->map[$x][$y][$z];
    }

    public function set(Vec3D $point, null|string|Stringable $value): void
    {
        if (!$this->inBounds($point)) {
            return;
        }

        $this->map[$point->x][$point->y][$point->z] = $value;

        if ($value === null) {
            unset($this->set[$point->getKey()]);
        } else {
            $this->set[$point->getKey()] = true;
        }

        $printWidth = strlen((string) $value) + 1;
        if ($printWidth > $this->printWidth) {
            $this->printWidth = $printWidth;
        }
    }

    public function setRange(Vec3D $from, Vec3D $to, null|string|Stringable $value): void
    {
        foreach (Vec3D::range($from, $to) as $point) {
            $this->set($point, $value);
        }
    }

    public function has(Vec3D $point): bool
    {
        return isset($this->set[$point->getKey()]);
    }

    public function inBounds(Vec3D $point): bool
    {
        [$x, $y, $z] = $point->asArray();

        return $x >= 0 && $y >= 0 && $z >= 0
            && $x < $this->w && $y < $this->h && $z < $this->d;
    }

    public function __toString(): string
    {
        $str = '';

        $append = function(array $vals) use (&$str) {
            $vals = array_values(array_unique(array_filter(
                $vals,
                fn(null|string|Stringable $val) => $val !== null
            )));

            $count = count($vals);
            if ($count < 1) {
                $val = '.';
            } elseif($count > 1) {
                $val = '?';
            } else {
                $val = $vals[0];
            }

            $str .= str_pad($val, $this->printWidth, ' ', STR_PAD_LEFT);
        };

        // x by z - front
        foreach (range(0, $this->w - 1) as $x) {
            $str .= str_pad((string) $x, $this->printWidth, ' ', STR_PAD_LEFT);
        }
        $str .= PHP_EOL;

        foreach (range($this->d - 1, 0) as $z) {
            foreach (range(0, $this->w - 1) as $x) {
                $vals = [];
                foreach (range(0, $this->h - 1) as $y) {
                    $vals[] = $this->get(new Vec3D($x, $y, $z));
                }

                $append($vals);
            }
            $str .= ' ' . $z . PHP_EOL;
        }

        $str .= PHP_EOL;

        // y by z - side
        foreach (range(0, $this->h - 1) as $y) {
            $str .= str_pad((string) $y, $this->printWidth, ' ', STR_PAD_LEFT);
        }
        $str .= PHP_EOL;

        foreach (range($this->d - 1, 0) as $z) {
            foreach (range(0, $this->h - 1) as $y) {
                $vals = [];
                foreach (range(0, $this->w - 1) as $x) {
                    $vals[] = $this->get(new Vec3D($x, $y, $z));
                }

                $append($vals);
            }
            $str .= ' ' . $z . PHP_EOL;
        }

        $str .= PHP_EOL;

        // x by y - top
        // ...

        return $str;
    }
}
