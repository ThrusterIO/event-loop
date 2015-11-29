<?php

namespace Thruster\Component\EventLoop;

/**
 * Class Signal
 *
 * @package Thruster\Component\EventLoop
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Signal
{
    /**
     * @var EventLoop
     */
    protected $loop;

    /**
     * @var int
     */
    protected $signalNo;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var int
     */
    protected $priority;

    public function __construct(
        EventLoop $loop,
        int $signalNo,
        callable $callback,
        int $priority = 0
    ) {
        $this->loop = $loop;
        $this->signalNo = $signalNo;
        $this->callback = $callback;
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
     * @return int
     */
    public function getSignalNo()
    {
        return $this->signalNo;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
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
        $this->loop->cancelSignal($this);
    }
}
