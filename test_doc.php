<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $doc = App\Models\Documento::create(['codigo'=>'DOC-998', 'nombre'=>'Test2', 'tipo'=>'Demanda', 'subido_por'=>1, 'caso_id'=>'']);
    echo "Success: " . $doc->id;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
