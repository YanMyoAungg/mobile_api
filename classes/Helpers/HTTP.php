<?php

namespace Helpers;

class HTTP
{
    static $app_url = "http://localhost:8000";

    static function redirect($path, $q = "")
    {
        $url = static::$app_url . $path;
        if ($q)
            $url .= "?$q";

        header("location: $url");
        exit();
    }
}
