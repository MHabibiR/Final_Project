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
        try {
            $query = "SELECT email FROM users WHERE is_subscribed = 1";
            $result = $this->db->query($query);
            
            if ($result->num_rows > 0) {
                $mail = $this->ConfigMailer();

                $jumlahUser = 0;
                while ($row = $result->fetch_assoc()) {
                    $mail->addBCC($row['email']);
                    $jumlahUser++;
                }

                $warnaHeader = '#dc3545';
                $statusTeks = 'BERBAHAYA / TIDAK SEHAT';
                if($aqi > 300) { $warnaHeader = '#7e1620'; $statusTeks = 'HAZARDOUS (SANGAT BERBAHAYA)'; }
                
                $mail->isHTML(true);
                $mail->Subject = "⚠️ PERINGATAN: Kualitas Udara BURUK di $kota ($aqi AQI)";
                
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                    
                    <div style='background-color: $warnaHeader; color: #ffffff; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0; font-size: 24px;'>PERINGATAN KUALITAS UDARA</h2>
                    </div>

                    <div style='padding: 30px; background-color: #ffffff; text-align: center;'>
                        <p style='color: #666666; font-size: 16px; margin-bottom: 10px;'>Kualitas udara di wilayah <strong>$kota</strong> saat ini:</p>
                        
                        <div style='font-size: 56px; font-weight: bold; color: $warnaHeader; margin: 10px 0;'>
                            $aqi
                        </div>
                        <div style='display: inline-block; background-color: #f8d7da; color: #721c24; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 14px;'>
                            $statusTeks
                        </div>

                        <hr style='border: 0; border-top: 1px solid #eeeeee; margin: 25px 0;'>

                        <p style='color: #333333; font-weight: bold;'>Mohon lakukan tindakan pencegahan:</p>
                        
                        <div style='text-align: left; background-color: #f9f9f9; padding: 15px; border-radius: 5px; display: inline-block;'>
                            <ul style='margin: 0; padding-left: 20px; color: #555555;'>
                                <li style='margin-bottom: 8px;'>😷 <strong>Gunakan Masker</strong> saat beraktivitas di luar.</li>
                                <li style='margin-bottom: 8px;'>🏠 <strong>Tutup Jendela</strong> dan pintu rumah.</li>
                                <li style='margin-bottom: 8px;'>🚫 <strong>Kurangi Aktivitas</strong> fisik berat di luar ruangan.</li>
                                <li>💨 Nyalakan <strong>Air Purifier</strong> jika tersedia.</li>
                            </ul>
                        </div>
                    </div>

                    <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #888888;'>
                        <p style='margin: 0;'>Pesan otomatis dari Sistem Monitoring Polusi Udara UBP.</p>
                        <p style='margin: 5px 0 0 0;'>Waktu: " . date('d M Y, H:i') . " WIB</p>
                        <p style='margin: 5px 0 0 0;'><a href='#' style='color: #888888;'>Login ke Website</a> untuk detail lebih lanjut.</p>
                    </div>
                </div>
                ";

                $mail->AltBody = "PERINGATAN! Kualitas udara di $kota mencapai angka $aqi (Sangat Buruk). Harap gunakan masker dan kurangi aktivitas luar ruangan.";

                $mail->send();
                
                $this->catatLaporan("Broadcast Email", "Alert AQI: $aqi dikirim ke $jumlahUser user", "Sent");
            }
        } catch (Exception $e) {
            $this->catatLaporan("Broadcast Email", "Error: " . $mail->ErrorInfo, "Failed");
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