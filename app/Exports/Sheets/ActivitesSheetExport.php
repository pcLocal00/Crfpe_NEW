<?php

namespace App\Exports\sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ActivitesSheetExport implements FromArray,WithHeadings,ShouldAutoSize,WithTitle,WithColumnWidths,WithColumnFormatting
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
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,

            'G' => NumberFormat::FORMAT_NUMBER,
            // 'G' => NumberFormat::FORMAT_TEXT,

            'H' => NumberFormat::FORMAT_TEXT,

            'I' => NumberFormat::FORMAT_NUMBER,
            // 'I' => NumberFormat::FORMAT_TEXT,

            'J' => NumberFormat::FORMAT_TEXT,

            'K' => NumberFormat::FORMAT_NUMBER,
            // 'K' => NumberFormat::FORMAT_TEXT,

            'L' => NumberFormat::FORMAT_TEXT,


            // 'M' => NumberFormat::FORMAT_TEXT,
            // 'N' => NumberFormat::FORMAT_TEXT,

            'M' => NumberFormat::FORMAT_NUMBER,
            'N' => NumberFormat::FORMAT_NUMBER,

            'O' => NumberFormat::FORMAT_TEXT,
            'P' => NumberFormat::FORMAT_TEXT,
            'Q' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'R' => NumberFormat::FORMAT_TEXT,

            'S' => NumberFormat::FORMAT_NUMBER_00,   
            // 'S' => NumberFormat::FORMAT_TEXT,       

            'T' => NumberFormat::FORMAT_TEXT,         
            'U' => NumberFormat::FORMAT_TEXT,          
            'V' => NumberFormat::FORMAT_DATE_DDMMYYYY,           
            'W' => NumberFormat::FORMAT_TEXT,    

            // 'X' => NumberFormat::FORMAT_TEXT,    
            'X' => NumberFormat::FORMAT_NUMBER_00,             

            'Y' => NumberFormat::FORMAT_TEXT,            
            'Z' => NumberFormat::FORMAT_TEXT,            
            'AA' => NumberFormat::FORMAT_DATE_DDMMYYYY, 
            'AB' => NumberFormat::FORMAT_TEXT,     

            // 'AC' => NumberFormat::FORMAT_TEXT, 
            'AC' => NumberFormat::FORMAT_NUMBER_00, 

            'AD' => NumberFormat::FORMAT_TEXT,             
            'AE' => NumberFormat::FORMAT_TEXT, 
            'AF' => NumberFormat::FORMAT_DATE_DDMMYYYY,             
            'AG' => NumberFormat::FORMAT_TEXT, 

            // 'AH' => NumberFormat::FORMAT_TEXT,            
            // 'AI' => NumberFormat::FORMAT_TEXT, 
            // 'AJ' => NumberFormat::FORMAT_TEXT,             
            // 'AK' => NumberFormat::FORMAT_TEXT,             
            // 'AL' => NumberFormat::FORMAT_TEXT, 
            // 'AM' => NumberFormat::FORMAT_TEXT,             
            // 'AN' => NumberFormat::FORMAT_TEXT,             
            // 'AO' => NumberFormat::FORMAT_TEXT, 

            'AH' => NumberFormat::FORMAT_NUMBER_00,            
            'AI' => NumberFormat::FORMAT_NUMBER_00, 
            'AJ' => NumberFormat::FORMAT_NUMBER_00,             
            'AK' => NumberFormat::FORMAT_NUMBER,             
            'AL' => NumberFormat::FORMAT_NUMBER_00, 
            'AM' => NumberFormat::FORMAT_NUMBER_00,             
            'AN' => NumberFormat::FORMAT_NUMBER_00,             
            'AO' => NumberFormat::FORMAT_NUMBER_00, 
             
            'AP' => NumberFormat::FORMAT_DATE_DDMMYYYY,    
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
            'T' => 20,            
            'U' => 20,            
            'V' => 20,            
            'W' => 20,            
            'X' => 20,            
            'Y' => 20,            
            'Z' => 20,            
            'AA' => 20,
            'AB' => 20,            
            'AC' => 20,
            'AD' => 20,            
            'AE' => 20,
            'AF' => 20,            
            'AG' => 20,
            'AH' => 20,            
            'AI' => 20,
            'AJ' => 20,            
            'AK' => 20,            
            'AL' => 20,
            'AM' => 20,            
            'AN' => 20,            
            'AO' => 20,
            'AP' => 20,        
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