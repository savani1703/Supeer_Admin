<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BlockInfoExport implements FromCollection, ShouldAutoSize, WithHeadings
{

    use Exportable;

    private $collectionData;

    public function __construct($collectionData)
    {
        $this->collectionData = $collectionData;
    }

    public function collection()
    {
        return $this->collectionData;
    }

    public function headings(): array
    {
        return [
            'Block Data',
            'Created At',
        ];
    }
}
