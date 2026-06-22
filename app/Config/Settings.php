<?php

namespace Config;

use CodeIgniter\Settings\Config\Settings as BaseSettings;

class Settings extends BaseSettings
{
    public function __construct()
    {
        parent::__construct();

        // Avoid database-backed settings table dependency during test bootstrap.
        if (ENVIRONMENT === 'testing') {
            $this->handlers = ['array'];
        }
    }
}

