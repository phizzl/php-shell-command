<?php


namespace Phizzl\PhpShellCommand;


class ShellCommand
{
    const OUTPUT_TYPE_DIRECT = 1;

    const OUTPUT_TYPE_ARRAY = 2;

    const OUTPUT_TYPE_BOTH = 3;

    const STATUS_OK = 'OK';

    const STATUS_TIMEOUT_REACHED = 'Timeout reached';

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
        2 => array("pipe", "r")
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
     * @param int $outputType
     * @return array(0=stdin,1=stderr,2=exitCode)
     */
    public function run($outputType = self::OUTPUT_TYPE_ARRAY, ExecTimeout $runTimeout = null)
    {
        $returnValue = array(
            0           => array(),
            1           => array(),
            2           => -1,
            'status'    => self::STATUS_OK,
            'exec_time' => .0
        );

        $timeout    = ( $runTimeout === null )
            ? $this->timeout
            : $runTimeout;

        $timeout->start();

        $process = proc_open($this->command, $this->descriptorspec, $pipes, $this->cwd);

        if (is_resource($process)) {
            $returnValue[0] = $this->readFromPipe($pipes[1], $outputType, $timeout);
            $returnValue[1] = $this->readFromPipe($pipes[2], $outputType, $timeout);

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnValue[2]             = proc_close($process);
            $returnValue['exec_time']   = $timeout->end();
            $returnValue['status']      = ( $timeout->isTimeoutHit() )
                ? self::STATUS_TIMEOUT_REACHED
                : $returnValue['status'];
        }

        return $returnValue;
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