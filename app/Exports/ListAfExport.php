<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;

class ListAfExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            // 'ID',
            // 'Text',
            // 'Date',
            'Schedule',
            // 'Duration',
            'Badge'
        ];
    }

    public function collection()
    {
        // $rows = [];

        // $json = '[
        //     {"id":"etudiant 2715","text":"Adeline THUILLIEZ","state":{"opened":false,"checkbox_disabled":false},"icon":"fa fa-user text-dark","parent":"#"},
        //     {"id":"D20012715","text":"07\/04\/2022","state":{"opened":false,"checkbox_disabled":false},"icon":"fa fa-calendar text-primary","parent":"etudiant 2715"},
        //     {"id":"2174-2715","li_attr":{"name":"schedule","schedule_id":2174,"member_id":"2715"},"text":"S\u00e9ance (2174) : COMP 09h00 - 12h30 <span class=\"text-success\">(03h30)<\/span><span class=\"badge rounded-pill bg-light text-dark\">Non renseign\u00e9<\/span>","state":{"opened":false,"checkbox_disabled":false},"icon":"fa fa-folder text-dark","parent":"D20012715"},
        //     {"id":"2175-2715","li_attr":{"name":"schedule","schedule_id":2175,"member_id":"2715"},"text":"S\u00e9ance (2175) : COMP 13h15 - 16h45 <span class=\"text-success\">(03h30)<\/span><span class=\"badge rounded-pill bg-light text-dark\">Non renseign\u00e9<\/span>","state":{"opened":false,"checkbox_disabled":false},"icon":"fa fa-folder text-dark","parent":"D20012715"},
        //     {"id":"D20022715","text":"08\/04\/2022","state":{"opened":false,"checkbox_disabled":false},"icon":"fa fa-calendar text-primary","parent":"etudiant 2715"},
        //     {"id":"2176-2715","li_attr":{"name":"schedule","schedule_id":2176,"member_id":"2715"},"text":"S\u00e9ance (2176) : COMP 09h00 - 12h30 <span class=\"text-success\">(03h30)<\/span><span class=\"badge rounded-pill bg-light text-dark\">Non renseign\u00e9<\/span>","state":{"opened":false,"checkbox_disabled":false},"icon":"fa fa-folder text-dark","parent":"D20022715"},
        //     {"id":"2177-2715","li_attr":{"name":"schedule","schedule_id":2177,"member_id":"2715"},"text":"S\u00e9ance (2177) : COMP 13h15 - 16h45 <span class=\"text-success\">(03h30)<\/span><span class=\"badge rounded-pill bg-light text-dark\">Non renseign\u00e9<\/span>","state":{"opened":false,"checkbox_disabled":false},"icon":"fa fa-folder text-dark","parent":"D20022715"}]';

        // $data = json_decode($this->data, true);
        $jsonData = $this->data->getContent();
        // dd($jsonData);
        $data = json_decode($jsonData, true);
            // dd($data);
        foreach ($data as $item) {
            // dd($item);
            $rows[] = [
                // $item['id'],
                // $item['text'],
                // substr($item['id'], 1, 10),
                $item['li_attr']['schedule_id']  ?? null,
                // substr($item['text'], strpos($item['text'], ':') + 2, 5),
                strip_tags($item['text'])
            ];

            // foreach ($item['children']  as $child) {
            //     $rows[] = [
            //         '',
            //         '',
            //         substr($child['id'], 1, 10),
            //         $child['li_attr']['schedule_id'] ?? null,
            //         substr($child['text'], strpos($child['text'], '(') + 1, 5),
            //         strip_tags($child['text'])
            //     ];
            // }
        }
        // dd($rows);

        return collect($rows);
    }

}

