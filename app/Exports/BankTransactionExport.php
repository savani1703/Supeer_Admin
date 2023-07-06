<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BankTransactionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithColumnFormatting
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
            'Date',
            'Account Holder',
            'Account Number',
            'UPI ID',
            'Bank',
            'Amount',
            'Description',
            'Is Get',
            'Payment UTR',
            'Payment Mode',
            'Transaction Mode',
            'Transaction Date',
            'Entry Date',
            'ID',
            'Hash',
            'Ref',
        ];
    }

    public function columnFormats(): array
    {
        return [
            "C" => NumberFormat::FORMAT_NUMBER,
            "I" => NumberFormat::FORMAT_NUMBER,
            "N" => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
