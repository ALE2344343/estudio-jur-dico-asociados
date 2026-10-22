<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\UploadedFile;

try {
    $file = UploadedFile::fake()->create('documento.pdf', 100);
    $path = $file->store('documentos', 'public');
    echo "Success: " . $path;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
