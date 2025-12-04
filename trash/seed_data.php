<?php
require_once __DIR__ . '/../Class/Database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h1>🌱 Memulai Proses Seeding Data Dummy...</h1>";
echo "<pre>";

// KONFIGURASI
$jumlahData = 100; 
$kota = "UBP Karawang";


$waktuAwal = time() - ($jumlahData * 3600); 

for ($i = 0; $i < $jumlahData; $i++) {
    
    // 1. Buat Waktu (Maju 1 jam setiap loop)
    $timestamp = $waktuAwal + ($i * 3600);
    $waktuSQL = date('Y-m-d H:i:s', $timestamp);
    
    // 2. Buat AQI 
    $aqiBase = 80 + (40 * sin($i / 10)); 
    $aqiRandom = rand(-15, 15); 
    $aqi = round($aqiBase + $aqiRandom);
    
    if ($aqi < 0) $aqi = 10;

    // 3. Buat Suhu berdasarkan Waktu 
    $jam = date('H', $timestamp);
    if ($jam >= 6 && $jam <= 18) {
        $suhu = rand(30, 34);
    } else {
        $suhu = rand(24, 28);
    }

    // 4. Masukkan ke Database
    $sql = "INSERT INTO riwayat_aqi (kota, aqi_level, suhu, waktu_catat) VALUES ('$kota', '$aqi', '$suhu', '$waktuSQL')";
    
    if ($conn->query($sql)) {
        
        // Tentukan warna status untuk log di layar
        if ($aqi <= 50) {
            $status = "Baik";
            $color = "green";
        } elseif ($aqi <= 100) {
            $status = "Sedang";
            $color = "#d4ac0d"; 
        } elseif ($aqi <= 150) {
            $status = "Tidak Sehat (Sensitif)";
            $color = "orange";
        } elseif ($aqi <= 200) {
            $status = "Tidak Sehat";
            $color = "red";
        } else {
            $status = "Berbahaya";
            $color = "purple";
        }
        
        echo "[$i] $waktuSQL -> AQI: <b style='color:$color'>$aqi ($status)</b>, Suhu: {$suhu}°C ... OK\n";
    } else {
        echo "[$i] Gagal: " . $conn->error . "\n";
    }
}

echo "</pre>";
echo "<h2>✅ SELESAI! Data dummy berhasil dibuat.</h2>";
echo "<a href='index.php'>Lihat Dashboard Sekarang &rarr;</a>";
?>