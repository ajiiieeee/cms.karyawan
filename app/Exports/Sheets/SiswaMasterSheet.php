<?php

namespace App\Exports\Sheets;

use App\Models\Siswa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Database\Eloquent\Builder;

class SiswaMasterSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected array $filters;
    private int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Data Induk Siswa';
    }

    public function query(): Builder
    {
        $query = Siswa::query()->with('pendaftaran');

        // Filter status siswa jika dipilih
        if (isset($this->filters['status_siswa']) && $this->filters['status_siswa'] !== '') {
            $query->where('status_siswa', $this->filters['status_siswa']);
        }

        // Filter rentang tanggal dibuat/daftar
        if (!empty($this->filters['tgl_mulai']) && !empty($this->filters['tgl_akhir'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->filters['tgl_mulai'])->startOfDay(),
                Carbon::parse($this->filters['tgl_akhir'])->endOfDay()
            ]);
        }

        return $query->latest('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'NIK',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Agama',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Email',
            'No. Telepon / WA',
            'Alamat Lengkap',
            'Kota',
            'Provinsi',
            'Jenis Tinggal',
            'Pendidikan Terakhir',
            'Pekerjaan',
            'Kebutuhan Khusus (ABK)',
            'Status Siswa',
            'Status Dokumen KTP',
            'Status Dokumen KK',
            'Tanggal Registrasi'
        ];
    }

    public function map($siswa): array
    {
        $this->rowNumber++;

        // Mapping Label
        $jenisTinggalMap = [
            'rumah_sendiri'  => 'Rumah Sendiri',
            'rumah_orang_tua' => 'Rumah Orang Tua',
            'kos'            => 'Kos/Kontrakan',
            'rumah_saudara'  => 'Rumah Saudara',
            'asrama'         => 'Asrama',
            'lainnya'        => $siswa->jenis_tinggal_lainnya ?: 'Lainnya'
        ];

        $pendidikanMap = [
            'sd'      => 'SD',
            'smp'     => 'SMP',
            'sma_smk' => 'SMA/SMK',
            'd1'      => 'D1',
            'd2'      => 'D2',
            'd3'      => 'D3',
            'd4/s1'   => 'D4/S1',
            's2'      => 'S2',
            's3'      => 'S3',
            'lainnya' => $siswa->pendidikan_terakhir_lainnya ?: 'Lainnya'
        ];

        $pekerjaanMap = [
            'pelajar'         => 'Pelajar/Mahasiswa',
            'pns'             => 'PNS',
            'karyawan_swasta' => 'Karyawan Swasta',
            'wiraswasta'      => 'Wiraswasta',
            'freelancer'      => 'Freelancer',
            'tidak_bekerja'   => 'Tidak Bekerja',
            'lainnya'         => $siswa->pekerjaan_lainnya ?: 'Lainnya'
        ];

        $abkMap = [
            'tidak_ada'  => 'Tidak Ada',
            'tunanetra'  => 'Tunanetra',
            'tunarungu'  => 'Tunarungu',
            'tunawicara' => 'Tunawicara',
            'tunadaksa'  => 'Tunadaksa',
            'tunagrahita' => 'Tunagrahita',
            'tunalaras'  => 'Tunalaras',
            'autis'      => 'Autis',
            'adhd'       => 'ADHD',
            'lainnya'    => $siswa->abk_lainnya ?: 'Lainnya'
        ];

        return [
            $this->rowNumber,
            "'" . (string)$siswa->nis, // Prefix kutip satu mencegah konversi numeric Excel
            "'" . (string)$siswa->nik, // Mencegah 16 digit NIK berubah jadi scientific notation (3.578E+15)
            $siswa->nama_siswa ?? '-',
            $siswa->jenis_kelamin === 'laki_laki' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'perempuan' ? 'Perempuan' : '-'),
            ucfirst($siswa->agama ?? '-'),
            $siswa->tempat_lahir ?? '-',
            $siswa->tanggal_lahir ? Carbon::parse($siswa->tanggal_lahir)->format('d/m/Y') : '-',
            $siswa->email ?? '-',
            "'" . (string)$siswa->no_telepon, // Menjaga angka 0 di awal nomor telepon
            $siswa->alamat ?? '-',
            $siswa->kota ?? '-',
            $siswa->provinsi ?? '-',
            $jenisTinggalMap[$siswa->jenis_tinggal] ?? ($siswa->jenis_tinggal ?: '-'),
            $pendidikanMap[$siswa->pendidikan_terakhir] ?? ($siswa->pendidikan_terakhir ?: '-'),
            $pekerjaanMap[$siswa->pekerjaan] ?? ($siswa->pekerjaan ?: '-'),
            $abkMap[$siswa->abk] ?? ($siswa->abk ?: 'Tidak Ada'),
            $siswa->status_siswa == 1 ? 'Aktif' : 'Tidak Aktif',
            !empty($siswa->upload_ktp) ? 'Tersedia' : 'Belum Upload',
            !empty($siswa->upload_kk) ? 'Tersedia' : 'Belum Upload',
            $siswa->created_at ? Carbon::parse($siswa->created_at)->format('d/m/Y H:i') : '-'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A'], // Navy Theme
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
