<?php
class PengelolaDataUdara {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Mengambil konfigurasi Admin (Token & Lokasi)
    public function ambilPengaturan() {
        $query = "SELECT * FROM pengaturan WHERE id = 1 LIMIT 1";
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }

    // Menyimpan data hasil scan ke database
    public function simpanCatatan($kota, $aqi, $suhu) {
        $perintah_sql = $this->conn->prepare("INSERT INTO riwayat_aqi (kota, aqi_level, suhu) VALUES (?, ?, ?)");
        $perintah_sql->bind_param("sid", $kota, $aqi, $suhu);
        return $perintah_sql->execute();
    }

    // Mengambil data terakhir (untuk Dashboard Realtime)
    public function ambilDataTerbaru() {
        $query = "SELECT * FROM riwayat_aqi ORDER BY waktu_catat DESC LIMIT 1";
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }

    // Mengambil riwayat untuk grafik
    public function ambilRiwayat($limit = 20) {
        $query = "SELECT aqi_level, waktu_catat FROM riwayat_aqi ORDER BY waktu_catat DESC LIMIT ?";
        $perintah_sql = $this->conn->prepare($query);
        $perintah_sql->bind_param("i", $limit);
        $perintah_sql->execute();
        return $perintah_sql->get_result();
    }

    // Mengambil semua data tanpa limit untuk CSV
    public function ambilSemuaData() {
        $result = $this->conn->query("SELECT * FROM riwayat_aqi ORDER BY waktu_catat DESC");
        return $result;
    }

    // Menghitung jumlah data berdasarkan kategori dari 100 data terakhir untuk pie chart
    public function ambilStatistikKategori() {
        $sql = "SELECT 
                    CASE 
                        WHEN aqi_level <= 50 THEN 'Baik (Hijau)'
                        WHEN aqi_level <= 100 THEN 'Sedang (Kuning)'
                        WHEN aqi_level <= 150 THEN 'Tidak Sehat (Oranye)'
                        ELSE 'Berbahaya (Merah/Ungu)'
                    END as kategori,
                    COUNT(*) as jumlah
                FROM (SELECT aqi_level FROM riwayat_aqi ORDER BY waktu_catat DESC LIMIT 100) as subquery
                GROUP BY kategori";
        
        $result = $this->conn->query($sql);
        
        $stats = [];
        while($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        return $stats;
    }

    /**
     * Cek apakah data terakhir masih belum kadaluarsa
     * @param int 
     * @return bool 
     */
    public function cekDataBaru($menit = 60) {
        $query = "SELECT waktu_catat FROM riwayat_aqi ORDER BY waktu_catat DESC LIMIT 1";
        $result = $this->conn->query($query);
        $data = $result->fetch_assoc();

        if (!$data) {
            return false;
        }

        $waktuTerakhir = strtotime($data['waktu_catat']);
        $waktuSekarang = time();
        $selisihMenit = ($waktuSekarang - $waktuTerakhir) / 60;

        if ($selisihMenit < $menit) {
            return true; 
        }
        
        return false;
    }
}
?>