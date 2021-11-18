<?php

 // Autoload files using Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use Mtabe\Stantanizer;

$query = "update users set last_name = 'last name' WHERE first_name = '#P{name}' AND id = '#P{id}'";

$response = Stantanizer::sanitize($query);

echo $response;
