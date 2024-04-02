<?php

namespace App\Exports;

use App\Exports\sheets\IntervenantsSheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TDBIntervenantsExport implements WithMultipleSheets
{
    use Exportable;
    protected $TDBIntervenants;

	public function __construct(array $datas)
	{
		$this->IntervenantsDatas = $datas['TDBIntervenants'];
	}
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new IntervenantsSheetExport($this->IntervenantsDatas);
        return $sheets;
    }
}