<?php

namespace App\Exports;

use App\Exports\sheets\GlobalSheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TasksExport implements WithMultipleSheets
{
    use Exportable;
    protected $tasks;

	public function __construct(array $datas)
	{
		$this->tasksDatas = $datas['tasks'];
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