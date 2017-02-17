<?php


namespace Phizzl\PhpShellCommand;


class ShellCommandSequence
{
    /**
     * @var array
     */
    protected $sequence;

    /**
     * ShellCommandSequence constructor.
     * @param array $sequence
     */
    public function __construct(array $sequence = array()){
        $this->sequence = $sequence;
    }

    /**
     * @param ShellCommand $command
     */
    public function addCommand(ShellCommand $command){
        $this->sequence[] = $command;
    }

    /**
     * @param int $outputMode
     * @return array
     */
    public function runSequence($outputMode = ShellCommand::OUTPUT_TYPE_ARRAY){
        $returnValues = array();

        foreach ($this->sequence as $index => $command) {
            if (!$command instanceof ShellCommand) {
                throw new ShellCommandException("Invalid shell command object at index \"$index\"");
            }

            $returnValues[] = $command->run($outputMode);
        }

        return $returnValues;
    }
}