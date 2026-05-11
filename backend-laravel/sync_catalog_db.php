<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Producto;
use App\Support\ProductoCatalog;

$catalog = ProductoCatalog::all();
$validKeys = [];
foreach ($catalog as $item) {
    if (!empty($item['image_url'])) {
        $validKeys[] = ProductoCatalog::itemKey($item);
    }
}

$allProducts = Producto::all();
$deactivated = 0;
$activated = 0;

foreach ($allProducts as $p) {
    $key = ProductoCatalog::productKey($p->referencia, $p->color, $p->tipo);
    if (!in_array($key, $validKeys)) {
        $p->activo = 0;
        $p->save();
        $deactivated++;
    } else {
        $p->activo = 1;
        $p->save();
        $activated++;
    }
}

echo "Inactivos: $deactivated\n";
echo "Activos: $activated\n";
