<?php

namespace App\Support;

/**
 * Utilitas normalisasi UID kartu RFID.
 *
 * Scanner RFID USB (tipe "keyboard emulator"/HID) umumnya mengetik UID kartu
 * dalam format DESIMAL 10 digit (mis. "0012345678"), sedangkan scanner jaringan
 * (ESP32 + RC522) mengirim UID dalam format HEX (mis. "A1B2C3D4").
 * Selain itu, beberapa reader membalik urutan byte (little-endian).
 *
 * Kelas ini menghasilkan semua kemungkinan representasi UID agar satu kartu
 * tetap dikenali apa pun format reader yang digunakan.
 */
class RfidUid
{
    /**
     * Bersihkan input mentah: hapus spasi, titik dua, strip, karakter kontrol.
     */
    public static function clean(?string $raw): string
    {
        $raw = strtoupper(trim((string) $raw));

        // Hapus prefix "0X" jika reader mengirim format 0xA1B2C3D4
        if (str_starts_with($raw, '0X')) {
            $raw = substr($raw, 2);
        }

        return preg_replace('/[^0-9A-Z]/', '', $raw) ?? '';
    }

    /**
     * Daftar kandidat UID (dalam berbagai format) untuk pencarian di database.
     *
     * @return string[]
     */
    public static function candidates(?string $raw): array
    {
        $uid = self::clean($raw);
        if ($uid === '') {
            return [];
        }

        $candidates = [$uid];

        // 1. Input desimal -> konversi ke HEX (big-endian & little-endian)
        if (ctype_digit($uid) && strlen($uid) <= 18) {
            $number = ltrim($uid, '0');
            $number = $number === '' ? '0' : $number;

            // Pastikan tidak overflow integer 64-bit
            if (strlen($number) < 19) {
                $hex = strtoupper(dechex((int) $number));
                $hex = self::padHex($hex);

                $candidates[] = $hex;
                $candidates[] = self::reverseBytes($hex);
                $candidates[] = ltrim($hex, '0');
            }

            // Variasi padding desimal (mis. "12345678" vs "0012345678")
            $candidates[] = $number;
            $candidates[] = str_pad($number, 10, '0', STR_PAD_LEFT);
        }

        // 2. Input HEX -> konversi ke desimal (big-endian & little-endian)
        //    Angka murni hanya dianggap HEX jika panjangnya khas UID HEX (8 / 14 karakter)
        $looksHex = ctype_xdigit($uid) && (!ctype_digit($uid) || in_array(strlen($uid), [8, 14], true));
        if ($looksHex && strlen($uid) <= 14) {
            $hex = self::padHex($uid);
            $candidates[] = $hex;

            foreach ([$hex, self::reverseBytes($hex)] as $variant) {
                $dec = (string) hexdec($variant);
                $candidates[] = $variant;
                $candidates[] = $dec;
                $candidates[] = str_pad($dec, 10, '0', STR_PAD_LEFT);
            }
        }

        return array_values(array_unique(array_filter($candidates, fn ($c) => $c !== '')));
    }

    /**
     * Padding HEX ke jumlah byte genap (4 byte / 7 byte umum untuk Mifare).
     */
    private static function padHex(string $hex): string
    {
        $len = strlen($hex);
        if ($len <= 8) {
            return str_pad($hex, 8, '0', STR_PAD_LEFT);
        }
        if ($len <= 14) {
            return str_pad($hex, 14, '0', STR_PAD_LEFT);
        }
        return strlen($hex) % 2 === 0 ? $hex : '0' . $hex;
    }

    /**
     * Balik urutan byte HEX ("A1B2C3D4" -> "D4C3B2A1").
     */
    private static function reverseBytes(string $hex): string
    {
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }
        return implode('', array_reverse(str_split($hex, 2)));
    }
}
