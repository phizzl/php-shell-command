<?php


namespace Phizzl\PhpShellCommand;


class ShellCommand
{
    const OUTPUT_TYPE_DIRECT = 1;

    const OUTPUT_TYPE_ARRAY = 2;

    const OUTPUT_TYPE_BOTH = 3;

    const STATUS_OK = 'OK';

    const STATUS_TIMEOUT_REACHED = 'Timeout reached';

    const STATUS_NOT_EXECUTED = 'Not executed';

    /**
     * @var string
     */
    protected $command;

    /**
     * @var null|string
     */
    protected $cwd;

    /**
     * @var ExecTimeout
     */
    protected $timeout;

    /**
     * @var array
     */
    protected $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    /**
     * ShellCommand constructor.
     * @param string $command
     * @param string|null $cwd
     * @param ExecTimeout|null $timeout
     */
    public function __construct($command, $cwd = null, ExecTimeout $timeout = null)
    {
        $this->command  = $command;
        $this->cwd      = $cwd;
        $this->timeout  = ( $timeout === null )
            ? new ExecTimeout(3)
            : $timeout;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param int $outputType
     * @return ShellCommandResult
     */
    public function run($outputType = self::OUTPUT_TYPE_ARRAY, ExecTimeout $runTimeout = null)
    {
        $timeout    = ( $runTimeout === null )
            ? $this->timeout
            : $runTimeout;

        $timeout->start();

        $process = proc_open($this->getCommand(), $this->descriptorspec, $pipes, $this->cwd);

        if (is_resource($process)) {
            $stdOut = $this->readFromPipe($pipes[1], $outputType, $timeout);
            $stdErr = $this->readFromPipe($pipes[2], $outputType, $timeout);

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);
            $execTime = $timeout->end();
            $status = ( $timeout->isTimeoutHit() )
                ? self::STATUS_TIMEOUT_REACHED
                : self::STATUS_OK;

            $result = new ShellCommandResult($status, $execTime, $exitCode, $stdOut, $stdErr);
        }

        return isset($result) ? $result : new ShellCommandResult(self::STATUS_NOT_EXECUTED, 0, -1, [], []);
    }

    /**
     * @param resource $pipe
     * @param int $outputType
     * @param ExecTimeout $timeout
     * @return array
     */
    protected function readFromPipe($pipe, $outputType, ExecTimeout $timeout)
    {
        $returnValues = array();
        $toArray = $outputType === self::OUTPUT_TYPE_ARRAY || $outputType === self::OUTPUT_TYPE_BOTH;
        $directOutput = $outputType === self::OUTPUT_TYPE_DIRECT || $outputType === self::OUTPUT_TYPE_BOTH;

        while (!$timeout->isTimeoutHit() && $output = fgets($pipe)) {
            if ($toArray) {
                $returnValues[] = trim($output);
            }
            if ($directOutput) {
                echo $output . PHP_EOL;
            }
        }

        return $returnValues;
    }
}