<?php

namespace App\Exports\Sheets;

use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents; // <-- 1. Tambahkan ini
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;   // <-- 2. Tambahkan ini
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembayaranRekapSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    protected array $filters;
    private int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Rekap Tagihan & Piutang';
    }

    public function query(): Builder
    {
        $query = Pembayaran::query()->with([
            'siswa',
            'detailPendaftaran.bidangStudi',
            'detailPendaftaran.levelKelas',
        ]);

        if (isset($this->filters['status_pembayaran']) && $this->filters['status_pembayaran'] !== '') {
            $query->where('status_pembayaran', $this->filters['status_pembayaran']);
        }

        if (!empty($this->filters['tempat_pembayaran'])) {
            $query->where('tempat_pembayaran', $this->filters['tempat_pembayaran']);
        }

        if (!empty($this->filters['jenis_pembayaran'])) {
            $query->where('jenis_pembayaran', $this->filters['jenis_pembayaran']);
        }

        if (!empty($this->filters['tgl_mulai']) && !empty($this->filters['tgl_akhir'])) {
            $query->whereBetween('tanggal_pembayaran', [
                Carbon::parse($this->filters['tgl_mulai'])->startOfDay(),
                Carbon::parse($this->filters['tgl_akhir'])->endOfDay(),
            ]);
        }

        return $query->latest('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'Tgl Pembayaran Awal',
            'No. Pendaftaran',
            'Nama Siswa',
            'NIK',
            'Kursus (Bidang Studi - Level)',
            'Tempat / Cabang',
            'Metode Pembayaran',
            'Bank Tujuan',
            'Total Tagihan (Rp)',
            'Uang Muka / DP (Rp)',
            'No. Kwitansi DP',
            'Pelunasan (Rp)',
            'Tgl Pelunasan',
            'No. Kwitansi Pelunasan',
            'Sisa Tagihan / Piutang (Rp)',
            'Status Pembayaran',
        ];
    }

    public function map($pembayaran): array
    {
        $this->rowNumber++;
        $detail = $pembayaran->detailPendaftaran;
        $kursus = ($detail?->bidangStudi?->nama_bidang_studi ?? '-') . ' - ' . ($detail?->levelKelas?->nama_level ?? '-');

        return [
            $this->rowNumber,
            $pembayaran->tanggal_pembayaran ? Carbon::parse($pembayaran->tanggal_pembayaran)->format('d/m/Y') : '-',
            "'" . (string)($detail?->no_pendaftaran ?? '-'),
            $pembayaran->siswa->nama_siswa ?? '-',
            "'" . (string)($pembayaran->siswa->nik ?? '-'),
            $kursus,
            $pembayaran->tempat_pembayaran ?? '-',
            ucfirst($pembayaran->jenis_pembayaran ?? '-'),
            $pembayaran->bank_tujuan ?? '-',
            (float)($pembayaran->jumlah_tagihan ?? 0),
            (float)($pembayaran->uang_muka ?? 0),
            "'" . (string)($pembayaran->no_kwitansi_dp ?? '-'),
            (float)($pembayaran->pelunasan ?? 0),
            $pembayaran->tanggal_pelunasan ? Carbon::parse($pembayaran->tanggal_pelunasan)->format('d/m/Y') : '-',
            "'" . (string)($pembayaran->no_kwitansi_pelunasan ?? '-'),
            (float)($pembayaran->sisa_tagihan ?? 0),
            $pembayaran->status_pembayaran ? 'Lunas' : 'Pending',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'J' => '"Rp "#,##0',
            'K' => '"Rp "#,##0',
            'L' => NumberFormat::FORMAT_TEXT,
            'M' => '"Rp "#,##0',
            'O' => NumberFormat::FORMAT_TEXT,
            'P' => '"Rp "#,##0',
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

    /**
     * Menambahkan baris Total Keseluruhan di baris terakhir data
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $totalRow = $lastRow + 1;

                // Merge kolom A sampai I untuk Label
                $sheet->mergeCells("A{$totalRow}:I{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL KESELURUHAN');

                // Formula SUM dinamis sesuai baris data
                $sheet->setCellValue("J{$totalRow}", "=SUM(J2:J{$lastRow})");
                $sheet->setCellValue("K{$totalRow}", "=SUM(K2:K{$lastRow})");
                $sheet->setCellValue("M{$totalRow}", "=SUM(M2:M{$lastRow})");
                $sheet->setCellValue("P{$totalRow}", "=SUM(P2:P{$lastRow})");

                // Styling Total: Font bold navy, background abu-abu, garis double bawah
                $sheet->getStyle("A{$totalRow}:Q{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FF1E3A8A'],
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE2E8F0'],
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF94A3B8'],
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_DOUBLE,
                            'color'       => ['argb' => 'FF1E3A8A'],
                        ],
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFCBD5E1'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFCBD5E1'],
                        ],
                    ],
                ]);

                // Alignment rata kanan & format Rupiah pada baris total
                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("J{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("K{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("M{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("P{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            },
        ];
    }
}
