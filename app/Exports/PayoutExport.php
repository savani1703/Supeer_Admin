<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayoutExport implements FromCollection, ShouldAutoSize, WithHeadings, WithColumnFormatting
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
            'Payout Id',
            'Merchant Ref. Id',
            'Merchant Id',
            'Merchant Name',
            'Payout Amount',
            'Payout Fees',
            'Total Amount',
            'Payout Type',
            'Account Holder Name',
            'Bank Account No.',
            'IFSC',
            'BANK',
            'Payout Status',
            'PG Ref Id',
            'PG Response Msg',
            'UTR',
            'Webhook Attempt',
            'Payout By',
            'PG',
            'PG Label',
            'Approval Status',
            'Approved At',
            'Created At',
        ];
    }

    public function columnFormats(): array
    {
        return [
            "N" => 0,
            "P" => 0,
            "J" => 0,
        ];
    }
}
