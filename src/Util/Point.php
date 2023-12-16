<?php

declare(strict_types=1);

namespace AoC\Util;

class Point
{
    public function __construct(
        public readonly int $x,
        public readonly int $y
    ) { }

    public function getKey(): string
    {
        return $this->x . '-' . $this->y;
    }

    public function add(Point $point): Point
    {
        return new Point(
            $this->x + $point->x,
            $this->y + $point->y,
        );
    }

    public function sub(Point $point): Point
    {
        return new Point(
            $this->x - $point->x,
            $this->y - $point->y
        );
    }
}
