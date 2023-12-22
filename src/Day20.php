<?php

declare(strict_types=1);

namespace AoC;

class Day20 implements Solution
{
    public function part1(string $input): string
    {
        $connOut = [];
        $connIn = [];
        /** @var array<Module> $modules */
        $modules = [];

        foreach (Util::splitByLines($input) as $line) {
            [$node, $to] = explode(' -> ', $line);
            $type = 'broadcaster';
            if (in_array($node[0], ['%', '&'])) {
                $type = $node[0];
                $node = substr($node, 1);
            }

            $connOut[$node] = explode(', ', $to);
            foreach ($connOut[$node] as $in) {
                $connIn[$in] ??= [];
                $connIn[$in][] = $node;
            }
            $modules[$node] = $type;
        }

        foreach ($modules as $node => $type) {
            $modules[$node] = match ($type) {
                '%' => new FlipFlop($node),
                '&' => new Conjunction($node, $connIn[$node]),
                'broadcaster' => new Broadcaster($node)
            };
        }

        $modules['button'] = new Button('button');
        $connOut['button'] = ['broadcaster'];
        $connIn['broadcaster'] = ['button'];

        $modules['button']->push();
        $queue = [$modules['button']->pulse(null)];

        return '';
    }

    public function part2(string $input): string
    {
        return '';
    }
}

class Signal
{
    public function __construct(
        public readonly string $from,
        public readonly bool $pulse,
    ) { }
}

interface SignalSender
{
    public function connect(SignalReceiver $receiver): void;

    public function pulseOut(): ?Signal;
}

interface SignalReceiver
{
    public function getName(): string;
    public function pulseIn(Signal $in): void;
}

abstract class AbstractModule implements SignalSender
{
    /** @var array<SignalReceiver> */
    protected array $receivers = [];

    public function __construct(
        protected string $name,
    ) { }

    public function getName(): string
    {
        return $this->name;
    }

    public function connect(SignalReceiver $receiver): void
    {
        $this->receivers[$receiver->getName()] = $receiver;
    }

    protected function send(bool $pulse): void
    {
        foreach ($this->receivers as $receiver)
        {
            $receiver->pulse($this->signal($pulse));
        }
    }

    protected function signal(bool $pulse): Signal
    {
        return new Signal($this->getName(), $pulse);
    }
}

//enum Pulse: int
//{
//    case Low = 0;
//    case High = 1;
//
//    public function flip(): Pulse
//    {
//        return match ($this) {
//            Pulse::Low => Pulse::High,
//            Pulse::High => Pulse::Low,
//        };
//    }
//}

class Conjunction extends AbstractModule implements SignalReceiver
{
    private array $state;

    public function __construct(string $name, array $inputs)
    {
        parent::__construct($name);
        foreach ($inputs as $input) {
            $this->state[$input] = false;
        }
    }

    public function pulse(Signal $in): ?Signal
    {
        $this->state[$in->from] = $in->pulse;
        return $this->signal(
            count($this->state) !== count(array_filter($this->state))
        );
    }
}

class FlipFlop extends AbstractModule implements SignalReceiver
{
    private bool $pulse = false;

    public function pulse(Signal $in): ?Signal
    {
        if (!$in->pulse) {
            $this->pulse = !$this->pulse;
            return $this->signal($this->pulse);
        }

        return null;
    }
}

class Broadcaster extends AbstractModule implements SignalReceiver
{
    public function pulse(Signal $in): void
    {
        $this->send($in->pulse);
    }
}

class Button extends AbstractModule
{
    public function push(): void
    {
        $this->send(false);
    }
}
