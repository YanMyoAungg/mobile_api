<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .wrap {
            width: 100%;
            max-width: 400px;
            margin: 40px auto;
        }
    </style>
</head>

<body class="text-center">
    <div class="wrap">
        <h1 class="h3 mb-3">Register</h1>
        <?php if (isset($_GET['error'])) : ?>
            <div class="alert alert-warning">
                Cannot create account. Please try again.
            </div>
        <?php endif ?>
        <form action="actions/create.php" method="post">
            <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="text" name="phone" class="form-control mb-2" placeholder="Phone" required>
            <input type="text" name="address" class="form-control mb-2" placeholder="Address" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
            <button type="submit" class="w-100 btn btn-lg btn-danger">Register</button>
        </form>
        <br>
        <a href="index.php">Login</a>
    </div>
</body>

</html>

<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
</head>

<body>
    <form action="actions/create.php" class="container mb-4" style="max-width: 600px;" method="POST">
        <h1 class="text-center text-danger mt-5 mb-5">Register Page</h1>
        <input type="text" name="name" placeholder="Enter Your Username" class="form-control" required><br>

        <input type="text" name="email" placeholder="Email" class="form-control" required><br>

        <input type="text" name="phone" placeholder="Phone" class="form-control" required><br>

        <input type="text" name="address" placeholder="Address" class="form-control" required><br>

        <input type="text" name="password" placeholder="Enter Your Password" class="form-control" required><br>

        <button name="button" class="btn btn-danger form-control">Register</button>

        <a href="index.php" class="mx-auto">Login</a>
    </form>
</body>

</html> -->