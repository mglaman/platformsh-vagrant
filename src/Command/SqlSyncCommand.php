<?php

namespace Platformsh\Vagrant\Command;

use Platformsh\Vagrant\Config\ProjectConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SqlSyncCommand extends Command
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
      ->setName('sql-sync')
      ->setDescription('Imports remote environment into local environment');
  }

  /**
   * {@inheritdoc}
   */
  function __construct()
  {
    parent::__construct('SQL Sync');
    $this->config = new ProjectConfig();
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $platformsh = $this->getProjectConfig();
    $projectId = $platformsh['project_id'];
    $projectName = $platformsh['project_name'];
    $projectEnv = $platformsh['project_environment'];

    // Get sql-dump from remote.
    $process = new Process("drush @$projectId.$projectEnv sql-dump > ./project/shared/$projectEnv-dump.sql");
    $this->runProcess($process);

    // Drop local database
    $process = new Process("vagrant -c ssh -c '/usr/local/bin/drush sql-drop --yes --root=/var/www/platformsh/www --uri=$projectName.platformsh.dev' ");
    $this->runProcess($process);

    // Import SQL
    $process = new Process("vagrant -c ssh -c '/usr/local/bin/drush sqlc --root=/var/www/platformsh/www --uri=$projectName.platformsh.dev' < /vagrant/project/shared/$projectEnv-dump.sql");
    $this->runProcess($process);
  }
}
