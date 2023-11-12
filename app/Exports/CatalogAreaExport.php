<?php

namespace App\Exports;

use App\Models\CatalogArea;
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
        return [
            $area->id,
        ];
    }

    public function headings(): array
    {
        //create an heading for each column of the model renaming the columns
        return [
            'ID',
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
