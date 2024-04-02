<?php

namespace App\Exports\sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class IntervenantsSheetExport implements FromArray,WithHeadings,ShouldAutoSize,WithTitle,WithColumnWidths,WithColumnFormatting
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
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_NUMBER,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_NUMBER,
            'N' => NumberFormat::FORMAT_NUMBER,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_NUMBER,
            'Q' => NumberFormat::FORMAT_NUMBER,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
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
            'K' => 20,            
            'L' => 20,
            'M' => 20,            
            'N' => 20,            
            'O' => 20,
            'P' => 20,            
            'Q' => 20,
            'R' => 20, 
            'S' => 20,         
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