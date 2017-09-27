<?php

namespace Phizzl\PhpShellCommand;


class ShellCommandBuilder
{
    /**
     * @var string
     */
    private $bin;

    /**
     * @var array
     */
    private $argsAndOpts;

    /**
     * @var string
     */
    private $stdOutRedirect;

    /**
     * @var string
     */
    private $stdErrRedirect;

    /**
     * @var string
     */
    private $readFromStdIn;

    /**
     * ShellCommandBuilder constructor.
     * @param string $bin
     */
    public function __construct($bin)
    {
        $this->bin = $bin;
        $this->argsAndOpts = [];
        $this->stdOutRedirect = '';
        $this->stdErrRedirect = '';
        $this->readFromStdIn = '';
    }

    /**
     * @param string $argument
     * @return $this
     */
    public function addArgument($argument)
    {
        $this->argsAndOpts[] = escapeshellarg($argument);
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $useEqualsAssignment
     * @return $this
     */
    public function addOption($name, $value, $useEqualsAssignment = false)
    {
        if($useEqualsAssignment){
            $this->argsAndOpts[] = "{$name}=" . escapeshellarg($value);
        }
        else{
            $this->argsAndOpts[] = "{$name} " . escapeshellarg($value);
        }
        return $this;
    }

    /**
     * @param string $redirectOutputTo
     * @return $this
     */
    public function redirectOutputTo($redirectOutputTo)
    {
        $this->stdOutRedirect = $redirectOutputTo;
        return $this;
    }

    /**
     * @param string $redirectErrorsTo
     * @return $this
     */
    public function redirectErrorsTo($redirectErrorsTo)
    {
        $this->stdErrRedirect = $redirectErrorsTo;
        return $this;
    }

    /**
     * @param string $readFromFile
     * @return $this
     */
    public function readFromFile($readFromFile)
    {
        $this->readFromStdIn = $readFromFile;
        return $this;
    }

    /**
     * @return ShellCommand
     */
    public function buildCommand()
    {
        $cmd = "{$this->bin} " . implode(" ", $this->argsAndOpts);

        if($this->readFromStdIn){
            $cmd .= " < {$this->readFromStdIn}";
        }

        if($this->stdOutRedirect){
            $cmd .= " > {$this->stdOutRedirect}";
        }

        if($this->stdErrRedirect){
            $cmd .= " 2>{$this->stdErrRedirect}";
        }

        return new ShellCommand($cmd);
    }
}