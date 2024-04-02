<?php

namespace App\Exports;

use App\Exports\sheets\GlobalSheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ClientsContactsExport implements WithMultipleSheets
{
    use Exportable;
    protected $clientsDatas;
    protected $contactsDatas;

	public function __construct(array $datas)
	{
		$this->clientsDatas = $datas['clients'];
		$this->contactsDatas = $datas['contacts'];
	}
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new GlobalSheetExport($this->clientsDatas);
        $sheets[] = new GlobalSheetExport($this->contactsDatas);
        return $sheets;
    }
}