<?php

namespace App\Exports;

use App\Exports\sheets\EtudiantsSheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TDBEtudiantsExport implements WithMultipleSheets
{
    use Exportable;
    protected $TDBEtudiants;

	public function __construct(array $datas)
	{
		$this->EtudiantsDatas = $datas['TDBEtudiants'];
	}
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new EtudiantsSheetExport($this->EtudiantsDatas);
        return $sheets;
    }
}