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
class LoginCommand extends Command
{

  /**
   * @var \Platformsh\Vagrant\Config\ProjectConfig
   */
  protected $config;

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
      $this->config = new ProjectConfig();
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $platformsh = $this->config->get('platformsh');
    if (!is_array($platformsh) || empty($platformsh)) {
        throw new \Exception('Invalid config.yml');
    }
    $projectName = $platformsh['project_name'];

    // @todo: get proper hostname from Vagrant config.
    $process = new Process("vagrant -c ssh -c 'cd /var/www/platformsh/www && /usr/local/bin/drush uli --uri=$projectName.platformsh.dev' ");
    $this->runProcess($process);
  }
}
