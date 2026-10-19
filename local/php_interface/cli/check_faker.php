<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$faker = Faker\Factory::create('ru_RU');
echo $faker->name() . "\n";
echo $faker->company() . "\n";
echo $faker->realText(100) . "\n";
echo $faker->randomFloat(2, 100, 50000) . "\n";