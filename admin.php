<?php
include("vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\UsersTable;
use Helpers\Auth;

$auth = Auth::check();
$table = new UsersTable(new Mysql);
$users = $table->getAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/bootstrap.bundle.min.js" defer></script>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <table class="table table-dark table-striped">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th></th>
                </tr>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?= $user->id ?></td>
                        <td><?= $user->name ?></td>
                        <td><?= $user->email ?></td>
                        <td><?= $user->phone ?></td>
                        <td>
                            <?php if ($user->role_id == 3) : ?>
                                <span class="badge bg-success">
                                    <?= $user->role ?>
                                </span>
                            <?php elseif ($user->role_id == 2) : ?>
                                <span class="badge bg-primary">
                                    <?= $user->role ?>
                                </span>
                            <?php else : ?>
                                <span class="badge bg-secondary">
                                    <?= $user->role ?>
                                </span>
                            <?php endif ?>
                        </td>
                        <td>
                            <div class="btn-group dropdown">
                                <?php if ($auth->role_id == 3) : ?>
                                    <a href="#" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Role</a>
                                    <div class="dropdown-menu dropdown-menu-dark">
                                        <a href="actions/role.php?role=1&id=<?= $user->id ?>" class="dropdown-item">User</a>
                                        <a href="actions/role.php?role=2&id=<?= $user->id ?>" class="dropdown-item">Manager</a>
                                        <a href="actions/role.php?role=3&id=<?= $user->id ?>" class="dropdown-item">Admin</a>
                                    </div>
                                <?php endif ?>

                                <?php if ($auth->role_id >= 2) : ?>
                                    <?php if ($user->suspended) : ?>
                                        <a href="actions/unsuspend.php?id=<?= $user->id ?>" class="btn btn-warning btn-sm">Ban</a>
                                    <?php else : ?>
                                        <a href="actions/suspend.php?id=<?= $user->id ?>" class="btn btn-outline-warning btn-sm">Ban</a>
                                    <?php endif ?>
                                    <a href="actions/delete.php?id=<?= $user->id ?>" class="btn btn-outline-danger btn-sm">Delete</a>
                                <?php endif ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </table>
            <a href="actions/logout.php">Logout</a>
        </div>

    </nav>
</body>

</html>