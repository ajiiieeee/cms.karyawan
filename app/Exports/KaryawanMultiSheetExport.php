<?php

namespace App\Exports;

use App\Exports\Sheets\KaryawanCutiSheet;
use App\Exports\Sheets\KaryawanFimpSheet;
use App\Exports\Sheets\KaryawanMasterSheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KaryawanMultiSheetExport implements Export, WithMultipleSheets
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new KaryawanMasterSheet($this->filters),
            new KaryawanCutiSheet($this->filters),
            new KaryawanFimpSheet($this->filters),
        ];
    }
}