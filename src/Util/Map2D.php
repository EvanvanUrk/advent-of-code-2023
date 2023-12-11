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

    public readonly int $w;

    public readonly int $h;

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

    public function set(int $x, int $y, string $value): void
    {
        if (!isset($this->map[$y]) || !isset($this->map[$y][$x])) {
            return;
        }
        $this->map[$y][$x] = $value;
    }

    /**
     * Walks the map left to right then top to bottom and executes a callback for
     * each position. Reverses direction if `$reverse` is `true`.
     *
     * @param callable(int $x, int $y, ?string $value): bool $callback
     * Callback to execute. May return `true` to immediately stop walking the map.
     * Should return false or void to only stop the current callback.
     */
    public function walk(callable $callback, bool $reverse = false): void
    {
        $this->walkRegion(0, $this->w - 1, 0, $this->h - 1, $callback, $reverse);
    }

    /**
     * Walks a region of the map left to right then top to bottom and executes a
     * callback for each position. Reverses direction if `$reverse` is `true`.
     *
     * @param callable(int $x, int $y, ?string $value): bool $callback
     * Callback to execute. May return `true` to immediately stop walking the map.
     * Should return false or void to only stop the current callback.
     */
    public function walkRegion(
        int $xMin,
        int $xMax,
        int $yMin,
        int $yMax,
        callable $callback,
        bool $reverse = false,
    ): void {
        foreach (Util::range($yMin, $yMax, $reverse) as $y) {
            foreach (Util::range($xMin, $xMax, $reverse) as $x) {
                if (true === $callback($x, $y, $this->get($x, $y))) {
                    break 2;
                }
            }
        }
    }

    /**
     * Searches the map for the given search term or expression and returns the
     * first match. Searches from left to right then top to bottom by default,
     * or from right to left then bottom to top if `$reverseSearch` is `true`.
     *
     * @return null|array{'x': int, 'y': int} Array with coordinates of first
     * match. Null if search was not found or matched.
     */
    public function find(
        string $search,
        bool $regexp = false,
        bool $reverseSearch = false
    ): ?array {
        return $this->findInRegion(
            0,
            $this->w - 1,
            0,
            $this->h - 1,
            $search,
            $regexp,
            $reverseSearch
        );
    }

    /**
     * Searches a region of the map for the given search term or expression and
     * returns the first match. Searches from left to right then top to bottom
     * by default, or from right to left then bottom to top if `$reverseSearch`
     * is `true`.
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
        $match = null;
        $this->walkRegion(
            $xMin, $xMax, $yMin, $yMax,
            function(int $x, int $y, ?string $value) use ($search, $regexp, &$match) {
                if ($value === null) { return false; }
                if (is_numeric($value)) { $value = (string) $value; }

                if ((!$regexp && $value === $search)
                    || ($regexp && preg_match($search, $value))) {
                    $match = ['x' => $x, 'y' => $y];
                    return true;
                }

                return false;
            },
            $reverseSearch
        );

        return $match;
    }
    /**
     * Searches the map for the given search term or expression and returns all
     * matches. Searches from left to right then top to bottom by default, or
     * from right to left then bottom to top if `$reverseSearch` is `true`.
     *
     * @return array<string, array{'x': int, 'y': int}> Coordinates per match
     * indexed by value. Empty if search was not found or matched.
     */
    public function findAll(
        string $search,
        bool $regexp = false,
        bool $reverseSearch = false
    ): array {
        return $this->findAllInRegion(
            0,
            $this->w - 1,
            0,
            $this->h - 1,
            $search,
            $regexp,
            $reverseSearch
        );
    }

    /**
     * Searches a region of the map for the given search term or expression and
     * returns all matches. Searches from left to right then top to bottom by
     * default, or from right to left then bottom to top if `$reverseSearch`
     * is `true`.
     *
     * @return array<string, array{'x': int, 'y': int}> Coordinates per match.
     * indexed by value. Empty if search was not found or matched.
     */
    public function findAllInRegion(
        int $xMin,
        int $xMax,
        int $yMin,
        int $yMax,
        string $search,
        bool $regexp = false,
        bool $reverseSearch = false,
    ): array {
        $matches = [];
        $this->walkRegion(
            $xMin, $xMax, $yMin, $yMax,
            function(int $x, int $y, ?string $value) use (&$matches, $search, $regexp) {
                if ($value === null) { return; }
                if (is_numeric($value)) { $value = (string) $value; }

                if ((!$regexp && $value === $search)
                    || ($regexp && preg_match($search, $value))) {
                    if (false === array_key_exists($value, $matches)) {
                        $matches[$value] = [];
                    }
                    $matches[$value][] = ['x' => $x, 'y' => $y];
                }
            },
            $reverseSearch
        );

        return $matches;
    }

    public function __toString(): string
    {
        return implode('', array_map(fn(array $line) => implode('', $line), $this->map));
    }
}
