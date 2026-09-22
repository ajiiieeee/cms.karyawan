<?php

namespace App\Jobs;

use App\Models\LevelKelas;
use App\Models\Pendaftaran;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use FPDF;

class SendPendaftaranEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [30, 60, 120];

    protected $siswaId;
    protected $pendaftaranId;
    protected $uploadPembayaranPath;

    /**
     * Create a new job instance.
     */
    public function __construct(int $siswaId, int $pendaftaranId, ?string $uploadPembayaranPath = null)
    {
        $this->siswaId = $siswaId;
        $this->pendaftaranId = $pendaftaranId;
        $this->uploadPembayaranPath = $uploadPembayaranPath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $siswa = Siswa::find($this->siswaId);
        $pendaftaran = Pendaftaran::with([
            'detailPendaftaran.levelKelas',
            'detailPendaftaran.bidangStudi',
        ])->find($this->pendaftaranId);

        if (!$siswa || !$pendaftaran) {
            return;
        }

        $this->sendConfirmationEmail($siswa, $pendaftaran);
    }

    /**
     * Send confirmation email to user after successful registration
     */
    private function sendConfirmationEmail($siswa, $pendaftaran)
    {
        try {
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host       = config('mail.mailers.smtp.host', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = config('mail.mailers.smtp.username');
            $mail->Password   = config('mail.mailers.smtp.password');
            $mail->SMTPSecure = config('mail.mailers.smtp.encryption', 'tls');
            $mail->Port       = (int) config('mail.mailers.smtp.port', 587);
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 30;

            // Recipients
            $fromAddress = config('mail.from.address');
            $fromName    = config('mail.from.name', 'CREATIVE MEDIA');
            $mail->setFrom($fromAddress, $fromName);
            $mail->addReplyTo($fromAddress, $fromName);
            // $mail->addAddress(env('ADMIN_EMAIL', 'adm@creativemedia.id'));
            $mail->addAddress(env('ADMIN_EMAIL', 'rospendik321@gmail.com'));

            // Get level name and bidang studi name from detail
            $detail = $pendaftaran->detailPendaftaran->first();
            $levelName       = $detail?->levelKelas?->nama_level ?? '';
            $bidangStudiName = $detail?->bidang_studi_custom
                ?? $detail?->bidangStudi?->nama_bidang_studi
                ?? '';

            // Format tanggal lahir
            $tanggalLahirFormatted = Carbon::parse($siswa->tanggal_lahir)->format('d-m-Y');

            // Generate PDF attachment
            $pdfPath = $this->generatePdfFormulir($siswa, $pendaftaran, $levelName, $bidangStudiName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'PENDAFTARAN KURSUS - Creative Media';
            $mail->Body    = $this->generateEmailBody($siswa, $pendaftaran, $levelName, $bidangStudiName, $tanggalLahirFormatted);

            // Attach PDF if generated successfully
            if ($pdfPath && file_exists($pdfPath)) {
                $mail->addAttachment($pdfPath, 'Formulir_Pendaftaran_' . $siswa->nis . '.pdf');
            }

            // Attach uploaded files from student registration
            $this->attachUploadedFiles($mail, $siswa, $this->uploadPembayaranPath);

            $mail->send();

            // Delete temporary PDF file after sending
            if ($pdfPath && file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            // Log success to custom queue.log file
            $this->logToQueueFile("email pendaftaran {$siswa->nama_siswa}-{$siswa->nis} berhasil terkirim");

        } catch (Exception $e) {
            // Log error to custom queue.log file
            $this->logToQueueFile("email pendaftaran {$siswa->nama_siswa}-{$siswa->nis} GAGAL terkirim: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Log message to custom queue.log file
     */
    private function logToQueueFile(string $message): void
    {
        $logPath = storage_path('logs/queue.log');
        $datetime = now()->format('Y-m-d H:i:s');
        $logMessage = "{$message} || {$datetime}" . PHP_EOL;

        file_put_contents($logPath, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Attach uploaded files from student registration
     */
    private function attachUploadedFiles($mail, $siswa, ?string $uploadPembayaranPath = null): void
    {
        // Attach KTP file
        if (!empty($siswa->upload_ktp)) {
            $fullPath = public_path('storage/' . $siswa->upload_ktp);

            if (!file_exists($fullPath)) {
                $fullPath = public_path($siswa->upload_ktp);
            }

            if (file_exists($fullPath)) {
                $attachmentName = basename($siswa->upload_ktp);
                $mail->addAttachment($fullPath, $attachmentName);
            }
        }

        // Attach KK file
        if (!empty($siswa->upload_kk)) {
            $fullPath = public_path('storage/' . $siswa->upload_kk);

            if (!file_exists($fullPath)) {
                $fullPath = public_path($siswa->upload_kk);
            }

            if (file_exists($fullPath)) {
                $attachmentName = basename($siswa->upload_kk);
                $mail->addAttachment($fullPath, $attachmentName);
            }
        }

        // Attach Bukti Pembayaran file
        if (!empty($uploadPembayaranPath)) {
            $fullPath = public_path('storage/' . $uploadPembayaranPath);

            if (!file_exists($fullPath)) {
                $fullPath = public_path($uploadPembayaranPath);
            }

            if (file_exists($fullPath)) {
                $attachmentName = basename($uploadPembayaranPath);
                $mail->addAttachment($fullPath, $attachmentName);
            }
        }
    }

    /**
     * Generate PDF Formulir Pendaftaran
     */
    private function generatePdfFormulir($siswa, $pendaftaran, string $levelName, string $bidangStudiName)
    {
        try {
            $pdf = new FPDF('P', 'mm', array(210, 330));

            // Path ke assets
            $assetsPath = public_path('assets/pdf/');
            $backgroundPage1 = $assetsPath . 'background.jpg';
            $backgroundPage2 = $assetsPath . 'background-2.jpg';
            $centangImage = $assetsPath . 'centang.png';

            // Page 1
            $pdf->AddPage();

            // Add background if exists
            if (file_exists($backgroundPage1)) {
                $pdf->Image($backgroundPage1, 0, 0, 210, 330);
            }

            $pdf->SetFont('Arial', '', 10);
            $pdf->SetMargins(0.5, 0.5, 0.5, 0.5);

            // Data Siswa
            $nama           = $siswa->nama_siswa;
            $ktp            = $siswa->nik;
            $tempat_lahir   = $siswa->tempat_lahir;
            $tanggal_lahir  = $siswa->tanggal_lahir;
            $agama          = strtolower($siswa->agama);
            $jenis_kelamin  = strtolower($siswa->jenis_kelamin);
            $tempat_tinggal = $siswa->jenis_tinggal;
            $alamat         = $siswa->alamat;
            $whatsapp       = $siswa->no_telepon;
            $email          = $siswa->email ?? '';
            $pendidikan     = strtolower($siswa->pendidikan_terakhir);
            $pekerjaan      = strtolower($siswa->pekerjaan ?? '');

            // Text fields
            $pdf->Text(53, 58, $nama);
            $pdf->Text(53, 64, $ktp);
            $pdf->Text(53, 70, $tempat_lahir . ', ' . date('d-m-Y', strtotime($tanggal_lahir)));

            // Agama checkboxes
            if (file_exists($centangImage)) {
                if ($agama == 'islam') {
                    $pdf->Image($centangImage, 57, 74, 5, 5);
                } elseif ($agama == 'kristen') {
                    $pdf->Image($centangImage, 79, 74, 5, 5);
                } elseif ($agama == 'katolik') {
                    $pdf->Image($centangImage, 101, 74, 5, 5);
                } elseif ($agama == 'hindu') {
                    $pdf->Image($centangImage, 127, 74, 5, 5);
                } elseif ($agama == 'budha' || $agama == 'buddha') {
                    $pdf->Image($centangImage, 147, 74, 5, 5);
                } else {
                    $pdf->Image($centangImage, 167, 74, 5, 5);
                }

                // Jenis Kelamin checkboxes
                if ($jenis_kelamin == 'laki_laki' || $jenis_kelamin == 'laki-laki') {
                    $pdf->Image($centangImage, 57, 82, 5, 5);
                } else {
                    $pdf->Image($centangImage, 101, 82, 5, 5);
                }

                // Tempat Tinggal checkboxes
                if (strpos($tempat_tinggal, 'orang_tua') !== false || strpos($tempat_tinggal, 'Orang Tua') !== false) {
                    $pdf->Image($centangImage, 57, 90, 5, 5);
                } elseif (strpos($tempat_tinggal, 'kos') !== false || strpos($tempat_tinggal, 'Kos') !== false) {
                    $pdf->Image($centangImage, 101, 90, 5, 5);
                } else {
                    $pdf->Image($centangImage, 127, 90, 5, 5);
                }
            }

            // Alamat dan kontak
            $pdf->Text(53, 102, $alamat);
            $pdf->Text(53, 108, '');
            $pdf->Text(53, 120, '');
            $pdf->Text(53, 126, $whatsapp);
            $pdf->Text(53, 132, $email);

            // Bidang Studi dan Level
            $pdf->Text(53, 295, $bidangStudiName);
            $pdf->Text(140, 295, $levelName);

            // Pendidikan checkboxes
            if (file_exists($centangImage)) {
                if (strpos($pendidikan, 'sd') !== false) {
                    $pdf->Image($centangImage, 57, 136, 5, 5);
                } elseif (strpos($pendidikan, 'smp') !== false) {
                    $pdf->Image($centangImage, 87, 136, 5, 5);
                } elseif (strpos($pendidikan, 'sma') !== false || strpos($pendidikan, 'smk') !== false) {
                    $pdf->Image($centangImage, 120, 136, 5, 5);
                } elseif (strpos($pendidikan, 'd3') !== false || strpos($pendidikan, 'diploma') !== false) {
                    $pdf->Image($centangImage, 158, 136, 5, 5);
                } elseif (strpos($pendidikan, 's1') !== false || strpos($pendidikan, 'sarjana') !== false || strpos($pendidikan, 'd4') !== false) {
                    $pdf->Image($centangImage, 57, 140, 5, 5);
                } elseif (strpos($pendidikan, 's2') !== false || strpos($pendidikan, 'magister') !== false) {
                    $pdf->Image($centangImage, 87, 140, 5, 5);
                } elseif (strpos($pendidikan, 's3') !== false || strpos($pendidikan, 'doktor') !== false) {
                    $pdf->Image($centangImage, 120, 140, 5, 5);
                }

                // Pekerjaan checkboxes
                if (strpos($pekerjaan, 'pns') !== false || strpos($pekerjaan, 'karyawan') !== false) {
                    $pdf->Image($centangImage, 57, 148, 5, 5);
                } elseif (strpos($pekerjaan, 'wiraswasta') !== false || strpos($pekerjaan, 'wirausaha') !== false || strpos($pekerjaan, 'freelancer') !== false) {
                    $pdf->Image($centangImage, 87, 148, 5, 5);
                } elseif (strpos($pekerjaan, 'pelajar') !== false || strpos($pekerjaan, 'mahasiswa') !== false) {
                    $pdf->Image($centangImage, 120, 148, 5, 5);
                }
            }

            // Page 2
            $pdf->AddPage();

            // Add background page 2 if exists
            if (file_exists($backgroundPage2)) {
                $pdf->Image($backgroundPage2, 0, 0, 210, 330);
            }

            // Checkbox persetujuan
            if (file_exists($centangImage)) {
                $pdf->Image($centangImage, 13, 255, 10, 10);
            }

            // Tanggal
            $pdf->Text(175, 270, date('d'));
            $pdf->Text(183, 270, date('m'));
            $pdf->Text(194, 270, substr(date('Y'), 2));

            // Nama siswa di tanda tangan
            $pdf->Text(164, 294, $nama);

            // Save PDF to temporary file
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filename = $tempDir . '/Formulir_' . $siswa->nis . '_' . time() . '.pdf';
            $pdf->Output('F', $filename);

            return $filename;

        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate HTML email body
     */
    private function generateEmailBody($siswa, $pendaftaran, string $levelName, string $bidangStudiName, string $tanggalLahirFormatted)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="dark">
            <meta name="supported-color-schemes" content="dark">
        </head>
        <body bgcolor="#1a1a2e" style="font-family: Arial, sans-serif; background-color: #1a1a2e; margin: 0; padding: 20px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#1a1a2e">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#16213e" style="max-width: 600px; border-radius: 10px; overflow: hidden;">
                            <!-- Header -->
                            <tr>
                                <td bgcolor="#0066ff" style="background: linear-gradient(90deg, #0066ff, #00aaff); padding: 15px; text-align: center;">
                                    <font color="#ffffff" style="color: #ffffff; font-size: 20px; font-weight: bold;">Data Pendaftaran</font>
                                </td>
                            </tr>
                            <!-- Content -->
                            <tr>
                                <td bgcolor="#16213e" style="padding: 30px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">Nama</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars($siswa->nama_siswa) . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">NO KTP</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars($siswa->nik) . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">Tempat, Tanggal Lahir</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars($siswa->tempat_lahir) . ', ' . $tanggalLahirFormatted . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">Agama</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars(ucfirst($siswa->agama)) . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">Alamat</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars($siswa->alamat) . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">WhatsApp</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars($siswa->no_telepon) . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">Email</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars($siswa->email ?? '') . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">Pendidikan Terakhir</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars(strtoupper($siswa->pendidikan_terakhir)) . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">Pekerjaan</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars(ucwords(str_replace('_', ' ', $siswa->pekerjaan ?? ''))) . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">Bidang Studi</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars($bidangStudiName) . '</font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">Level</font>
                                            </td>
                                            <td style="padding: 12px 0; border-bottom: 1px solid #2a2a4a;">
                                                <font color="#cccccc" style="color: #cccccc;">:</font> <font color="#ffffff" style="color: #ffffff;">' . htmlspecialchars(ucwords($levelName)) . '</font>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- Footer -->
                            <tr>
                                <td bgcolor="#16213e" style="padding: 20px; text-align: center;">
                                    <font color="#888888" size="2" style="color: #888888; font-size: 12px;">Terima kasih telah mendaftar di Creative Media.</font><br>
                                    <font color="#888888" size="2" style="color: #888888; font-size: 12px;">Tim kami akan segera menghubungi Anda.</font>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';

        return $html;
    }

    /**
     * Generate plain text email body
     */
    private function generateEmailBodyPlainText($siswa, $pendaftaran, string $levelName, string $bidangStudiName, string $tanggalLahirFormatted)
    {
        $text = "KONFIRMASI PENDAFTARAN\n";
        $text .= "======================\n\n";
        $text .= "Nama                  : " . $siswa->nama_siswa . "\n";
        $text .= "No.Identitas          : " . $siswa->nik . "\n";
        $text .= "Tempat, Tanggal Lahir : " . $siswa->tempat_lahir . ", " . $tanggalLahirFormatted . "\n";
        $text .= "Agama                 : " . Str::ucfirst($siswa->agama) . "\n";
        $text .= "Alamat                : " . $siswa->alamat . "\n";
        $text .= "WhatsApp              : " . $siswa->no_telepon . "\n";
        $text .= "Email                 : " . ($siswa->email ?? '') . "\n";
        $text .= "Pendidikan Terakhir   : " . strtoupper($siswa->pendidikan_terakhir) . "\n";
        $text .= "Pekerjaan             : " . ucwords(str_replace('_', ' ', $siswa->pekerjaan ?? '')) . "\n";
        $text .= "Bidang Studi          : " . $bidangStudiName . "\n";
        $text .= "Level                 : " . ucwords($levelName) . "\n\n";
        $text .= "Terima kasih telah mendaftar di Creative Media.\n";
        $text .= "Tim kami akan segera menghubungi Anda.\n";

        return $text;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('SendPendaftaranEmail job failed permanently', [
            'siswa_id'        => $this->siswaId,
            'pendaftaran_id'  => $this->pendaftaranId,
            'error'           => $exception->getMessage(),
        ]);
    }
}
