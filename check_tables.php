<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make("Illuminate\Contracts\Console\Kernel");
$kernel->bootstrap();

$connection = DB::connection();
$tables = $connection->select("SHOW TABLES");
echo "=== Tables in Database ===\n";
foreach($tables as $table) {
    echo current((array)$table) . "\n";
}
