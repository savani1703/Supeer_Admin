<?php

namespace App\Exports\Bank;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\LocalTemporaryFile;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class YesManualPayoutExport  implements WithEvents
{

    private int $startIndex = 2;
    private $manualPayoutData;

    public function __construct($manualPayoutData)
    {
        $this->manualPayoutData = $manualPayoutData;
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function(BeforeWriting $event) {
                $templateFile = new LocalTemporaryFile(public_path('/bank/yes/YES_BULK_FILE_FORMAT.xlsx'));
                $event->writer->reopen($templateFile, Excel::XLSX);
                $sheet = $event->writer->getSheetByIndex(0);

                $this->populateSheet($sheet);

                $event->writer->getSheetByIndex(0)->export($event->getConcernable()); // call the export on the first sheet

                return $event->getWriter()->getSheetByIndex(0);
            },
        ];
    }

    private function populateSheet(\Maatwebsite\Excel\Sheet $sheet)
    {
        $currentIndex = $this->startIndex;
        $currentDate = Carbon::now("Asia/Kolkata")->format("d/m/Y");
        foreach ($this->manualPayoutData as $payoutData) {
            $sheet->setCellValue("A$currentIndex", $currentIndex);
            $sheet->setCellValue("B$currentIndex", $payoutData->bank_holder);
            $sheet->setCellValue("C$currentIndex", "IMPS");
            $sheet->setCellValue("D$currentIndex", intval($payoutData->to_account_number));
            $sheet->setCellValue("E$currentIndex", $payoutData->payout_amount);
            $sheet->setCellValue("F$currentIndex", $payoutData->ifsc);
            $sheet->setCellValue("G$currentIndex", null);
            $sheet->setCellValue("H$currentIndex", $payoutData->payout_id);
            $currentIndex++;
        }
    }
}
