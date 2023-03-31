<?php

namespace App\Exports;

use App\Models\Owner;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;

class OwnersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Owner::all();
    }

    public function headings(): array
    {
        //create an heading for each column of the model renaming the columns
        return [
            'ID',
            'Creato il ',
            'Modificato il',
            'Sisteco ID',
            'Nome',
            'Cognome',
            'Email',
            'Nome Azienda',
            'P.IVA',
            'Codice Fiscale',
            'Telefono',
            'Via',
            'Numero Civico',
            'Cittá',
            'CAP',
            'Provincia',
            'Localitá'


        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:Q1')->getFont()->setSize(15)->setBold(true);
            },
        ];
    }
}
