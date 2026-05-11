<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Producto;
use App\Support\ProductoCatalog;

$prods = ProductoCatalog::applyCatalogFilter(Producto::where('activo', 1), true)
    ->orderBy('referencia')
    ->orderBy('color')
    ->take(20)
    ->get();

echo "Products on first page:\n";
foreach ($prods as $p) {
    echo "ID: {$p->id} | Ref: {$p->referencia} | Color: {$p->color} | Tipo: {$p->tipo} | Img: " . substr($p->imagen, 0, 50) . "...\n";
}
