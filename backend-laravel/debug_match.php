<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Producto;
use App\Support\ProductoCatalog;

$p = Producto::where('referencia', '1006')->first();
if ($p) {
    echo "DB: Ref: [{$p->referencia}] Color: [{$p->color}] Tipo: [{$p->tipo}]\n";
    $item = ProductoCatalog::find($p->referencia, $p->color, $p->tipo);
    if ($item) {
        echo "Catalog Match Found!\n";
        echo "Image URL: " . ($item['image_url'] ?? 'NULL') . "\n";
    } else {
        echo "Catalog Match NOT Found.\n";
        // Check why
        echo "Normalized Key DB: " . ProductoCatalog::productKey($p->referencia, $p->color, $p->tipo) . "\n";
    }
} else {
    echo "Product 1006 not found in DB.\n";
}
