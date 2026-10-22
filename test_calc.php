<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = new App\Http\Controllers\CalculadoraController();
$re = new ReflectionMethod($c, 'calcularFiniquito');
$re->setAccessible(true);
$res = $re->invoke($c, '2021-01-17', '2023-12-31', 1800, 'despido_injustificado', null);
echo json_encode($res, JSON_PRETTY_PRINT);
