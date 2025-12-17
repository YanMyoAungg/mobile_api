<?php

include("../vendor/autoload.php");

use Libs\Database\Mysql;

use Libs\Database\UsersTable;

use Faker\Factory as Faker;

$faker = Faker::create();
$table = new UsersTable(new Mysql);
echo "Starting data population...<br>";
for ($i = 0; $i < 20; $i++) {
    $table->insert([
        "name" => $faker->name,
        "email" => $faker->email,
        "phone" => $faker->phoneNumber,
        "address" => $faker->address,
        "password" => "password"
    ]);
}

echo "Done Data population.";

// $table = new UsersTable(new Mysql);
// $id = $table->insert([
//     "name" => "Alice",
//     "email" => "alice@gmail.com",
//     "phone" => "0999558",
//     "address" => "Some Address",
//     "password" => "password",
// ]);
// echo $id;
