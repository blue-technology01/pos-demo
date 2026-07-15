<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\CashRegister;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CashRegisterExport implements FromCollection, WithHeadings, WithEvents
{
    public function collection()
    {
        return CashRegister::with('user')
            ->get()
            ->map(function ($register) {
                return [
                    'id' => $register->id,
                    'cashier_name' => $register->user?->name,
                    'opening_balance' => (float) $register->opening_balance,
                    'expected_balance' => (float) $register->expected_balance,
                    'closing_balance' => (float) $register->closing_balance,
                    'difference_amount' => (float) $register->difference_amount,
                    'total_sales' => (float) $register->total_sales,
                    'total_transactions' => (int) $register->total_transactions,
                    'opened_at' => $register->opened_at ? Carbon::parse($register->opened_at)->format('Y-m-d') : '',
                ];
            });
    }

    // header of page
    public function headings(): array
    {
        return [
            'ID', 'Cashier Name', 'Opening Balance', 'Expected Balance', 'Closing Balance',
            'Difference Amount', 'Total Sales', 'Total Transactions', 'Opened At',
        ];
    }

    // style for sheet
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $totalRow = $lastRow + 1;

                $sheet->setCellValue("B{$totalRow}", "TOTAL");

                $columns = ['C', 'D', 'E', 'F', 'G', 'H'];
                foreach ($columns as $col) {
                    $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}2:{$col}{$lastRow})");
                }
                // Header Style
                $sheet->getStyle("A1:I1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2F5597']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                // Total Row
                $colorMap = [
                    'C' => '808080', // gray
                    'E' => '000000', // black
                    'F' => 'C00000', // red
                    'G' => '76923C', // green
                    'H' => '1F4E79', // blue
                ];
                foreach ($colorMap as $col => $rgb) {
                    $sheet->getStyle("{$col}{$totalRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $rgb]],
                    ]);
                }
                // Auto Size & Alignment
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                $sheet->getStyle("A1:I{$totalRow}")->applyFromArray([
                    'alignment' => ['vertical' => 'center', 'horizontal' => 'center'],
                ]);
            },
        ];
    }
}
