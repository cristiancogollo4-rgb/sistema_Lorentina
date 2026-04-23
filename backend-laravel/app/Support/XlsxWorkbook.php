<?php

namespace App\Support;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class XlsxWorkbook
{
    private ZipArchive $zip;
    private string $path;

    /** @var array<int, string> */
    private array $sharedStrings = [];

    /** @var array<string, string> */
    private array $sheetPaths = [];

    public function __construct(string $path)
    {
        $this->path = $path;

        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('La extensión ZIP de PHP no está habilitada. Activa ZipArchive para importar archivos Excel.');
        }

        $this->zip = new ZipArchive();

        if ($this->zip->open($this->path) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo Excel.');
        }

        $this->sharedStrings = $this->loadSharedStrings();
        $this->sheetPaths = $this->loadSheetPaths();
    }

    public function __destruct()
    {
        $this->zip->close();
    }

    /**
     * @return array<int, string>
     */
    public function getSheetNames(): array
    {
        return array_keys($this->sheetPaths);
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function getSheetRows(string $sheetName): array
    {
        return array_values($this->getSheetRowsIndexed($sheetName));
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function getSheetRowsIndexed(string $sheetName): array
    {
        if (! isset($this->sheetPaths[$sheetName])) {
            return [];
        }

        $xml = $this->readXml($this->sheetPaths[$sheetName]);

        if (! isset($xml->sheetData->row)) {
            return [];
        }

        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $currentRow = [];
            $rowNumber = (int) ($row['r'] ?? 0);

            foreach ($row->c as $cell) {
                $reference = (string) ($cell['r'] ?? '');
                $columnIndex = $this->columnIndexFromReference($reference);
                $currentRow[$columnIndex] = $this->cellValue($cell);
            }

            if ($currentRow !== []) {
                ksort($currentRow);
                $rows[$rowNumber > 0 ? $rowNumber : count($rows) + 1] = $currentRow;
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array{start_row:int, end_row:int, total:int}>
     */
    public function getSheetSumRanges(string $sheetName, string $column = 'B'): array
    {
        if (! isset($this->sheetPaths[$sheetName])) {
            return [];
        }

        $xml = $this->readXml($this->sheetPaths[$sheetName]);

        if (! isset($xml->sheetData->row)) {
            return [];
        }

        $normalizedColumn = strtoupper($column);
        $ranges = [];

        foreach ($xml->sheetData->row as $row) {
            foreach ($row->c as $cell) {
                $reference = strtoupper((string) ($cell['r'] ?? ''));

                if (! str_starts_with($reference, $normalizedColumn)) {
                    continue;
                }

                if (! isset($cell->f, $cell->v)) {
                    continue;
                }

                $formula = strtoupper((string) $cell->f);

                if (
                    preg_match(
                        '/SUM\(' . preg_quote($normalizedColumn, '/') . '(\d+):' . preg_quote($normalizedColumn, '/') . '(\d+)\)/',
                        $formula,
                        $matches
                    ) !== 1
                ) {
                    continue;
                }

                $ranges[] = [
                    'start_row' => (int) $matches[1],
                    'end_row' => (int) $matches[2],
                    'total' => is_numeric((string) $cell->v) ? (int) $cell->v : 0,
                ];
            }
        }

        return $ranges;
    }

    private function readXml(string $innerPath): SimpleXMLElement
    {
        $content = $this->zip->getFromName($innerPath);

        if ($content === false) {
            throw new RuntimeException("No se pudo leer {$innerPath} del Excel.");
        }

        return simplexml_load_string($content);
    }

    /**
     * @return array<int, string>
     */
    private function loadSharedStrings(): array
    {
        $index = $this->zip->locateName('xl/sharedStrings.xml');

        if ($index === false) {
            return [];
        }

        $xml = $this->readXml('xl/sharedStrings.xml');
        $strings = [];

        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            $text = '';
            foreach ($si->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @return array<string, string>
     */
    private function loadSheetPaths(): array
    {
        $workbook = $this->readXml('xl/workbook.xml');
        $relations = $this->readXml('xl/_rels/workbook.xml.rels');

        $relationshipMap = [];
        foreach ($relations->Relationship as $relation) {
            $relationshipMap[(string) $relation['Id']] = 'xl/' . ltrim((string) $relation['Target'], '/');
        }

        $namespaces = $workbook->getNamespaces(true);
        $workbook->registerXPathNamespace('main', $namespaces[''] ?? 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', $namespaces['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $paths = [];
        foreach ($workbook->xpath('//main:sheets/main:sheet') ?: [] as $sheet) {
            $attributes = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $sheetId = (string) ($attributes['id'] ?? '');
            $sheetName = (string) ($sheet['name'] ?? '');

            if ($sheetId !== '' && $sheetName !== '' && isset($relationshipMap[$sheetId])) {
                $paths[$sheetName] = $relationshipMap[$sheetId];
            }
        }

        return $paths;
    }

    private function columnIndexFromReference(string $reference): int
    {
        if ($reference === '') {
            return 0;
        }

        preg_match('/[A-Z]+/i', $reference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private function cellValue(SimpleXMLElement $cell): mixed
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        $value = isset($cell->v) ? (string) $cell->v : '';

        if ($type === 's') {
            return $this->sharedStrings[(int) $value] ?? '';
        }

        return $value;
    }
}
