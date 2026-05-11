<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Producto;

$duplicates = Producto::query()
    ->select('imagen', \DB::raw('count(*) as total'))
    ->whereNotNull('imagen')
    ->groupBy('imagen')
    ->having('total', '>', 1)
    ->orderByDesc('total')
    ->take(10)
    ->get();

foreach ($duplicates as $d) {
    echo "Image: {$d->imagen} | Total Products: {$d->total}\n";
    $prods = Producto::where('imagen', $d->imagen)->get(['referencia', 'color']);
    foreach ($prods as $p) {
        echo "  - Ref: {$p->referencia} | Color: {$p->color}\n";
    }
}
