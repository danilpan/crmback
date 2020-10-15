<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use DateTime;

class ExportToExcel{

    public function __contruct(){

    }

    public function exToExcel($items, $columns){

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $count = 0;
        foreach($columns as $column) if($column->value) $count++;

        $abc = $this->exACB($count);
        $sheet = $this->exBuildHeaders($sheet, $columns, $abc);


        $sheet = $this->exBuildBody($sheet, $columns, $items, $abc);
    
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="file.xlsx"');
        $writer->save('file.xlsx');
        return 'file.xlsx';
    }

    private function exBuildHeaders($sheet, $columns, $abc){

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'FFA0A0A0',
                ],
                'endColor' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
        ];

        $i=0;
        foreach($columns as $column){
            if(!$column->value)
                    continue;
            $sheet->setCellValue($abc[$i].'1', $column->caption);
            $sheet->getStyle($abc[$i].'1')->applyFromArray($styleArray);
            $i++;
        }

        return $sheet;
    }

    private function exBuildBody($sheet, $columns, $items, $abc){

        foreach($items as $itemKey=>$item){
            $j=0;
            foreach($columns as $column){
                if(!$column->value)
                    continue;

                $dataField = $column->key;
                $dataField = explode(".", $dataField);
                $row_item = "";

                if(isset($item[$dataField[0]])){
                    $row_item =  json_encode($item[$dataField[0]]);

                    if(isset($dataField[1]) && isset($item[$dataField[0]][$dataField[1]])){
                        $row_item = $item[$dataField[0]][$dataField[1]];
                    }

                    if(!isset($dataField[1]) && gettype($item[$dataField[0]]) != 'array'){
                        $row_item =  $item[$dataField[0]];
                    }

                    if(isset($dataField[1]) && isset($item[$dataField[0]][0])){
                        $row_item = "";
                        foreach($item[$dataField[0]] as $i){
                            $row_item .= " ";
                            if(isset($i[$dataField[1]])){
                                $row_item .=  $i[$dataField[1]];
                            }
                        }
                    }

                    if(!isset($dataField[1]) && gettype($item[$dataField[0]]) == 'array'){
                        $row_item = "";
                        foreach($item[$dataField[0]] as $i){
                            $row_item .= " ";
                            if(gettype($i) == 'array'){
                                 $row_item .= json_encode($i);
                            }else{
                                $row_item .=  $i;
                            }
                        }
                    }
                }
                if($row_item == "[]"){
                    $row_item = "";
                };

                $row_number = $itemKey + 2;
                if( isset($column->dataType) && $column->dataType == 'datetime'){
                    if(isset($item[$dataField[0]]) && is_string($item[$dataField[0]])){
                        $row_item = DateTime::createFromFormat('Y-m-d H:i:s', $item[$dataField[0]]);
                        $sheet->getStyle($abc[$j]."".$row_number)
                            ->getNumberFormat()
                            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
                    }else{
                        $row_item = null;
                    }
                    $sheet->setCellValue($abc[$j]."".$row_number,  \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($row_item));
       
                }else{
                    $sheet->setCellValue($abc[$j]."".$row_number, strip_tags($row_item));
                }
                $j++;
            }
        }
        return $sheet;
    }

    public function exACB($count){
        $abc = [];
        $char = 'A';
        for($i=0; $i<$count; $i++){
            $abc[] = $char;
            $char++;
        };
        return $abc;
    }
}
