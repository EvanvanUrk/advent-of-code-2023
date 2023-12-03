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

    /**
     * Creates a 2D map from the puzzle input. Values are interpreted as single character strings.
     */
    public function __construct(string $input)
    {
        $this->map = array_map(
            fn(string $line) => mb_str_split($line),
            Util::splitByLines($input)
        );
    }

    public function get(int $x, int $y): ?string
    {
        if (!isset($this->map[$y]) || !isset($this->map[$y][$x])) {
            return null;
        }
        return $this->map[$y][$x];
    }

    /**
     * Walks the puzzle input as a 2d map and executes a callback for each position
     *
     * @param string $input Puzzle input
     * @param callable $callback Receives x/y coordinates and value at current pos
     */
    public function walk(callable $callback): void
    {
        foreach ($this->map as $y => $line) {
            foreach ($line as $x => $value) {
                $callback($x, $y, $value);
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
        $getRange = fn(int $min, int $max) => $reverseSearch
            ? array_reverse(range($min, $max))
            : range($min, $max);

        foreach ($getRange($yMin, $yMax) as $y) {
            foreach ($getRange($xMin, $xMax) as $x) {
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
