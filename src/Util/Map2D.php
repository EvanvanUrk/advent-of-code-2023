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
     * Creates a 2D map from a 2 dimensional array. Assumes all rows are of equal length.
     */
    public function __construct(array $map)
    {
        $this->map = $map;

        $this->h = count($this->map);
        $this->w = max(array_map(
            function(array $row) {
                return count($row);
            },
            $this->map
        ));
    }

    public static function fromInput(string $input): Map2D
    {
        $map = array_map(
            fn(string $line) => mb_str_split($line),
            Util::splitByLines($input)
        );

        return new Map2D($map);
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

    public function getW(): int
    {
        return $this->w;
    }

    public function getH(): int
    {
        return $this->h;
    }

    public function getCol(int $x): ?array
    {
        if ($x >= $this->w) {
            return null;
        }

        return array_reduce(
            $this->map,
            fn($col, $row) => array_merge($col, [$row[$x]]),
            []
        );
    }

    public function getRow(int $y): ?array
    {
        return $this->map[$y] ?? null;
    }

    public function insertCol(int $x, array $col): bool
    {
        if (count($col) !== $this->h || $x >= $this->w) {
            return false;
        }

        $col = array_values($col);
        foreach ($this->map as $i => &$row) {
            array_splice($row, $x, 0, $col[$i]);
        }
        $this->w += 1;
        return true;
    }

    public function insertRow(int $y, array $row): bool
    {
        if (count($row) !== $this->w || $y >= $this->h) {
            return false;
        }

        $row = array_values($row);
        array_splice($this->map, $y, 0, [$row]);
        $this->h += 1;
        return true;
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

    public function map(callable $callback): Map2D
    {
        return $this->mapRegion(0, $this->w - 1, 0, $this->h - 1, $callback);
    }

    public function mapRegion(
        int $xMin,
        int $xMax,
        int $yMin,
        int $yMax,
        callable $callback
    ): Map2D {
        $map = [];
        foreach (Util::range($yMin, $yMax) as $y) {
            $map[$y] = [];
            foreach (Util::range($xMin, $xMax) as $x) {
                $map[$y][$x] == $callback($x, $y, $this->get($x, $y));
            }
        }

        return new Map2D($map);
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
        return implode('', array_map(
            fn(array $line) => implode('', $line) . PHP_EOL,
            $this->map
        ));
    }
}
