<?php

namespace Platformsh\Vagrant\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

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

}
