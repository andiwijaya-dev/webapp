<?php

namespace Andiwijaya\WebApp\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class GenericExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
  protected $columns;
  protected $data;

  /**
   * @return Collection
   */
  public function collection()
  {
    $arr = collect([]);
    foreach($this->data as $data){

      $obj = [];
      foreach($this->columns as $idx=>$column)
        $obj[] = $data[$idx] ?? '';

      $arr->add($obj);
    }

    return $arr;
  }

  public function __construct($columns, $data)
  {
    $this->columns = $columns;
    $this->data = $data;
  }

  public function headings(): array
  {
    return $this->columns;
  }

  public function registerEvents(): array
  {
    return [
      BeforeExport::class  => function(BeforeExport $event) {
        $event->writer->setCreator(env('APP_NAME'));
      },
      AfterSheet::class    => function(AfterSheet $event) {

        $event->sheet->styleCells(
          'A1:Z1',
          [
            'font'=>[ 'bold'=>true ]
          ]
        );
      },
    ];
  }
}
