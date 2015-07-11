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
        $platformsh = $this->config->get('platformsh');
        if (!is_array($platformsh) || empty($platformsh)) {
            throw new \Exception('Invalid config.yml');
        }

        $projectId = $platformsh['project_id'];
        $projectName = $platformsh['project_name'];
        $projectEnv = $platformsh['project_environment'];

        $this->stdErr->writeln("Project Name: <info>$projectName</info>");
        $this->stdErr->writeln("Project ID: <info>$projectId</info>");
        $this->stdErr->writeln("Project Environment: <info>$projectEnv</info>\n");

        // Have the user verify the info.
        $dialog = $this->getHelper('dialog');
        if (!$dialog->askConfirmation(
          $output,
          'Is this information correct? <question>[Y,n]</question>',
          true
        )) {
            $this->stdErr->writeln("<error>Environment build cancelled</error>");
            return;
        }

        $this->platformSetup($projectId, $projectEnv);
        $this->vagrantSetup();
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
            // Do nothing for now.
            // @todo: cd into the project folder and run platform project:build
            return;
        }

        $process = new Process($platformCommand);
        $this->runProcess($process);
    }

    /**
     * Runs the Vagrant command to start the virtual machine.
     */
    protected function vagrantSetup() {
        if (is_file(CLI_ROOT . '/.vagrant/machines/platformsh/virtualbox/action_provision')) {
            $this->stdErr->writeln("The virtual machine will <info>be rebuilt</info>");
            $vagrantCommand = 'vagrant reload --provision';
        } else {
            $this->stdErr->writeln("The virtual machine will <info>be created</info>");
            $vagrantCommand = 'vagrant up';
        }

        $process = new Process($vagrantCommand);
        $this->runProcess($process);
    }

    /**
     * @param \Symfony\Component\Process\Process $process
     */
    protected function runProcess(Process $process) {
        $process->setTimeout(null);
        $process->setIdleTimeout(60);
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
