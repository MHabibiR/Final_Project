<?php
class EnvLoader {
    /**
     * Membaca file .env dan menyimpannya ke variabel environment server
     */
    public static function load($path) {
        if (!file_exists($path)) {
            throw new Exception("File .env tidak ditemukan di: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Pisahkan Key=Value
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Simpan ke Environment Variable PHP
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}
?>