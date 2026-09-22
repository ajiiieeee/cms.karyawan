<?php

namespace App\Exports\Sheets;

use App\Models\Siswa;
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

class PendaftaranSiswaSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    protected array $filters;
    private int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Rekap Pendaftaran Siswa';
    }

    public function query(): Builder
    {
        // Berbasis Siswa (1 Siswa = 1 Baris seperti di Web)
        $query = Siswa::whereHas('pendaftaran')
            ->with(['pendaftaran' => function ($q) {
                $q->orderBy('tanggal_pendaftaran', 'desc')->with('detailPendaftaran');
            }]);

        if (!empty($this->filters['tgl_mulai']) && !empty($this->filters['tgl_akhir'])) {
            $query->whereHas('pendaftaran', function ($q) {
                $q->whereBetween('tanggal_pendaftaran', [
                    Carbon::parse($this->filters['tgl_mulai'])->startOfDay(),
                    Carbon::parse($this->filters['tgl_akhir'])->endOfDay(),
                ]);
            });
        }

        if (!empty($this->filters['tempat_daftar'])) {
            $query->whereHas('pendaftaran.detailPendaftaran', function ($q) {
                $q->where('tempat_daftar', $this->filters['tempat_daftar']);
            });
        }

        if (!empty($this->filters['bidang_studi_id'])) {
            $query->whereHas('pendaftaran.detailPendaftaran', function ($q) {
                $q->where('bidang_studi_id', $this->filters['bidang_studi_id']);
            });
        }

        return $query->latest('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'Tgl Pendaftaran Terbaru',
            'NIS',
            'NIK',
            'Nama Lengkap Siswa',
            'No. Telepon / WA',
            'Email',
            'Kota',
            'Tempat Daftar',
            'Total Kursus Diambil',
            'Grand Total Biaya (Rp)',
            'Status Dokumen KTP',
            'Status Dokumen KK',
            'Petugas Pendaftar',
        ];
    }

    public function map($siswa): array
    {
        $this->rowNumber++;

        // Ambil pendaftaran terbaru untuk data tempat daftar & tgl pendaftaran
        $latestPendaftaran = $siswa->pendaftaran->first();
        $tempatDaftar = $latestPendaftaran?->detailPendaftaran->first()->tempat_daftar ?? '-';

        // Gabungkan seluruh kursus siswa dari semua riwayat pendaftarannya
        $allDetails = $siswa->pendaftaran->flatMap->detailPendaftaran;
        $totalKursus = $allDetails->count();
        $grandTotal = (float) $allDetails->sum('total_harga');

        return [
            $this->rowNumber,
            $latestPendaftaran && $latestPendaftaran->tanggal_pendaftaran
                ? Carbon::parse($latestPendaftaran->tanggal_pendaftaran)->format('d/m/Y')
                : '-',
            "'" . (string)($siswa->nis ?? '-'),
            "'" . (string)($siswa->nik ?? '-'),
            $siswa->nama_siswa ?? '-',
            "'" . (string)($siswa->no_telepon ?? '-'),
            $siswa->email ?? '-',
            $siswa->kota ?? '-',
            $tempatDaftar,
            $totalKursus . ' Kursus',
            $grandTotal,
            !empty($siswa->upload_ktp) ? 'Tersedia' : 'Belum Upload',
            !empty($siswa->upload_kk) ? 'Tersedia' : 'Belum Upload',
            $latestPendaftaran->created_by ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'K' => '"Rp "#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A'],
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
                $sheet->setCellValue("A{$totalRow}", 'TOTAL BIAYA PENDAFTARAN');
                $sheet->setCellValue("K{$totalRow}", "=SUM(K2:K{$lastRow})");

                $sheet->getStyle("A{$totalRow}:N{$totalRow}")->applyFromArray([
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
                $sheet->getStyle("K{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            },
        ];
    }
}
