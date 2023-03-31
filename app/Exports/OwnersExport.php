<?php

namespace App\Exports;

use App\Models\Owner;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;

class OwnersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithEvents, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Owner::all();
    }

    /**
     * @param Owner $owner
     * @return array
     */
    public function map($owner): array
    {
        return [
            $owner->id,
            $owner->created_at,
            $owner->updated_at,
            $owner->sisteco_id,
            $owner->first_name,
            $owner->last_name,
            $owner->email,
            $owner->business_name,
            $owner->vat_number,
            $owner->fiscal_code,
            $owner->phone,
            $owner->{'addr:street'},
            $owner->{'addr:housenumber'},
            $owner->{'addr:city'},
            $owner->{'addr:postcode'},
            $owner->{'addr:province'},
            $owner->{'addr:locality'},
            $owner->cadastralParcels->count(),
            $owner->cadastralParcels->sum('estimated_value'),
        ];
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
            'Localitá',
            'Num TOT',
            'Val TOT',
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
