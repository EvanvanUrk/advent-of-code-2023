<?php

declare(strict_types=1);

namespace AoC;

class Day3 implements Solution
{
    public function part1(string $input): string
    {
        $lines = Util::splitByLines($input);

        $numbers = [];
        foreach($lines as $y => $line) {
            $number = '';
            $lLen = strlen($line);
            for ($x = 0; $x < $lLen; $x++) {
                $currentPosNumeric = is_numeric($line[$x]);
                if ($currentPosNumeric) {
                    $number .= $line[$x];
                }

                if ($number === '') { continue; }
                $endOfLine = $x === $lLen - 1;
                if (!$currentPosNumeric || $endOfLine) {
                    if ($currentPosNumeric && $endOfLine) { $x += 1; }
                    if ($this->isPart($number, $lines, $x, $y, $lLen)) {
                        $numbers[] = $number;
                    }
                    $number = '';
                }
            }
        }

        return (string) array_sum($numbers);
    }

    public function part2(string $input): string
    {
        $lines = Util::splitByLines($input);

        $ratios = [];
        foreach($lines as $y => $line) {
            $number = '';
            $lLen = strlen($line);
            for ($x = 0; $x < $lLen; $x++) {
                $currentPosNumeric = is_numeric($line[$x]);
                if ($currentPosNumeric) {
                    $number .= $line[$x];
                }

                if ($number === '') { continue; }
                $endOfLine = $x === $lLen - 1;
                if (!$currentPosNumeric || $endOfLine) {
                    if ($currentPosNumeric && $endOfLine) { $x += 1; }
                    $yMin = $y - 1;
                    $yMax = $y + 1;
                    $xMax = $x; // current space is already after the number
                    $xMin = $x - strlen($number) - 1;

                    foreach (range($yMin, $yMax) as $checkY) {
                        foreach (range($xMin, $xMax) as $checkX) {
                            if (isset($lines[$checkY]) && isset($lines[$checkY][$checkX])
                                && $lines[$checkY][$checkX] === '*') {
                                $ratioKey = $checkY . '-' . $checkX;
                                if (!isset($ratios[$ratioKey])) { $ratios[$ratioKey] = []; }
                                $ratios[$ratioKey][] = (int) $number;
                                break 2;
                            }
                        }
                    }

                    $number = '';
                }
            }
        }

        return (string) array_sum(
            array_map(
                function(array $idx) {
                    return array_product($idx);
                },
                array_filter(
                    $ratios,
                    function(array $numbers) {
                        return count($numbers) === 2;
                    }
                )
            )
        );
    }

    public function isPart(
        string $number,
        array $lines,
        int $x,
        int $y,
        int $lLen
    ): bool {
        $nLen = strlen($number);
        $sEnd = min($x + 1, $lLen);
        $sStart = max($x - $nLen - 1, 0);
        $sLen = $sEnd - $sStart;

        foreach ([-1, 0, 1] as $offset) {
            if (isset($lines[$y + $offset])) {
                $search = substr($lines[$y + $offset], $sStart, $sLen);

                $match = preg_replace('/[0-9.]+/', '', $search);
                if ($match !== '') {
                    return true;
                }
            }
        }

        return false;
    }
}
