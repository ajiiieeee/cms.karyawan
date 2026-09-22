<?php

namespace App\Helpers;

class IdEncryptor
{
    /**
     * Encrypt an ID for safe URL exposure.
     */
    public static function encrypt(int|string $id): string
    {
        $key = substr(hash('sha256', config('app.key')), 0, 32);
        $iv = substr(hash('sha256', 'id-encryptor-iv'), 0, 16);
        $encrypted = openssl_encrypt((string) $id, 'AES-256-CBC', $key, 0, $iv);

        return rtrim(strtr($encrypted, '+/', '-_'), '=');
    }

    /**
     * Decrypt an encrypted ID from URL.
     */
    public static function decrypt(string $encrypted): ?int
    {
        try {
            $key = substr(hash('sha256', config('app.key')), 0, 32);
            $iv = substr(hash('sha256', 'id-encryptor-iv'), 0, 16);

            $data = strtr($encrypted, '-_', '+/');
            $padding = strlen($data) % 4;
            if ($padding) {
                $data .= str_repeat('=', 4 - $padding);
            }

            $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);

            if ($decrypted === false || !is_numeric($decrypted)) {
                return null;
            }

            return (int) $decrypted;
        } catch (\Exception $e) {
            return null;
        }
    }
}
