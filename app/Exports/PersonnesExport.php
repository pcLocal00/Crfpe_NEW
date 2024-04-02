<?php

namespace App\Exports;

use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;

class PersonnesExport implements FromView
{
    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     return Contact::all();
    // }

    public function view(): View
    {
        return view('exports.personnes', [
            'personnes' => Contact::all()
        ]);
    }
}
