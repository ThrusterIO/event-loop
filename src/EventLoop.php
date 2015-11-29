<?php

namespace Thruster\Component\EventLoop;

use Ev;
use EvIo;
use EvLoop;
use EvTimer;
use EvChild;
use EvSignal;
use SplObjectStorage;

/**
 * Class EventLoop
 *
 * @package Thruster\Component\EventLoop
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class EventLoop implements EventLoopInterface
{
    /**
     * @var EvLoop
     */
    protected $loop;

    /**
     * @var SplObjectStorage|EvTimer[]
     */
    protected $timers;

    /**
     * @var SplObjectStorage|EvSignal[]
     */
    protected $signals;

    /**
     * @var SplObjectStorage|EvChild[]
     */
    protected $children;

    /**
     * @var bool
     */
    protected $running;

    /**
     * @var EvIo[]
     */
    private $readEvents;

    /**
     * @var EvIo[]
     */
    private $writeEvents;

    public function __construct()
    {
        $this->loop        = EvLoop::defaultLoop();
        $this->timers      = new SplObjectStorage();
        $this->signals     = new SplObjectStorage();
        $this->children    = new SplObjectStorage();
        $this->readEvents  = [];
        $this->writeEvents = [];
    }

    /**
     * @inheritdoc
     */
    public function addReadStream($stream, callable $listener) : self
    {
        $this->addStream($stream, $listener, Ev::READ);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addWriteStream($stream, callable $listener) : self
    {
        $this->addStream($stream, $listener, Ev::WRITE);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeReadStream($stream) : self
    {
        $key = (int) $stream;
        if (isset($this->readEvents[$key])) {
            $this->readEvents[$key]->stop();
            unset($this->readEvents[$key]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeWriteStream($stream) : self
    {
        $key = (int) $stream;
        if (isset($this->writeEvents[$key])) {
            $this->writeEvents[$key]->stop();
            unset($this->writeEvents[$key]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeStream($stream) : self
    {
        $this->removeReadStream($stream);
        $this->removeWriteStream($stream);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addStream($stream, callable $listener, $flags) : self
    {
        $listener = function ($event) use ($stream, $listener) {
            call_user_func($listener, $stream, $this);
        };

        $event = $this->loop->io($stream, $flags, $listener);

        if (($flags & \Ev::READ) === $flags) {
            $this->readEvents[(int)$stream] = $event;
        } elseif (($flags & \Ev::WRITE) === $flags) {
            $this->writeEvents[(int)$stream] = $event;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addTimer($interval, callable $callback, int $priority = 0) : Timer
    {
        $timer = new Timer($this, $interval, $callback, false, $priority);

        $callback = function ($evTimer) use ($timer) {
            call_user_func($timer->getCallback(), $timer, $evTimer);

            if ($this->isTimerActive($timer)) {
                $this->cancelTimer($timer);
            }
        };

        $event = $this->loop->timer($interval, 0, $callback, null, $priority);

        $this->timers->attach($timer, $event);

        return $timer;
    }

    /**
     * @inheritdoc
     */
    public function addPeriodicTimer($interval, callable $callback, int $priority = 0) : Timer
    {
        $timer = new Timer($this, $interval, $callback, true, $priority);

        $internalCallback = function ($evTimer) use ($timer) {
            call_user_func($timer->getCallback(), $timer, $evTimer);
        };

        $event = $this->loop->periodic(
            $timer->getInterval(),
            $timer->getInterval(),
            null,
            $internalCallback,
            null,
            $priority
        );

        $this->timers->attach($timer, $event);

        return $timer;
    }

    /**
     * @inheritdoc
     */
    public function cancelTimer(Timer $timer) : self
    {
        if (isset($this->timers[$timer])) {
            $this->timers[$timer]->stop();
            $this->timers->detach($timer);
        }
    }

    /**
     * @inheritdoc
     */
    public function isTimerActive(Timer $timer) : bool
    {
        return $this->timers->contains($timer);
    }

    /**
     * @inheritdoc
     */
    public function addSignal(int $signalNo, callable $callback, int $priority = 0) : Signal
    {
        $signal = new Signal($this, $signalNo, $callback, $priority);

        $internalCallback = function ($evSignal) use ($signal) {
            call_user_func($signal->getCallback(), $signal, $evSignal);
        };

        $event = $this->loop->signal($signalNo, $internalCallback, null, $priority);

        $this->signals->attach($signal, $event);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function cancelSignal(Signal $signal) : self
    {
        if (isset($this->signals[$signal])) {
            $this->signals[$signal]->stop();
            $this->signals->detach($signal);
        }
    }

    /**
     * @inheritdoc
     */
    public function isSignalActive(Signal $signal) : bool
    {
        return $this->signals->contains($signal);
    }

    /**
     * @inheritdoc
     */
    public function addChild(callable $callback, int $pid = 0, int $priority = 0) : Child
    {
        $child = new Child($this, $pid, $callback, $priority);

        $internalCallback = function ($evChild) use ($child) {
            call_user_func($child->getCallback(), $child, $evChild);
        };

        $event = $this->loop->child($pid, false, $internalCallback, null, $priority);

        $this->signals->attach($child, $event);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function cancelChild(Child $child) : self
    {
        if (isset($this->children[$child])) {
            $this->children[$child]->stop();
            $this->children->detach($child);
        }
    }

    /**
     * @inheritdoc
     */
    public function isChildActive(Child $child) : bool
    {
        return $this->children->contains($child);
    }

    /**
     * @inheritdoc
     */
    public function afterFork() : self
    {
        $this->loop->loopFork();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function tick() : self
    {
        $this->loop->run(Ev::RUN_ONCE | Ev::RUN_NOWAIT);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function run() : self
    {
        $this->running = true;

        while ($this->running) {
            $flags = Ev::RUN_ONCE;

            if ($this->timers->count() < 1 &&
                $this->signals->count() < 1 &&
                $this->children->count() < 1 &&
                count($this->readEvents) < 1 &&
                count($this->writeEvents) < 1
            ) {
                break;
            }

            $this->loop->run($flags);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function stop() : self
    {
        $this->running = false;

        return $this;
    }
}
