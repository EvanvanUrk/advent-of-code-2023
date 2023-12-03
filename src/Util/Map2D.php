<?php

declare(strict_types=1);

namespace AoC\Util;

use AoC\Util;

class Map2D
{
    /**
     * @var array<int, array<int, string>> Map values indexed by Y first, X second
     */
    private array $map;

    private int $w;

    private int $h;

    /**
     * Creates a 2D map from the puzzle input. Values are interpreted as single
     * character strings. Assumes all lines start at `x = 0`.
     */
    public function __construct(string $input)
    {
        $this->map = array_map(
            fn(string $line) => mb_str_split($line),
            Util::splitByLines($input)
        );

        $this->h = count($this->map);
        $this->w = max(array_map(
            function(array $line) {
                return count($line);
            },
            $this->map
        ));
    }

    public function get(int $x, int $y): ?string
    {
        if (!isset($this->map[$y]) || !isset($this->map[$y][$x])) {
            return null;
        }
        return $this->map[$y][$x];
    }

    /**
     * Walks the puzzle left to right then top to bottom and executes a
     * callback for each position. Reverses direction if `$reverse` is `true`.
     *
     * @param callable(int $x, int $y, ?string $value): void $callback
     */
    public function walk(callable $callback, bool $reverse = false): void
    {
        foreach (Util::range(0, $this->h - 1, $reverse) as $y) {
            foreach (Util::range(0, $this->w - 1, $reverse) as $x) {
                $callback($x, $y, $this->get($x, $y));
            }
        }
    }

    /**
     * Searches a region of the map for the given search term or expression.
     * Searches from left to right then top to bottom by default, or from right
     * to left then bottom to top if `$reverseSearch` is `true`.
     *
     * @return null|array{'x': int, 'y': int} Array with coordinates of first
     * match. Null if search was not found or matched.
     */
    public function findInRegion(
        int $xMin,
        int $xMax,
        int $yMin,
        int $yMax,
        string $search,
        bool $regexp = false,
        bool $reverseSearch = false,
    ): ?array {
        foreach (Util::range($yMin, $yMax, $reverseSearch) as $y) {
            foreach (Util::range($xMin, $xMax, $reverseSearch) as $x) {
                $value = $this->get($x, $y);
                if ($value === null) { continue; }

                if ((!$regexp && $this->get($x, $y) === $search)
                    || ($regexp && preg_match($search, $value))) {
                    return ['x' => $x, 'y' => $y];
                }
            }
        }

        return null;
    }
}
