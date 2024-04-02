<?php

namespace App\Exports;

use App\Exports\sheets\ActivitesSheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TDBActivitesExport implements WithMultipleSheets
{
    use Exportable;
    protected $TDBActivites;

	public function __construct(array $datas)
	{
		$this->ActivitesDatas = $datas['TDBActivites'];
	}
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new ActivitesSheetExport($this->ActivitesDatas);
        return $sheets;
    }
}