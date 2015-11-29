<?php
namespace Thruster\Component\EventLoop;


/**
 * Class EventLoop
 *
 * @package Thruster\Component\EventLoop
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface EventLoopInterface
{
    /**
     * @param resource  $stream
     * @param callable $listener
     */
    public function addReadStream($stream, callable $listener);

    /**
     * @param resource  $stream
     * @param callable $listener
     */
    public function addWriteStream($stream, callable $listener);

    /**
     * @param resource $stream
     * @param callable $listener
     * @param int  $flags
     */
    public function addStream($stream, callable $listener, $flags);

    /**
     * @param resource $stream
     */
    public function removeReadStream($stream);

    /**
     * @param resource $stream
     */
    public function removeWriteStream($stream);

    /**
     * @param resource $stream
     */
    public function removeStream($stream);

    /**
     * @param float $interval
     * @param callable $callback
     * @param int      $priority
     *
     * @return Timer
     */
    public function addTimer($interval, callable $callback, int $priority = 0) : Timer;

    /**
     * @param float $interval
     * @param callable $callback
     * @param int      $priority
     *
     * @return Timer
     */
    public function addPeriodicTimer($interval, callable $callback, int $priority = 0) : Timer;

    /**
     * @param Timer $timer
     *
     * @return $this
     */
    public function cancelTimer(Timer $timer);

    /**
     * @param Timer $timer
     *
     * @return bool
     */
    public function isTimerActive(Timer $timer) : bool;

    /**
     * @param int      $signalNo
     * @param callable $callback
     * @param int      $priority
     *
     * @return Signal
     */
    public function addSignal(int $signalNo, callable $callback, int $priority = 0) : Signal;

    /**
     * @param Signal $signal
     *
     * @return $this
     */
    public function cancelSignal(Signal $signal);

    /**
     * @param Signal $signal
     *
     * @return bool
     */
    public function isSignalActive(Signal $signal) : bool;

    /**
     * @param callable $callback
     * @param int      $pid
     * @param int      $priority
     *
     * @return Child
     */
    public function addChild(callable $callback, int $pid = 0, int $priority = 0) : Child;

    /**
     * @param Child $child
     *
     * @return $this
     */
    public function cancelChild(Child $child);

    /**
     * @param Child $child
     *
     * @return bool
     */
    public function isChildActive(Child $child) : bool;

    /**
     * @return $this
     */
    public function afterFork();

    /**
     * @return $this
     */
    public function tick();

    /**
     * @return $this
     */
    public function run();

    /**
     * @return $this
     */
    public function stop();
}
