<?php

namespace Platformsh\Vagrant\Config;

class ProjectConfig extends Config
{
    public function __construct()
    {
        if (file_exists(CLI_ROOT . '/config.yml')) {
            parent::__construct(CLI_ROOT . '/config.yml');
        }
    }
}
