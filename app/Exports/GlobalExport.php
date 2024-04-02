<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GlobalExport implements FromArray,WithHeadings,ShouldAutoSize
{
    protected $datas;
	protected $datasHeader;
	
	public function __construct(array $datas)
	{
		$this->datasHeader = $datas[0];
		$this->datas = $datas[1];
	}
	
	public function array(): array
	{
		return $this->datas;
	}
    
	public function headings(): array
	{
		return $this->datasHeader;
	}
}