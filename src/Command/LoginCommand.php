<?php

namespace Platformsh\Vagrant\Command;

use Platformsh\Vagrant\Config\ProjectConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class SetupCommand
 * @package Platformsh\Vagrant\Command
 */
class LoginCommand extends Command
{

  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
      $this
        ->setName('login')
        ->setDescription('Logs into the Drupal project');
  }

  /**
   * {@inheritdoc}
   */
  function __construct()
  {
      parent::__construct('Login');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $projectName = $this->getProjectConfig('project_name');
    // @todo: get proper hostname from Vagrant config.
    $process = new Process("vagrant -c ssh -c '/usr/local/bin/drush uli --root=/var/www/platformsh/www --uri=$projectName.platformsh.dev' ");
    $this->runProcess($process);
  }
}
