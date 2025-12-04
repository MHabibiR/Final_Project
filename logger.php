<?php
require_once __DIR__ . '/Class/Database.php';
require_once __DIR__ . '/Class/ApiClient.php';
require_once __DIR__ . '/Class/PengelolaDataUdara.php';
require_once __DIR__ . '/Class/Notifikasi.php'; 

echo "--- Memulai Proses Logging ---\n";

$db = new Database();
$conn = $db->getConnection();
$repo = new PengelolaDataUdara($conn);
$notifService = new Notifikasi($conn);

// --- CEK CACHE / TTL (Time To Live) ---
// Aturan: Jangan ambil data jika data terakhir umurnya < 60 menit
if ($repo->cekDataBaru(60)) {
    echo "[SKIP] Data terakhir masih baru (< 60 menit). Tidak perlu request API.\n";
    exit; 
}

// Ambil Setting
$settings = $repo->ambilPengaturan();
if (!$settings) die("Setting kosong.\n");

$token = $settings['api_token'];
$lat   = $settings['latitude'];
$lon   = $settings['longitude'];
$batasBahaya = $settings['threshold_bahaya']; 
$emailAdmin  = $settings['email_admin'];      

// --- LAKUKAN REQUEST API ---
$api = new ApiClient($token);
$result = $api->getAirQualityByGeo($lat, $lon);

if (isset($result['status']) && $result['status'] == 'ok') {
    $aqi = $result['data']['aqi'];
    $suhu = $result['data']['iaqi']['t']['v'] ?? 0;
    $lokasiDB = "UBP Karawang";

    // Simpan ke DB
    if ($repo->simpanCatatan($lokasiDB, $aqi, $suhu)) {
        echo "[SUKSES] Data AQI: $aqi tersimpan.\n";

        // Cek apakah AQI melebihi ambang batas bahaya?
        if ($aqi >= $batasBahaya) {
            echo "[PERINGATAN] AQI ($aqi) melebihi batas ($batasBahaya). Mengirim email...\n";
            
            // Panggil Service untuk kirim email
            $notifService->kirimEmail($aqi, $lokasiDB);
        }

    } else {
        echo "[GAGAL DB] " . $conn->error . "\n";
    }
} else {
    echo "[API ERROR] Gagal ambil data.\n";
}
?>