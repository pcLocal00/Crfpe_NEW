<?php

namespace App\Exports;

use App\Exports\sheets\GlobalSheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EvolutionTasksExport implements WithMultipleSheets
{
    use Exportable;
    protected $TasksEvolution;

	public function __construct(array $datas)
	{
		$this->tasksDatas = $datas['TasksEvolution'];
	}
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new GlobalSheetExport($this->tasksDatas);
        return $sheets;
    }
}