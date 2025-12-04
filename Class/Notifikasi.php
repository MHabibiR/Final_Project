<?php
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Notifikasi {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function kirimEmail($aqi, $kota) { 
        
        $query = "SELECT email FROM users WHERE is_subscribed = 1";
        $result = $this->db->query($query);
        
        // Cek berapa user yang ketemu
        $jumlahUser = $result->num_rows;

        if ($jumlahUser == 0) {
            return;
        }

        // 2. Setup PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'rahmanhabibi517@gmail.com'; 
            $mail->Password   = 'efwz yjuh txas tmje'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('rahmanhabibi517@gmail.com', 'Sistem Monitoring Polusi Udara UBP');
            
            // 3. Masukkan semua email ke BCC
            while($user = $result->fetch_assoc()) {
                $mail->addBCC($user['email']);
            }

            $mail->isHTML(true);
            $mail->Subject = "BAHAYA: Kualitas Udara Buruk ($aqi)";
            $mail->Body    = "<h3>PERINGATAN!</h3><p>Udara di $kota sedang buruk (AQI: $aqi). Harap gunakan masker!</p>";

            $mail->send();
            
            echo "[SUKSES] Email berhasil dikirim \n";
            $this->catatLaporan("Email ($jumlahUser users)", "Alert AQI: $aqi", "Sent");

        } catch (Exception $e) {
            echo "[ERROR PHPMailer] Gagal kirim: " . $mail->ErrorInfo . "\n";
            $this->catatLaporan("Email", "Error: " . $mail->ErrorInfo, "Failed");
        }
    }

    private function catatLaporan($penerima, $pesan, $status) {
        $perintah_sql = $this->db->prepare("INSERT INTO notifikasi_log (penerima, pesan, status) VALUES (?, ?, ?)");
        $perintah_sql->bind_param("sss", $penerima, $pesan, $status);
        $perintah_sql->execute();
    }
}
?>