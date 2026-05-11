<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Producto;
use App\Support\ProductoCatalog;

$unmatched = Producto::where('activo', 1)->get()->filter(function($p) {
    return ProductoCatalog::find($p->referencia, $p->color, $p->tipo) === null;
});

echo "Unmatched products: " . $unmatched->count() . "\n";
foreach ($unmatched->take(10) as $p) {
    echo "Ref: {$p->referencia} | Color: {$p->color} | Tipo: {$p->tipo}\n";
}
