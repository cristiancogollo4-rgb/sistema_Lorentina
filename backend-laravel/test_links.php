<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Producto;

$testProducts = [
    'Plana' => Producto::where('referencia', '1006')->where('activo', 1)->first(),
    'Plataforma' => Producto::where('referencia', 'like', 'Z%')->where('activo', 1)->first()
];

foreach ($testProducts as $type => $p) {
    if (!$p) {
        echo "$type: NOT FOUND\n";
        continue;
    }
    
    $url = $p->imagen_src;
    echo "Testing $type (Ref: {$p->referencia}):\n";
    echo "  - Original URL: {$p->imagen}\n";
    echo "  - Transformed URL: $url\n";
    
    // Check if URL is accessible
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  - HTTP Status: $status\n";
    if ($status === 200) {
        echo "  - Result: SUCCESS\n";
    } else {
        echo "  - Result: FAILED\n";
    }
    echo "------------------\n";
}
