<?php
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/EnvLoader.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Notifikasi {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    private function ConfigMailer() {
        if (!getenv('SMTP_HOST')) {
            EnvLoader::load(__DIR__ . '/../.env');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER');
        $mail->Password   = getenv('SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = getenv('SMTP_PORT');

        $mail->setFrom(getenv('SMTP_USER'), 'Sistem Monitoring UBP');

        return $mail;
    }

    // Kirim email
    public function kirimEmail($aqi, $kota) { 
        $query = "SELECT email FROM users WHERE is_subscribed = 1";
        $result = $this->db->query($query);
        $jumlahUser = $result->num_rows;

        if ($jumlahUser == 0) return;

        try {
            $mail = $this->ConfigMailer();
            while($user = $result->fetch_assoc()) {
                $mail->addBCC($user['email']);
            }

            $mail->isHTML(true);
            $mail->Subject = "BAHAYA: Kualitas Udara Buruk ($aqi)";
            $mail->Body    = "<h3>PERINGATAN!</h3><p>Udara di $kota sedang buruk (AQI: $aqi). Harap gunakan masker!</p>";

            $mail->send();
            $this->catatLaporan("Email ($jumlahUser users)", "Alert AQI: $aqi", "Sent");

        } catch (Exception $e) {
            $this->catatLaporan("Email", "Error: " . $mail->ErrorInfo, "Failed");
        }
    }

    // Kirim link reset password 
    public function ResetLink($email, $token) {
        try {
            $mail = $this->ConfigMailer();

            $mail->addAddress($email);

            if (!getenv('APP_URL')) { EnvLoader::load(__DIR__ . '/../.env'); }

            $baseUrl = getenv('APP_URL');
            $link = $baseUrl . "/Auth/reset_password.php?token=" . $token;

            $mail->isHTML(true);
            $mail->Subject = "Reset Password Akun Monitoring";
            $mail->Body    = "
                <h3>Permintaan Reset Password</h3>
                <p>Klik link di bawah ini untuk mereset password Anda:</p>
                <p><a href='$link'>$link</a></p>
                <p>Link ini berlaku selama 1 jam.</p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    // Fungsi Log ke Database
    private function catatLaporan($penerima, $pesan, $status) {
        $stmt = $this->db->prepare("INSERT INTO notifikasi_log (penerima, pesan, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $penerima, $pesan, $status);
        $stmt->execute();
    }
}
?>