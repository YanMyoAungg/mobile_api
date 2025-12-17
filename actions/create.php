<?php
include("../vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\UsersTable;
use Helpers\HTTP;

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$password = $_POST['password'] ?? '';

if (!$name || !$email || !$password) {
    HTTP::redirect('/register.php', 'error=missing');
}

$data = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'address' => $address,
    'password' => $password,
];

$table = new UsersTable(new Mysql());
try {
    $table->insert($data);
    HTTP::redirect('/index.php', 'register=success');
} catch (Exception $e) {
    // Could be duplicate email or DB error
    HTTP::redirect('/register.php', 'error=true');
}
