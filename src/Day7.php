<?php

declare(strict_types=1);

namespace AoC;

use AoC\Solution;

class Day7 implements Solution
{
    public function part1(string $input): string
    {
        $answer = collect(Util::splitByLines($input))
            ->map(fn(string $line) => explode(' ', $line))
            ->map(fn(array $parts) => [
                'hand' => new Hand($parts[0]),
                'score' => (int) $parts[1],
            ])
            ->sort(fn(array $a, array $b) => $a['hand']->compareTo($b['hand']))
            ->values()
            ->map(fn(array $hand, int $idx) => ($idx + 1) * $hand['score'])
            ->sum()
        ;

        return (string) $answer;
    }

    public function part2(string $input): string
    {
        $answer = collect(Util::splitByLines($input))
            ->map(fn(string $line) => explode(' ', $line))
            ->map(fn(array $parts) => [
                'hand' => new HandWithJoker($parts[0]),
                'score' => (int) $parts[1],
            ])
            ->sort(fn(array $a, array $b) => $a['hand']->compareTo($b['hand']))
            ->values()
            ->map(fn(array $hand, int $idx) => ($idx + 1) * $hand['score'])
            ->sum()
        ;

        return (string) $answer;
    }
}

enum Kind: int {
    case FiveOfAKind = 6;
    case FourOfAKind = 5;
    case FullHouse = 4;
    case ThreeOfAKind = 3;
    case TwoPair = 2;
    case OnePair = 1;
    case HighCard = 0;
}

class Hand
{
    public const CARD_ORDER = [ '2', '3', '4', '5', '6', '7', '8', '9', 'T', 'J', 'Q', 'K', 'A' ];

    protected array $count;

    public function __construct(
        public readonly string $cards
    ) {
        $this->countCards();
    }

    public function compareTo(Hand $other): int
    {
        $kindThis = $this->getKind();
        $kindOther = $other->getKind();
        if ($kindThis->value > $kindOther->value) {
            return 1;
        }

        if ($kindThis->value < $kindOther->value) {
            return -1;
        }

        $map = array_flip($this::CARD_ORDER);

        foreach (range(0, 4) as $i) {
            if ($map[$this->cards[$i]] > $map[$other->cards[$i]]) {
                return 1;
            }

            if ($map[$this->cards[$i]] < $map[$other->cards[$i]]) {
                return -1;
            }
        }

        return 0;
    }

    public function getKind(): Kind
    {
        return match (max($this->count)) {
            5 => Kind::FiveOfAKind,
            4 => Kind::FourOfAKind,
            3 => count($this->count) === 2 ? Kind::FullHouse : Kind::ThreeOfAKind,
            2 => count($this->count) === 3 ? Kind::TwoPair : Kind::OnePair,
            1 => Kind::HighCard,
        };
    }

    protected function countCards(): void
    {
        $count = [];
        foreach (mb_str_split($this->cards) as $card) {
            if (array_key_exists($card, $count) === false)  {
                $count[$card] = 0;
            }

            $count[$card] += 1;
        }

        asort($count);

        $this->count = array_reverse($count, true);
    }
}

class HandWithJoker extends Hand
{
    public const CARD_ORDER = [ 'J', '2', '3', '4', '5', '6', '7', '8', '9', 'T', 'Q', 'K', 'A' ];

    protected function countCards(): void
    {
        parent::countCards();
        if (isset($this->count['J']) && $this->count['J'] !== 5) {
            $jokers = $this->count['J'];
            unset($this->count['J']);
            $this->count[array_keys($this->count)[0]] += $jokers;
        }
    }
}
