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
class SetupCommand extends Command
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
          ->setName('setup')
          ->setDescription('Setup the Platform.sh Vagrant VM');
    }

    /**
     * {@inheritdoc}
     */
    function __construct()
    {
        parent::__construct('Setup');
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

        $this->stdOut->writeln("Project Name: <info>$projectName</info>");
        $this->stdOut->writeln("Project ID: <info>$projectId</info>");
        $this->stdOut->writeln("Project Environment: <info>$projectEnv</info>\n");

        // Have the user verify the info.
        $dialog = $this->getHelper('dialog');
        if (!$dialog->askConfirmation(
          $output,
          'Is this information correct? <question>[Y,n]</question>',
          true
        )) {
            $this->stdOut->writeln("<error>Environment build cancelled</error>");
            return;
        }

        $this->platformSetup($projectId, $projectEnv);
        $this->vagrantSetup();
        $this->finalizeSetup();
    }

    /**
     * Runs Platform.sh command to get project.
     *
     * @param $id
     * @param $environment
     */
    protected function platformSetup($id, $environment) {
        if (!is_dir(CLI_ROOT . '/project')) {
            $platformCommand = "platform get $id project --environment $environment";
        } else {
            $platformCommand = "cd project && platform build";
        }

        $process = new Process($platformCommand);
        $this->runProcess($process);

        $process = new Process("cd project && project drush-aliases && drush cc drush");
        $this->runProcess($process);


        // Generate the settings.local.php file with proper database information.
        $settingsCode = "<?php\n\n// Database configuration.\n\$databases['default']['default'] = array(\n  'driver' => 'mysql',\n  'host' => 'localhost',\n  'username' => 'root',\n   'password' => 'root',\n  'database' => 'default',\n  'prefix' => '',\n);";
        file_put_contents("project/shared/settings.local.php", $settingsCode);
    }

    /**
     * Runs the Vagrant command to start the virtual machine.
     */
    protected function vagrantSetup() {
        if (is_file(CLI_ROOT . '/.vagrant/machines/platformsh/virtualbox/action_provision')) {
            $this->stdOut->writeln("The virtual machine will <info>be rebuilt</info>");
            $vagrantCommand = 'vagrant reload --provision';
        } else {
            $this->stdOut->writeln("The virtual machine will <info>be created</info>");
            $vagrantCommand = 'vagrant up';
        }

        $process = new Process($vagrantCommand);
        $this->runProcess($process);
    }

    /**
     * Runs misc finishing touches.
     */
    protected function finalizeSetup() {
      $this->stdOut->writeln("<info>Syncing environment databases.</info>");
      $command = $this->getApplication()->find('sql-sync');
      $command->run($this->stdIn, $this->stdOut);
    }
}
