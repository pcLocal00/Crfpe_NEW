<?php

namespace App\Exports;

use App\Models\Entitie;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;

// class ClientsExport implements FromCollection
class ClientsExport implements FromView
{
    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     return Entitie::all();
    // }

    public function view(): View
    {
        return view('exports.clients', [
            'entities' => Entitie::all()
        ]);
    }
}
