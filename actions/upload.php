<?php
include("../vendor/autoload.php");

use Helpers\Auth;
use Helpers\HTTP;
use Libs\Database\Mysql;
use Libs\Database\UsersTable;



$auth = Auth::check();
$table = new UsersTable(new Mysql);
$name = $_FILES['photo']['name'];
$tmp = $_FILES['photo']['tmp_name'];
$type = $_FILES['photo']['type'];

if ($type == "image/jpeg" or $type  == "image/png" or $type == "image/jpg") {
    $table = new UsersTable(new Mysql);
    $table->updatePhoto($name, $auth->id);
    $auth->photo = $name;
    move_uploaded_file($tmp, "photos/$name");
    HTTP::redirect("/profile.php");
} else {
    HTTP::redirect("/profile.php", "error=type");
}
