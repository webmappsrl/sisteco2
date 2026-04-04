<?php

namespace App\Exports;

use App\Models\Catalog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CatalogTypePricesExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    /** @var list<string> */
    private const PENDENZE = ['A', 'B', 'C'];

    /** @var list<int> */
    private const TRASPORTI = [1, 2, 3];

    public function __construct(private Catalog $catalog)
    {
    }

    /**
     * Stesso layout di «Tabella_tipologie_intervento»: una riga per combinazione pendenza/trasporto.
     */
    public function headings(): array
    {
        return ['N. INTERVENTO', 'PENDENZA', 'TRASPORTO', 'CODICE', 'PREZZO'];
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{0: string|int, 1: string, 2: int, 3: string, 4: float|null}>
     */
    public function collection()
    {
        $rows = collect();

        $this->catalog->catalogTypes()
            ->orderBy('cod_int')
            ->get()
            ->each(function ($type) use ($rows) {
                $prices = $type->prices ?? [];
                $cod = $type->cod_int;
                foreach (self::PENDENZE as $pendenza) {
                    foreach (self::TRASPORTI as $trasporto) {
                        $chiave = $pendenza.'.'.$trasporto;
                        $codice = $cod.'.'.$pendenza.'.'.$trasporto;
                        $rows->push([
                            $cod,
                            $pendenza,
                            $trasporto,
                            $codice,
                            array_key_exists($chiave, $prices) ? (float) $prices[$chiave] : null,
                        ]);
                    }
                }
            });

        return $rows;
    }
}
