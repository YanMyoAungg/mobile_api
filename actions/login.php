<?php
session_start();
include("../vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\UsersTable;
use Helpers\HTTP;

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// initialize attempt tracking in session
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// check lockout
if (isset($_SESSION['lockout_until']) && time() < $_SESSION['lockout_until']) {
    // still locked; redirect back to login (UI will show remaining time)
    HTTP::redirect('/index.php?auth=locked');
}

$table = new UsersTable(new Mysql());
$user = $table->findByEmailAndPassword($email, $password);
if ($user) {
    // successful login: clear attempts and lockout
    unset($_SESSION['login_attempts']);
    unset($_SESSION['lockout_until']);

    if ($user->suspended) {
        HTTP::redirect("/index.php", "suspended=true");
    }
    $_SESSION['user'] = $user;
    HTTP::redirect("/profile.php");
} else {
    // failed attempt
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

    // if reached 5 consecutive failures, set 5 minute lockout
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['lockout_until'] = time() + (5 * 60); // 5 minutes
        // reset attempts counter after lockout
        $_SESSION['login_attempts'] = 0;
        HTTP::redirect('/index.php?auth=locked');
    }

    HTTP::redirect('/index.php?auth=fail');
}
// session_start();
// include("../vendor/autoload.php");

// use Libs\Database\MySQL;
// use Libs\Database\UsersTable;
// use Helpers\HTTP;

// $email = $_POST['email'];
// $password = md5($_POST['password']);
// $table = new UsersTable(new MySQL());
// $user = $table->findByEmailAndPassword($email, $password);
// if ($user) {
//     $_SESSION['user'] = $user;
//     HTTP::redirect("/profile.php");
// } else {
//     HTTP::redirect("/index.php", "incorrect=1");
// }
