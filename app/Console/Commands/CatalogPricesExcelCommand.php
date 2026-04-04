<?php

namespace App\Console\Commands;

use App\Exports\CatalogTypePricesExport;
use App\Models\Catalog;
use App\Models\CatalogType;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CatalogPricesExcelCommand extends Command
{
    protected $signature = 'sisteco:catalog-prices
                            {catalog_id : ID del catalogo}
                            {action : export o import}
                            {path : Percorso assoluto del file .xlsx o .xls}';

    protected $description = 'Esporta o importa il prezzario (campo prices dei catalog_types) in formato tabella tipologie o in formato matrice legacy';

    public function handle(): int
    {
        $catalogId = (int) $this->argument('catalog_id');
        $action = strtolower(trim($this->argument('action')));
        $path = $this->argument('path');

        $catalog = Catalog::with('catalogTypes')->find($catalogId);
        if (! $catalog) {
            $this->error("Catalogo con id {$catalogId} non trovato.");

            return self::FAILURE;
        }

        return match ($action) {
            'export' => $this->doExport($catalog, $path),
            'import' => $this->doImport($catalog, $path),
            default => $this->invalidAction($action),
        };
    }

    private function invalidAction(string $action): int
    {
        $this->error("Azione non valida \"{$action}\". Usa export o import.");

        return self::FAILURE;
    }

    private function doExport(Catalog $catalog, string $path): int
    {
        $dir = dirname($path);
        if (! is_dir($dir) || ! is_writable($dir)) {
            $this->error("La cartella di destinazione non esiste o non è scrivibile: {$dir}");

            return self::FAILURE;
        }

        $binary = Excel::raw(new CatalogTypePricesExport($catalog), \Maatwebsite\Excel\Excel::XLSX);
        if (file_put_contents($path, $binary) === false) {
            $this->error("Impossibile scrivere il file: {$path}");

            return self::FAILURE;
        }

        $types = $catalog->catalogTypes->count();
        $righe = $types * 9;
        $this->info("Esportate {$righe} righe ({$types} tipologie × 9 combinazioni pendenza/trasporto) in {$path}");
        $this->line('Colonne: N. INTERVENTO, PENDENZA, TRASPORTO, CODICE, PREZZO (come Tabella_tipologie_intervento).');

        return self::SUCCESS;
    }

    private function doImport(Catalog $catalog, string $path): int
    {
        if (! is_readable($path)) {
            $this->error("File non leggibile: {$path}");

            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        if ($rows === []) {
            $this->error('Il foglio è vuoto.');

            return self::FAILURE;
        }

        $header = array_shift($rows);
        $header = array_map(fn ($h) => is_string($h) ? trim($h) : $h, $header);

        $map = $this->buildCanonicalHeaderMap($header);

        if (isset($map['catalog_type_id'])) {
            return $this->importWideMatrix($catalog, $rows, $header, $map['catalog_type_id']);
        }

        if (isset($map['n_intervento'], $map['pendenza'], $map['trasporto'], $map['prezzo'])) {
            return $this->importTabellaTipologie($catalog, $rows, $map);
        }

        $this->error('Intestazioni non riconosciute. Usa il formato tabella (N. INTERVENTO, PENDENZA, TRASPORTO, CODICE, PREZZO) oppure la matrice con colonna catalog_type_id.');

        return self::FAILURE;
    }

    /**
     * @param  array<int, string|null>  $header
     * @return array<string, int>
     */
    private function buildCanonicalHeaderMap(array $header): array
    {
        $out = [];
        foreach ($header as $i => $h) {
            if ($h === null || $h === '') {
                continue;
            }
            $key = $this->canonicalHeaderKey((string) $h);
            if ($key !== null) {
                $out[$key] = $i;
            }
        }

        return $out;
    }

    private function canonicalHeaderKey(string $label): ?string
    {
        $n = strtolower(trim(preg_replace('/\s+/u', ' ', $label)));

        return match ($n) {
            'n. intervento', 'n intervento', 'n° intervento', 'n°. intervento', 'numero intervento' => 'n_intervento',
            'pendenza' => 'pendenza',
            'trasporto' => 'trasporto',
            'codice' => 'codice',
            'prezzo' => 'prezzo',
            'catalog_type_id' => 'catalog_type_id',
            'cod_int' => 'cod_int',
            'name' => 'name',
            default => null,
        };
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<string, int>  $map
     */
    private function importTabellaTipologie(Catalog $catalog, array $rows, array $map): int
    {
        $iN = $map['n_intervento'];
        $iP = $map['pendenza'];
        $iT = $map['trasporto'];
        $iPrezzo = $map['prezzo'];
        $iCodice = $map['codice'] ?? null;

        /** @var array<int, array{model: CatalogType, prices: array<string, float>}> $touched */
        $touched = [];
        $skipped = 0;

        foreach ($rows as $rowIndex => $row) {
            $rawN = $row[$iN] ?? null;
            if ($rawN === null || $rawN === '') {
                $skipped++;

                continue;
            }

            $codInt = $this->normalizeCodInt($rawN);
            $pendenza = strtoupper(trim((string) ($row[$iP] ?? '')));
            $trasportoRaw = $row[$iT] ?? null;

            if ($pendenza === '' || $trasportoRaw === null || $trasportoRaw === '') {
                $skipped++;

                continue;
            }

            $trasporto = is_numeric($trasportoRaw) ? (string) (int) $trasportoRaw : trim((string) $trasportoRaw);
            $priceKey = $pendenza.'.'.$trasporto;

            $type = CatalogType::where('catalog_id', $catalog->id)
                ->where('cod_int', $codInt)
                ->first();

            if (! $type) {
                $this->warn('Riga '.($rowIndex + 2).": nessun catalog_type con cod_int \"{$codInt}\" in questo catalogo — ignorata.");
                $skipped++;

                continue;
            }

            if ($iCodice !== null && isset($row[$iCodice])) {
                $codiceCell = $row[$iCodice];
                if (is_string($codiceCell) && $codiceCell !== '' && ! str_starts_with($codiceCell, '=')) {
                    $expected = $codInt.'.'.$pendenza.'.'.$trasporto;
                    $got = trim($codiceCell);
                    if ($got !== $expected && $got !== str_replace(' ', '', $expected)) {
                        $this->warn('Riga '.($rowIndex + 2).": CODICE \"{$got}\" diverso da \"{$expected}\" — si usa comunque pendenza/trasporto.");
                    }
                }
            }

            $parsed = $this->parseNumericCell($row[$iPrezzo] ?? null);
            if ($parsed === null) {
                $skipped++;

                continue;
            }

            if (! isset($touched[$type->id])) {
                $touched[$type->id] = [
                    'model' => $type,
                    'prices' => $type->prices ?? [],
                ];
            }
            $touched[$type->id]['prices'][$priceKey] = $parsed;
        }

        foreach ($touched as $item) {
            $item['model']->prices = $item['prices'];
            $item['model']->save();
        }

        $this->info('Aggiornati '.count($touched).' catalog_types (formato tabella). Righe non applicate (vuote o senza prezzo): '.$skipped.'.');

        return self::SUCCESS;
    }

    private function normalizeCodInt(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            $f = (float) $value;
            if (floor($f) === $f) {
                return (string) (int) $f;
            }

            return rtrim(rtrim(sprintf('%.10F', $f), '0'), '.');
        }

        return trim((string) $value);
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string|null>  $header
     */
    private function importWideMatrix(Catalog $catalog, array $rows, array $header, int $idIdx): int
    {
        $fixed = ['catalog_type_id', 'cod_int', 'name'];
        $priceColumnIndexes = [];
        foreach ($header as $colIdx => $name) {
            if ($name === null || $name === '') {
                continue;
            }
            if (in_array($name, $fixed, true)) {
                continue;
            }
            $priceColumnIndexes[$colIdx] = (string) $name;
        }

        if ($priceColumnIndexes === []) {
            $this->error('Nessuna colonna prezzo trovata (matrice: colonne oltre catalog_type_id, cod_int, name).');

            return self::FAILURE;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($rows as $rowIndex => $row) {
            if (! isset($row[$idIdx]) || $row[$idIdx] === null || $row[$idIdx] === '') {
                $skipped++;

                continue;
            }

            $typeId = (int) $row[$idIdx];
            $type = CatalogType::where('id', $typeId)
                ->where('catalog_id', $catalog->id)
                ->first();

            if (! $type) {
                $this->warn('Riga '.($rowIndex + 2).": catalog_type_id {$typeId} non trovato in questo catalogo — ignorata.");
                $skipped++;

                continue;
            }

            $prices = $type->prices ?? [];
            foreach ($priceColumnIndexes as $colIdx => $priceKey) {
                if (! array_key_exists($colIdx, $row)) {
                    continue;
                }
                $cell = $row[$colIdx];
                $parsed = $this->parseNumericCell($cell);
                if ($parsed === null) {
                    continue;
                }
                $prices[$priceKey] = $parsed;
            }

            $type->prices = $prices;
            $type->save();
            $updated++;
        }

        $this->info("Aggiornati {$updated} catalog_types (formato matrice). Righe saltate: {$skipped}.");

        return self::SUCCESS;
    }

    private function parseNumericCell(mixed $cell): ?float
    {
        if ($cell === null || $cell === '') {
            return null;
        }
        if (is_int($cell) || is_float($cell)) {
            return (float) $cell;
        }
        if (! is_string($cell)) {
            return null;
        }

        $s = trim(str_replace(["\xc2\xa0", ' ', '€'], '', $cell));
        if ($s === '') {
            return null;
        }

        $hasComma = str_contains($s, ',');
        $hasDot = str_contains($s, '.');
        if ($hasComma && $hasDot) {
            if (strrpos($s, ',') > strrpos($s, '.')) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } else {
                $s = str_replace(',', '', $s);
            }
        } elseif ($hasComma) {
            $s = str_replace(',', '.', $s);
        }

        return is_numeric($s) ? (float) $s : null;
    }
}
