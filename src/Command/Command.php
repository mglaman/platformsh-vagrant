<?php

namespace Platformsh\Vagrant\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class Command extends BaseCommand {
    /** @var OutputInterface|null */
    protected $output;
    /** @var OutputInterface|null */
    protected $stdErr;
    /** @var bool */
    protected static $interactive = false;

    function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->stdErr = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        self::$interactive = $input->isInteractive();
    }

    /**
     * @param \Symfony\Component\Process\Process $process
     */
    protected function runProcess(Process $process) {
        $process->setTimeout(null);
        $process->setIdleTimeout(120);
        try {
            $process->mustRun(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->stdErr->writeln("<error>err =></error> " . $buffer);
                } else {
                    $this->stdErr->writeln("<info>out =></info> " . $buffer);
                }
            });
        } catch (ProcessFailedException $e) {
            $process->signal(SIGKILL);
            echo $e->getMessage();
            exit(1);
        }
    }
}
