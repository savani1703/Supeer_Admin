<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithColumnFormatting
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
            'Transaction Id',
            'Merchant Id',
            'Merchant Name',
            'Merchant Order Id',
            'Customer Id',
            'Customer Name',
            'Customer Email',
            'Customer Mobile',
            'Currency',
            'Payment Status',
            'Payment Amount',
            'Fees',
            'Associate Fees',
            'Payable Amount',
            'Payment Gateway response code',
            'Payment Gateway response message',
            'Payment Gateway reference id',
            'Bank UTR Number',
            'Payment Method',
            'Payment Gateway Name',
            'PG Label',
            'Bank Name',
            'Bank Holder Account Number',
            'Bank Holder IFsc',
            'Upi ID',
            'Webhook Attempt',
            'Callback URL',
            'Customer ip',
            'Date',
        ];
    }

    public function columnFormats(): array
    {
        return [
            "Q" => NumberFormat::FORMAT_NUMBER,
            "P" => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
