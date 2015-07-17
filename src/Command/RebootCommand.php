<?php

namespace Platformsh\Vagrant\Command;

use Platformsh\Vagrant\Config\ProjectConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class SetupCommand
 * @package Platformsh\Vagrant\Command
 */
class RebootCommand extends Command
{

  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
      $this
        ->setName('reboot')
        ->setDescription('Reboots the machine');
  }

  /**
   * {@inheritdoc}
   */
  function __construct()
  {
      parent::__construct('Reboot');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->stdErr->writeln("The virtual machine will <info>reboot</info>");
    $process = new Process('vagrant reload');
    $this->runProcess($process);
  }
}
