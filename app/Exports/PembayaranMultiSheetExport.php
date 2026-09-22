<?php

namespace App\Exports;

use App\Exports\Sheets\PembayaranMutasiSheet;
use App\Exports\Sheets\PembayaranRekapSheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PembayaranMultiSheetExport implements Export, WithMultipleSheets
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new PembayaranRekapSheet($this->filters),
            new PembayaranMutasiSheet($this->filters),
        ];
    }
}