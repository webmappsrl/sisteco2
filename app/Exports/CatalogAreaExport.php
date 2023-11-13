<?php

namespace App\Exports;

use App\Models\CatalogArea;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;

class CatalogAreaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithEvents, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return CatalogArea::all();
    }
    /**
     * @param CatalogArea $area
     * @return array
     */
    public function map($area): array
    {

        $surface = DB::table('catalog_areas')
        ->select(DB::raw('ST_Area(geometry) as area'))
        ->where('id', $area->id)
        ->value('area');
        $surface_ha = $surface / 10000;

        return [
            $area->id,
            $area->catalogType->name,
            $area->catalogType->cod_int,
            $surface_ha,
            $area->hiking_routes_length,
            $area->estimated_value,
            $area->catalog_estimate['general']['platform_net_price'],
        ];
    }

    public function headings(): array
    {
        //create an heading for each column of the model renaming the columns
        return [
            'ID',
            'Intervento',
            'Codice Intervento',
            'Superficie (ha)',
            'Sentieri (m)',
            'Stima (€)',
            'Piattaforma (€)'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:S1')->getFont()->setSize(15)->setBold(true);
            },
        ];
    }
}
