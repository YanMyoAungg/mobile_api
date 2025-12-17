<?php
include("../vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\UsersTable;
use Helpers\HTTP;

$id = $_GET['id'];
$role = $_GET['role'];

$table = new UsersTable(new Mysql);
$table->changeRole($id, $role);

HTTP::redirect('/admin.php');
