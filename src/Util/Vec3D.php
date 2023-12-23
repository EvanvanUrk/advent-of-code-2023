<?php

declare(strict_types=1);

namespace AoC\Util;

use AoC\Util;

class Vec3D
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
        public readonly int $z
    ) { }

    public function getKey(): string
    {
        return $this->x . '-' . $this->y . '-' . $this->z;
    }

    public function add(Vec3D $point): Vec3D
    {
        return new Vec3D(
            $this->x + $point->x,
            $this->y + $point->y,
            $this->z + $point->z,
        );
    }

    public function sub(Vec3D $point): Vec3D
    {
        return new Vec3D(
            $this->x - $point->x,
            $this->y - $point->y,
            $this->z - $point->z,
        );
    }

    public function opposite(): Vec3D
    {
        return new Vec3D(-$this->x, -$this->y, -$this->z);
    }

    public function asArray(bool $withKeys = false): array
    {
        return $withKeys
            ? ['x' => $this->x, 'y' => $this->y, 'z' => $this->z]
            : [$this->x, $this->y, $this->z];
    }

    /**
     * @return array<Vec3D>
     */
    public static function range(Vec3D $from, Vec3D$to, bool $reverse = false, int $step = 1): array
    {
        $range = [];
        foreach (Util::range($from->x, $to->x, $reverse, $step) as $x) {
            foreach (Util::range($from->y, $to->y, $reverse, $step) as $y) {
                foreach (Util::range($from->z, $to->z, $reverse, $step) as $z) {
                    $range[] = new Vec3D($x, $y, $z);
                }
            }
        }

        return $range;
    }
}
