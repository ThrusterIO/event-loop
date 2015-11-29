<?php

namespace Thruster\Component\EventLoop;

/**
 * Class Child
 *
 * @package Thruster\Component\EventLoop
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Child
{
    /**
     * @var EventLoop
     */
    protected $loop;

    /**
     * @var int
     */
    protected $pid;

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
        int $pid,
        callable $callback,
        int $priority = 0
    ) {
        $this->loop = $loop;
        $this->pid = $pid;
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
    public function getPid()
    {
        return $this->pid;
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
        $this->loop->cancelChild($this);
    }
}
