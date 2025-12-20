<?php

namespace Helpers;

use Dotenv\Dotenv;

class DotenvLoader
{
    public static function load($path)
    {
        if (file_exists($path . '/.env')) {
            $dotenv = Dotenv::createImmutable($path);
            $dotenv->safeLoad();
        }
    }
}
