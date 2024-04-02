<?php

namespace App\Exports;

use App\Exports\sheets\EtudiantsSheetExport;
use App\Exports\sheets\PresencesSheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TDBPresenceExport implements WithMultipleSheets
{
    use Exportable;
    protected $TDBPresence;

	public function __construct(array $datas)
	{
		$this->PresenceDatas = $datas['TDBPresence'];
	}
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new PresencesSheetExport($this->PresenceDatas);
        return $sheets;
    }
}