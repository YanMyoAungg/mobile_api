<?php
include("../vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\UsersTable;
use Helpers\HTTP;

$id = $_GET['id'];
$table = new UsersTable(new Mysql);
$table->unsuspend($id);

HTTP::redirect('/admin.php');
