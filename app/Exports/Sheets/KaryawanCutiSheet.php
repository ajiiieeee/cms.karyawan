<?php

namespace App\Exports\Sheets;

use App\Models\PengajuanCuti;
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

class KaryawanCutiSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
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
        return 'Riwayat Pengajuan Cuti';
    }

    public function query(): Builder
    {
        $query = PengajuanCuti::query()->with(['karyawan', 'kategoriCuti']);

        if (!empty($this->filters['status_akun'])) {
            $query->whereHas('karyawan', function ($q) {
                $q->where('status_akun', $this->filters['status_akun']);
            });
        }

        if (!empty($this->filters['jabatan'])) {
            $query->whereHas('karyawan', function ($q) {
                $q->where('jabatan', $this->filters['jabatan']);
            });
        }

        return $query->latest('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'Tgl Pengajuan',
            'Nama Karyawan',
            'Jabatan',
            'Jenis Cuti',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi (Hari)',
            'Alasan / Keterangan',
            'Status Pengajuan',
            'Catatan Penolakan',
        ];
    }

    public function map($cuti): array
    {
        $this->rowNumber++;
        $karyawan = $cuti->karyawan;
        $jenis = $cuti->kategoriCuti->nama_kategori ?? ($cuti->jenis_cuti ?? '-');

        $status = $cuti->status_pengajuan ?: ($cuti->status_approval == 1 ? 'Approve' : ($cuti->status_approval == 2 ? 'Reject' : 'Pending'));

        return [
            $this->rowNumber,
            $cuti->created_date ? Carbon::parse($cuti->created_date)->format('d/m/Y') : ($cuti->created_at ? Carbon::parse($cuti->created_at)->format('d/m/Y') : '-'),
            $karyawan->nama_karyawan ?? '-',
            $this->jabatanMap[$karyawan->jabatan ?? ''] ?? ucfirst($karyawan->jabatan ?? '-'),
            $jenis,
            $cuti->tanggal_awal ? Carbon::parse($cuti->tanggal_awal)->format('d/m/Y') : '-',
            $cuti->tanggal_akhir ? Carbon::parse($cuti->tanggal_akhir)->format('d/m/Y') : '-',
            (int)($cuti->jumlah_hari ?? 0),
            $cuti->keterangan ?? '-',
            $status,
            $cuti->reject_statement ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'], // Royal Blue
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

                $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL HARI CUTI DIAJUKAN');
                $sheet->setCellValue("H{$totalRow}", "=SUM(H2:H{$lastRow})");

                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->applyFromArray([
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
                $sheet->getStyle("H{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}