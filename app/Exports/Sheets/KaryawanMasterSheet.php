<?php

namespace App\Exports\Sheets;

use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KaryawanMasterSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    protected array $filters;
    private int $rowNumber = 0;

    private array $jabatanMap = [
        'cso'            => 'CSO',
        'admin'          => 'Admin',
        'manager'        => 'Manager',
        'programmer'     => 'Programmer',
        'desaingrafis'   => 'Desain Grafis',
        'hrd'            => 'HRD',
        'direktur'       => 'Direktur',
        'trainer'        => 'Trainer',
        'bd'             => 'BD',
        'bc'             => 'BC',
        'itsupport'      => 'IT Support',
        'contentcreator' => 'Content Creator',
    ];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Profil & Saldo Cuti';
    }

    public function query(): Builder
    {
        $query = Karyawan::query()->with(['saldoCuti']);

        if (!empty($this->filters['status_akun'])) {
            $query->where('status_akun', $this->filters['status_akun']);
        }

        if (!empty($this->filters['jabatan'])) {
            $query->where('jabatan', $this->filters['jabatan']);
        }

        return $query->orderBy('nama_karyawan', 'asc');
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama Karyawan',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'No. Telepon',
            'Email',
            'Jabatan',
            'Tanggal Masuk',
            'Status Akun',
            'Kuota Cuti (Hari)',
            'Cuti Terpakai (Hari)',
            'Sisa Saldo Cuti (Hari)',
        ];
    }

    public function map($karyawan): array
    {
        $this->rowNumber++;
        $saldo = $karyawan->saldoCuti;
        $kuota = $saldo ? (int) $saldo->total_cuti : 0;
        $terpakai = $saldo ? (int) $saldo->saldo_terpakai : 0;
        $sisa = $saldo ? (int) $saldo->saldo_sisa : 0;

        return [
            $this->rowNumber,
            "'" . (string)($karyawan->nik ?? '-'),
            $karyawan->nama_karyawan ?? '-',
            ucfirst($karyawan->jenis_kelamin ?? '-'),
            $karyawan->tanggal_lahir ? Carbon::parse($karyawan->tanggal_lahir)->format('d/m/Y') : '-',
            "'" . (string)($karyawan->telefon ?? '-'),
            $karyawan->email ?? '-',
            $this->jabatanMap[$karyawan->jabatan] ?? ucfirst($karyawan->jabatan ?? '-'),
            $karyawan->tanggal_masuk ? Carbon::parse($karyawan->tanggal_masuk)->format('d/m/Y') : '-',
            $karyawan->status_akun === 'aktif' ? 'Aktif' : 'Tidak Aktif',
            $kuota,
            $terpakai,
            $sisa,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A'], // Navy
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $totalRow = $lastRow + 1;

                $sheet->mergeCells("A{$totalRow}:J{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL HARI CUTI SELURUH KARYAWAN');
                $sheet->setCellValue("K{$totalRow}", "=SUM(K2:K{$lastRow})");
                $sheet->setCellValue("L{$totalRow}", "=SUM(L2:L{$lastRow})");
                $sheet->setCellValue("M{$totalRow}", "=SUM(M2:M{$lastRow})");

                $sheet->getStyle("A{$totalRow}:M{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF1E3A8A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
                    'borders' => [
                        'top'    => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF94A3B8']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF1E3A8A']],
                        'left'   => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']],
                        'right'  => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']],
                    ],
                ]);

                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("K{$totalRow}:M{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}