<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$leyes = [
    [
        'id' => 1,
        'gaceta_oficial' => 'Gaceta Oficial Edición Extra',
        'link_oficial' => 'http://www.gacetaoficialdebolivia.gob.bo/normas/buscar/1942-12-08'
    ],
    [
        'id' => 2,
        'gaceta_oficial' => 'Gaceta Oficial 2872',
        'link_oficial' => 'http://www.gacetaoficialdebolivia.gob.bo/normas/verGratis/28591'
    ],
    [
        'id' => 3,
        'gaceta_oficial' => 'Gaceta Oficial 1863',
        'link_oficial' => 'http://www.gacetaoficialdebolivia.gob.bo/normas/verGratis/19515'
    ],
    [
        'id' => 4,
        'gaceta_oficial' => 'Gaceta Oficial 581',
        'link_oficial' => 'http://www.gacetaoficialdebolivia.gob.bo/normas/verGratis/145887'
    ],
    [
        'id' => 5,
        'gaceta_oficial' => 'Gaceta Oficial 457',
        'link_oficial' => 'http://www.gacetaoficialdebolivia.gob.bo/normas/verGratis/144670'
    ],
    [
        'id' => 6,
        'gaceta_oficial' => 'Gaceta Oficial 664',
        'link_oficial' => 'http://www.gacetaoficialdebolivia.gob.bo/normas/verGratis/146820'
    ],
    [
        'id' => 7,
        'gaceta_oficial' => 'Gaceta Oficial 0113',
        'link_oficial' => 'http://www.gacetaoficialdebolivia.gob.bo/normas/verGratis/138137'
    ]
];

foreach ($leyes as $l) {
    App\Models\Ley::where('id', $l['id'])->update([
        'gaceta_oficial' => $l['gaceta_oficial'],
        'link_oficial'   => $l['link_oficial']
    ]);
}
echo "Leyes actualizadas con links de la Gaceta Oficial de Bolivia.";
