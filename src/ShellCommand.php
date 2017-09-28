<?php


namespace Phizzl\PhpShellCommand;


class ShellCommand
{
    const STATUS_OK = 'OK';

    const STATUS_NOT_EXECUTED = 'Not executed';

    /**
     * @var string
     */
    private $command;

    /**
     * @var null|string
     */
    private $cwd;

    /**
     * @var array
     */
    private $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    /**
     * ShellCommand constructor.
     * @param string $command
     * @param string|null $cwd
     */
    public function __construct($command, $cwd = null)
    {
        $this->command  = $command;
        $this->cwd      = $cwd;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param bool $silent
     * @return ShellCommandResult
     */
    public function run($silent = true)
    {
        $start = microtime(true);
        $process = proc_open($this->getCommand(), $this->descriptorspec, $pipes, $this->cwd);
        if (!is_resource($process)) {
            return new ShellCommandResult(self::STATUS_NOT_EXECUTED, 0, -1, '', 'Process could not be created');
        }

        $stdOut = stream_get_contents($pipes[1]);
        $stdErr = stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if(!$silent){
            echo $stdOut;
        }

        if($stdErr){
            trigger_error($stdErr);
        }

        return new ShellCommandResult(self::STATUS_OK, (microtime(true)-$start), $exitCode, $stdOut, $stdErr);
    }
}