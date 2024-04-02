<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AfExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'AF',
            'ETUDIANT',
            'Statut étudiant',
            'Groupe étudiant',
            'Type',
            'nb séance',
            'nb heure',
            'Intitulé',
            'date',
            'Motif',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'B' => 100,
            'C' => 100,
            'D' => 100,
        ];
    }
}

