<?php

namespace App\Services;

use App\Models\Sertifikat;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SertifikatGenerator
{
    private string $templatePath;
    private string $logoPath;

    public function __construct()
    {
        $this->templatePath = public_path('assets/images/sertifikat_v2.jpeg');
        $this->logoPath     = public_path('assets/images/logo_cm.png');
        $this->logoQr       = public_path('assets/images/crp.png');
    }

    /**
     * Prepare all data needed to render the sertifikat.pdf view.
     */
    public function getViewData(Sertifikat $sertifikat): array
    {
        if (!file_exists($this->templatePath)) {
            throw new \RuntimeException('Template sertifikat tidak ditemukan.');
        }

        if (!file_exists($this->logoPath)) {
            throw new \RuntimeException('Logo tidak ditemukan.');
        }

        if (!file_exists($this->logoQr)) {
            throw new \RuntimeException('Logo QR tidak ditemukan.');
        }

        $templatePath = $this->templatePath;
        $logoPath     = $this->logoPath;
        $logoQrPath   = $this->logoQr;

        $data = [
            'sertifikat'     => $sertifikat,
            'backgroundPath' => Cache::remember(
                'sertifikat:base64:background',
                604800,
                fn() => 'data:image/jpeg;base64,' . base64_encode(file_get_contents($templatePath))
            ),
            'logoPath'       => Cache::remember(
                'sertifikat:base64:logo',
                604800,
                fn() => 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            ),
            'qrCodeSvg'      => null,
            'qrLogoPath'     => null,
        ];

        $data['qrCodeSvg']  = $this->generateQrCodeSvg($sertifikat);
        $data['qrLogoPath'] = Cache::remember(
            'sertifikat:base64:logo_qr',
            604800,
            fn() => 'data:image/png;base64,' . base64_encode(file_get_contents($logoQrPath))
        );

        return $data;
    }

    /**
     * Generate QR Code as SVG string (no imagick required).
     * Result is cached per-sertifikat for 30 days.
     */
    private function generateQrCodeSvg(Sertifikat $sertifikat): string
    {
        return Cache::remember("sertifikat:qrcode:{$sertifikat->id}", 2592000, function () use ($sertifikat) {
            $verificationUrl = $sertifikat->getVerificationUrl();

            $svg = QrCode::format('svg')
                ->size(200)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($verificationUrl);

            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        });
    }
}
