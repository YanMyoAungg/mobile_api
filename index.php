<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
</head>

<?php
session_start();
$lockoutRemaining = 0;
if (isset($_SESSION['lockout_until'])) {
    $lockoutRemaining = max(0, $_SESSION['lockout_until'] - time());
}
?>

<body>
    <?php if (isset($_GET['suspended'])) : ?>
        <div class="alert alert-danger">
            Account suspended
        </div>
    <?php endif ?>
    <form action="actions/login.php" class="container" style="max-width: 600px;" method="POST">
        <h1 class="text-center text-danger mt-5 mb-5">Login Page</h1>

        <?php if (isset($_GET["register"])) : ?>
            <div class="alert alert-info">
                Register success, Please Login
            </div>
        <?php endif ?>

        <?php if (isset($_GET['auth']) && $_GET['auth'] === 'fail') : ?>
            <div class="alert alert-warning">
                Incorrect email or password
            </div>
        <?php endif ?>

        <?php if (isset($_GET['auth']) && $_GET['auth'] === 'locked') : ?>
            <div class="alert alert-danger">
                Too many failed attempts. Login disabled temporarily.
            </div>
        <?php endif ?>

        <input type="text" name="email" placeholder="Enter Your Username" class="form-control" required><br>
        <input type="password" name="password" placeholder="Enter Your Password" class="form-control" required><br>
        <button id="loginBtn" name="button" class="btn btn-danger form-control">Login</button>
    </form>
    <script>
        (function() {
            var remaining = <?php echo (int)$lockoutRemaining; ?>;
            var btn = document.getElementById('loginBtn');
            if (remaining > 0 && btn) {
                btn.disabled = true;
                var update = function() {
                    if (remaining <= 0) {
                        btn.disabled = false;
                        btn.textContent = 'Login';
                        clearInterval(interval);
                        return;
                    }
                    var mins = Math.floor(remaining / 60);
                    var secs = remaining % 60;
                    btn.textContent = 'Locked (' + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0') + ')';
                    remaining--;
                };
                update();
                var interval = setInterval(update, 1000);
            }
        })();
    </script>
</body>

</html>

<?php
// session_start();
// if (isset($_POST['button'])) {
//     $user = $_POST['user'];
//     $_SESSION['user'] = $user;
//     header('location: actionlogin.php');
//     exit();
// }
?>