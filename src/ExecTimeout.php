<?php


namespace Phizzl\PhpShellCommand;


class ExecTimeout
{
    protected $timeoutInSeconds;

    protected $startTime;

    protected $maxTime;

    protected $endTime;

    /**
     * ExecTimeout constructor.
     * @param int $timeoutInSeconds
     */
    public function __construct( $timeoutInSeconds ) {
        $this->timeoutInSeconds = (int)$timeoutInSeconds;
    }

    /**
     * @return $this
     */
    public function start() {
        $this->startTime    = microtime(true);
        $this->maxTime      = $this->startTime + $this->timeoutInSeconds;
        $this->endTime      = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTimeoutHit() {
        return ( $this->endTime === null )
            ? $this->maxTime < microtime(true)
            : $this->maxTime < $this->endTime;
    }

    /**
     * @return mixed
     */
    public function end() {
        $this->endTime = microtime(true);
        return $this->endTime - $this->startTime;
    }
}