<?php

namespace Platformsh\Vagrant\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        ->setDescription('Logs into the Drupal project')
        ->addOption('environment', null, InputOption::VALUE_NONE, 'Run command on Platform.sh environment');
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
    // If --environment, run on Platform.sh environment. Otherwise the VM.
    if ($input->getOption('environment')) {
      $projectId = $this->getProjectConfig('project_id');
      $projectEnv = $this->getProjectConfig('project_environment');
      $process = new Process("drush @$projectId.$projectEnv uli");

    } else {
      $projectName = $this->getProjectConfig('project_name');
      // @todo: get proper hostname from Vagrant config.
      $process = new Process("vagrant -c ssh -c '/usr/local/bin/drush uli --root=/var/www/platformsh/www --uri=$projectName.platformsh.dev' ");
    }
    $this->runProcess($process);
  }
}
