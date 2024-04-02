<?php

namespace App\Exports\sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class GlobalSheetExport implements FromArray,WithHeadings,ShouldAutoSize,WithTitle,WithColumnWidths
{
    protected $datas;
	protected $datasHeader;
	protected $sheetTitle;
	
	public function __construct(array $datas)
	{
		$this->datasHeader = $datas[0];
		$this->datas = $datas[1];
		$this->sheetTitle = $datas[2];
	}
	public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,            
            'C' => 20,
            'D' => 20,            
            'E' => 20,
            'F' => 20,            
            'G' => 20,
            'H' => 20,            
            'I' => 20,
            'J' => 20,            
        ];
    }
	public function array(): array
	{
		return $this->datas;
	}
    
	public function headings(): array
	{
		return $this->datasHeader;
	}

	/**
     * @return string
     */
    public function title(): string
    {
        return $this->sheetTitle;
    }
    
}