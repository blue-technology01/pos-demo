<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Services\Report\TopProductService;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TopProductExport implements FromCollection, WithHeadings, WithEvents
{
    public function __construct(
        protected Request $request
    ) {}

    // export data
    public function collection(): Collection
    {
        return app(TopProductService::class)
            ->getExportProducts($this->request);
    }

    // header file excel
    public function headings(): array
    {
        return [
            'Rank',
            'Product Name',
            'Category',
            'Unit Price',
            'Qty Sold',
            'Total Revenue',
            'Stock Status',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Header Style
                $sheet->getStyle("A1:G1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '2F5597'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical'   => 'center',
                    ],
                ]);
                // Currency Format
                $sheet->getStyle("D2:D{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('$#,##0.00');
                $sheet->getStyle("F2:F{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('$#,##0.00');

                // Qty Center
                $sheet->getStyle("A2:G{$lastRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical'   => 'center',
                    ],
                ]);
                // Stock Status Color
                for ($row = 2; $row <= $lastRow; $row++) {

                    $status = $sheet->getCell("G{$row}")->getValue();

                    if ($status === 'In Stock') {

                        $sheet->getStyle("G{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => '76923C'],
                            ],
                        ]);

                    } elseif ($status === 'Low Stock') {

                        $sheet->getStyle("G{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'C55A11'],
                            ],
                        ]);

                    } elseif ($status === 'Out of Stock') {

                        $sheet->getStyle("G{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'C00000'],
                            ],
                        ]);
                    }
                }
                // Auto Size
                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                // Row Alignment
                $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
                    'alignment' => [
                        'vertical'   => 'center',
                        'horizontal' => 'center',
                    ],
                ]);
            },
        ];
    }
}
