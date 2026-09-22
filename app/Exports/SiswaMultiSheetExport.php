<?php

namespace App\Exports;

use App\Exports\Sheets\SiswaMasterSheet;
use App\Exports\Sheets\SiswaKursusSheet;
use App\Exports\Sheets\SiswaPembayaranSheet;
use Maatwebsite\Excel\Concerns\Export; // <-- 1. Tambahkan ini
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// 2. Tambahkan "Export" pada implements
class SiswaMultiSheetExport implements Export, WithMultipleSheets
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new SiswaMasterSheet($this->filters),
            new SiswaKursusSheet($this->filters),
            new SiswaPembayaranSheet($this->filters),
        ];
    }
}
