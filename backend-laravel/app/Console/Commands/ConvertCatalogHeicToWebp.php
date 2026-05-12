<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConvertCatalogHeicToWebp extends Command
{
    protected $signature = 'catalog:convert-heic-webp {--update-json : Reemplaza referencias HEIC por WebP en el catalogo JSON}';

    protected $description = 'Convierte imagenes HEIC del catalogo publico a WebP para evitar conversiones pesadas en el navegador.';

    public function handle(): int
    {
        if (! class_exists(\Imagick::class)) {
            $this->error('Imagick no esta instalado. Instala la extension con soporte HEIC/WebP para ejecutar esta conversion.');

            return self::FAILURE;
        }

        $catalogRoot = public_path('images/catalog');
        if (! File::isDirectory($catalogRoot)) {
            $this->error("No existe el directorio {$catalogRoot}.");

            return self::FAILURE;
        }

        $converted = [];
        $files = File::allFiles($catalogRoot);

        foreach ($files as $file) {
            if (strtolower($file->getExtension()) !== 'heic') {
                continue;
            }

            $source = $file->getPathname();
            $target = preg_replace('/\.heic$/i', '.webp', $source);

            if (! $target) {
                continue;
            }

            if (File::exists($target) && File::lastModified($target) >= File::lastModified($source)) {
                $converted[$this->relativeCatalogPath($source)] = $this->relativeCatalogPath($target);
                $this->line('Existe: ' . $this->relativeCatalogPath($target));
                continue;
            }

            try {
                $image = new \Imagick($source);
                $image->setImageFormat('webp');
                $image->setImageCompressionQuality(82);
                $image->stripImage();
                $image->writeImage($target);
                $image->clear();
                $image->destroy();

                $converted[$this->relativeCatalogPath($source)] = $this->relativeCatalogPath($target);
                $this->info('Convertida: ' . $this->relativeCatalogPath($target));
            } catch (\Throwable $exception) {
                $this->warn('No se pudo convertir ' . $this->relativeCatalogPath($source) . ': ' . $exception->getMessage());
            }
        }

        if ($this->option('update-json') && $converted !== []) {
            $this->updateCatalogJson($converted);
        }

        $this->info('Conversion terminada. Archivos WebP listos: ' . count($converted));

        return self::SUCCESS;
    }

    private function relativeCatalogPath(string $path): string
    {
        return str_replace('\\', '/', 'catalog/' . ltrim(str_replace(public_path('images/catalog'), '', $path), '\\/'));
    }

    /**
     * @param array<string, string> $converted
     */
    private function updateCatalogJson(array $converted): void
    {
        $path = database_path('seeders/data/productos_link_catalog.json');

        if (! File::exists($path)) {
            $this->warn('No se encontro el catalogo JSON para actualizar.');

            return;
        }

        $json = File::get($path);
        foreach ($converted as $source => $target) {
            $json = str_replace($source, $target, $json);
            $json = str_replace(basename($source), basename($target), $json);
        }

        File::put($path, $json);
        $this->info('Catalogo JSON actualizado para preferir WebP.');
    }
}

