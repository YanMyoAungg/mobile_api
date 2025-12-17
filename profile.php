<?php

include("vendor/autoload.php");

use Helpers\Auth;

// $auth = Helpers\Auth::check();
$auth = Auth::check();


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">

</head>

<body>
    <div class="container" style="max-width: 800px;">
        <h1 class="h3 mt-4 mb3">Profile</h1>

        <?php if ($auth->photo) : ?>
            <img class="img-thumbnail mb-3" src="actions/photos/<?= $auth->photo ?>" alt="Profile Picture" width="200">
        <?php endif ?>

        <form action="actions/upload.php" method="post" enctype="multipart/form-data" class="input-group my-4">

            <input type="file" name="photo" class="form-control">
            <button class="btn btn-secondary">Upload</button>

        </form>

        <ul class="list-group mb-4">
            <li class="list-group-item">Name: <?= $auth->name ?></li>
            <li class="list-group-item">Email: <?= $auth->email ?></li>
            <li class="list-group-item">phone: <?= $auth->phone ?></li>
            <li class="list-group-item">address: <?= $auth->address ?></li>
        </ul>
        <form action="actions/logout.php" method="POST">
            <button name="logout" class="btn btn-danger">Logout</button>
        </form>
    </div>
</body>

</html>