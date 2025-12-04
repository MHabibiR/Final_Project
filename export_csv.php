<?php
require_once __DIR__ . '/Class/Database.php';
require_once __DIR__ . '/Class/PengelolaDataUdara.php';

// Inisialisasi
$db = new Database();
try {
    $repo = new PengelolaDataUdara($db->getConnection());
    $data = $repo->ambilSemuaData();
} catch (Exception $e) {
    die("Error Database: " . $e->getMessage());
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Laporan_Polusi_UBP_' . date('Y-m-d') . '.csv"');

// Buka Output Stream
$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'Lokasi', 'AQI Level', 'Suhu (C)', 'Waktu Pencatatan']);

if ($data->num_rows > 0) {
    while ($row = $data->fetch_assoc()) {
        fputcsv($output, [
            $row['id'], 
            $row['kota'], 
            $row['aqi_level'], 
            $row['suhu'], 
            $row['waktu_catat']
        ]);
    }
} else {
    fputcsv($output, ['Data Masih Kosong']);
}

fclose($output);
exit;
?>