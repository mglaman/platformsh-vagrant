<?php

namespace Platformsh\Vagrant\Command;

use Platformsh\Vagrant\Config\ProjectConfig;
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

    /**
     * @var \Platformsh\Vagrant\Config\ProjectConfig
     */
    protected $config;

    function __construct($name = null)
    {
        parent::__construct($name);
        $this->config = new ProjectConfig();
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
    protected function runProcess(Process $process)
    {
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


    /**
     * Returns Platform.sh project config information.
     *
     * @param null|string $key
     *
     * @return array|mixed|string
     * @throws \Exception
     */
    protected function getProjectConfig($key = NULL)
    {
        $platformsh = $this->config->get('platformsh');
        if (!is_array($platformsh) || empty($platformsh)) {
            throw new \Exception('Invalid config.yml');
        }

        if ($key === NULL) {
            return $platformsh;
        } else {
            return $platformsh[$key];
        }
    }
}
