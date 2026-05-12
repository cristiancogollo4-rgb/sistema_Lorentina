<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ url('/productos') }}</loc>
        <priority>0.9</priority>
    </url>
    @foreach($productos as $producto)
    <url>
        <loc>{{ route('productos.show', $producto->id) }}</loc>
        <lastmod>{{ $producto->updated_at ? $producto->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <priority>0.8</priority>
    </url>
    @endforeach
</urlset>
