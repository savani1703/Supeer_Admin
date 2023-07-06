<?php

namespace App\Exports\Bank;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\LocalTemporaryFile;

class IDFCManualPayoutExport implements WithEvents, WithColumnFormatting
{

    private int $startIndex = 3;
    private $manualPayoutData;
    private $notify_email_id;

    public function __construct($manualPayoutData, $notify_email_id)
    {
        $this->manualPayoutData = $manualPayoutData;
        $this->notify_email_id = $notify_email_id;
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function(BeforeWriting $event) {
                $templateFile = new LocalTemporaryFile(public_path('/bank/idfc/IDFC_BULK_FILE_FORMAT.xlsx'));
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
            $sheet->setCellValue("A$currentIndex", $payoutData->bank_holder);
            $sheet->setCellValue("B$currentIndex", $payoutData->to_account_number);
            $sheet->setCellValue("C$currentIndex", $payoutData->ifsc);
            $sheet->setCellValue("D$currentIndex", $payoutData->payout_type);
            $sheet->setCellValue("E$currentIndex", $payoutData->from_account_number);
            $sheet->setCellValue("F$currentIndex", $currentDate);
            $sheet->setCellValue("G$currentIndex", $payoutData->payout_amount);
            $sheet->setCellValue("H$currentIndex", "INR");
            $sheet->setCellValue("I$currentIndex", $this->notify_email_id);
            $sheet->setCellValue("J$currentIndex", $payoutData->payout_id);
            $sheet->setCellValue("K$currentIndex", $payoutData->payout_id);
            $currentIndex++;
        }
    }

    public function columnFormats(): array
    {
        return [
            "B" => 0,
            "E" => 0
        ];
    }
}
