<?php

namespace App\Imports;

use App\Entitie;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EntitiesImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        //dd($row);
        /* return new Entitie([
            //
        ]); */ 

        /* return array(
            'ref' => $row[0],
            'entity_type' => $row[1],
            'name' => $row[2],
        ); */
    }
}
