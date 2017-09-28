<?php

namespace Phizzl\PhpShellCommand;


class ShellCommandBuilder
{
    const OPTION_ASSIGN_SPACE = " ";

    const OPTION_ASSIGN_EQUALS = "=";

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
     * @var bool
     */
    private $runInBackground;

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
        $this->runInBackground = false;
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
     * @param string $optionAssignStr
     * @return $this
     */
    public function addOption($name, $value = '', $optionAssignStr = self::OPTION_ASSIGN_EQUALS)
    {
        $this->argsAndOpts[] = $name . ( strlen($value) ? $optionAssignStr . escapeshellarg($value) : '' );

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
     * @return $this
     */
    public function runInBackground()
    {
        $this->runInBackground = true;
        return $this;
    }

    /**
     * @return string
     */
    public function buildCommandString()
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

        if($this->runInBackground){
            $cmd .= " &";
        }

        return $cmd;
    }

    /**
     * @return ShellCommand
     */
    public function buildCommand()
    {
        return new ShellCommand($this->buildCommandString());
    }
}