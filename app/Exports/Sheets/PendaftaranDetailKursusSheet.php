<?php

namespace App\Exports\Sheets;

use App\Models\DetailPendaftaran;
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

class PendaftaranDetailKursusSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    protected array $filters;
    private int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Rincian Detail Kursus';
    }

    public function query(): Builder
    {
        $query = DetailPendaftaran::query()->with([
            'pendaftaran.siswa',
            'bidangStudi',
            'levelKelas',
            'kategoriKelas',
        ]);

        if (!empty($this->filters['tgl_mulai']) && !empty($this->filters['tgl_akhir'])) {
            $query->whereHas('pendaftaran', function ($q) {
                $q->whereBetween('tanggal_pendaftaran', [
                    Carbon::parse($this->filters['tgl_mulai'])->startOfDay(),
                    Carbon::parse($this->filters['tgl_akhir'])->endOfDay(),
                ]);
            });
        }

        if (!empty($this->filters['tempat_daftar'])) {
            $query->where('tempat_daftar', $this->filters['tempat_daftar']);
        }

        if (!empty($this->filters['bidang_studi_id'])) {
            $query->where('bidang_studi_id', $this->filters['bidang_studi_id']);
        }

        return $query->latest('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Pendaftaran',
            'Tgl Pendaftaran',
            'Nama Siswa',
            'Tempat Daftar',
            'Bidang Studi',
            'Level Kelas',
            'Kategori Kelas',
            'Harga Normal (Rp)',
            'Diskon 1',
            'Diskon 2',
            'Total Harga Kursus (Rp)',
            'Petugas Input',
        ];
    }

    public function map($detail): array
    {
        $this->rowNumber++;
        $pendaftaran = $detail->pendaftaran;
        $bidang = $detail->bidangStudi->nama_bidang_studi ?? ($detail->bidang_studi_custom ?? '-');

        return [
            $this->rowNumber,
            "'" . (string)($detail->no_pendaftaran ?? '-'),
            $pendaftaran && $pendaftaran->tanggal_pendaftaran ? Carbon::parse($pendaftaran->tanggal_pendaftaran)->format('d/m/Y') : '-',
            $pendaftaran?->siswa?->nama_siswa ?? '-',
            $detail->tempat_daftar ?? '-',
            $bidang,
            $detail->levelKelas->nama_level ?? '-',
            $detail->kategoriKelas->nama_kategori ?? '-',
            (float)($detail->harga_kursus ?? 0),
            $detail->diskon1 !== null ? $detail->diskon1 . '%' : '0%',
            $detail->diskon2 !== null ? $detail->diskon2 . '%' : '0%',
            (float)($detail->total_harga ?? 0),
            $detail->created_by ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'I' => '"Rp "#,##0',
            'L' => '"Rp "#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
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

                $sheet->mergeCells("A{$totalRow}:H{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL KESELURUHAN');

                // Formula SUM untuk Total Harga Normal dan Total Harga Bersih
                $sheet->setCellValue("I{$totalRow}", "=SUM(I2:I{$lastRow})");
                $sheet->setCellValue("L{$totalRow}", "=SUM(L2:L{$lastRow})");

                $sheet->getStyle("A{$totalRow}:M{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FF1E3A8A'],
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE2E8F0'],
                    ],
                    'borders' => [
                        'top'    => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF94A3B8']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF1E3A8A']],
                        'left'   => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']],
                        'right'  => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']],
                    ],
                ]);

                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("I{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("L{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            },
        ];
    }
}
