<?php

namespace App\Services;

use Illuminate\Support\Str;

class TwoFactorAuthService
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random 16-character Base32 TOTP secret key.
     */
    public function generateSecretKey(int $length = 16): string
    {
        $secret = '';
        $chars = self::BASE32_CHARS;
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, $max)];
        }

        return $secret;
    }

    /**
     * Build the standard otpauth:// URI for Google Authenticator, Authy, 1Password, Microsoft Authenticator, etc.
     */
    public function getOtpAuthUrl(string $issuer, string $email, string $secret): string
    {
        $encodedIssuer = rawurlencode($issuer);
        $encodedEmail = rawurlencode($email);

        return "otpauth://totp/{$encodedIssuer}:{$encodedEmail}?secret={$secret}&issuer={$encodedIssuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Calculate the 6-digit TOTP code for a given Base32 secret and time slice (30s window).
     */
    public function getCode(string $secret, ?int $timeSlice = null): string
    {
        if ($timeSlice === null) {
            $timeSlice = (int) floor(time() / 30);
        }

        $secretKey = $this->base32Decode($secret);
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        $hmac = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashPart = substr($hmac, $offset, 4);

        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        $modulo = 10 ** 6;

        return str_pad((string) ($value % $modulo), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit TOTP code allowing +/- 1 time window (30 seconds) clock drift.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $cleanCode = preg_replace('/\s+/', '', $code);
        if (!preg_match('/^\d{6}$/', $cleanCode)) {
            return false;
        }

        $currentTimeSlice = (int) floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculated = $this->getCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculated, $cleanCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate 8 one-time recovery codes (e.g. ABCD-EFGH).
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(5) . '-' . Str::random(5));
        }

        return $codes;
    }

    /**
     * Decode a Base32 string into raw binary bytes.
     */
    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
        if ($secret === '') {
            return '';
        }

        $chars = self::BASE32_CHARS;
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        $len = strlen($secret);
        for ($i = 0; $i < $len; $i++) {
            $val = strpos($chars, $secret[$i]);
            if ($val === false) {
                continue;
            }
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
