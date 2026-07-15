<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Carbon\Carbon;

class ProductsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    public function model(array $row)
    {
        return new Product([
            'code'          => $row['code'],
            'name'          => $row['name'],
            'category_code' => $row['category_code'],
            'stock'         => (int) $row['stock'],
            'min_stock'     => (int) $row['min_stock'],
            'barcode'       => (string) $row['barcode'],
            'description'   => $row['description'],
            'image'         => $row['image'],
            'expiry_date'   => $this->parseDate($row['expiry_date']),
            'status'        => $row['status'] ?? 'active',
        ]);
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // Handles both Excel serial dates and normal date strings (e.g. 2027-05-14)
        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        }

        return Carbon::parse($value);
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
