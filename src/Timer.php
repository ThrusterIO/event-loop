<?php

namespace Thruster\Component\EventLoop;

/**
 * Class Timer
 *
 * @package Thruster\Component\EventLoop
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Timer
{
    /**
     * @var EventLoop
     */
    protected $loop;

    /**
     * @var float
     */
    protected $interval;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var bool
     */
    protected $periodic;

    /**
     * @var int
     */
    protected $priority;

    public function __construct(
        EventLoop $loop,
        $interval,
        callable $callback,
        bool $periodic = false,
        int $priority = 0
    ) {
        $this->loop = $loop;
        $this->interval = (float) $interval;
        $this->callback = $callback;
        $this->periodic = $periodic;
        $this->priority = $priority;
    }

    /**
     * @return EventLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return float
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return boolean
     */
    public function isPeriodic()
    {
        return $this->periodic;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    public function cancel()
    {
        $this->loop->cancelTimer($this);
    }
}
