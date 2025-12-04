<?php
class AnalyticsService {

    /**
     * Menganalisis tren kenaikan/penurunan polusi berdasarkan data historis
     * @param array $historyValues Array berisi nilai AQI 
     * @param int $currentAQI Nilai AQI saat ini
     * @return array Data hasil analisis (Pesan, Warna, Icon, Prediksi)
     */
    public function analyzeTrend($historyValues, $currentAQI) {
        $result = [
            'msg' => "Data belum cukup",
            'color' => "text-muted",
            'icon' => "⏳",
            'prediction' => 0
        ];

        // Butuh minimal 5 data untuk analisis akurat
        if (count($historyValues) >= 5) {
            $last5 = array_slice($historyValues, -5); 
            $changes = [];
            
            // Hitung selisih (Delta) antar jam
            for ($i = 0; $i < count($last5) - 1; $i++) {
                $changes[] = $last5[$i+1] - $last5[$i];
            }
            
            // Rata-rata perubahan
            $avgChange = array_sum($changes) / count($changes);
            
            // Hitung Prediksi
            $result['prediction'] = round($currentAQI + $avgChange);
            
            // Tentukan Status Tren
            if ($avgChange > 2) {
                $result['msg'] = "WASPADA: Tren Naik Cepat!";
                $result['color'] = "text-danger"; // Merah
                $result['icon'] = "📈";
            } elseif ($avgChange > 0) {
                $result['msg'] = "Tren Sedikit Meningkat";
                $result['color'] = "text-warning"; // Kuning
                $result['icon'] = "↗️";
            } elseif ($avgChange < -2) {
                $result['msg'] = "Kabar Baik: Udara Membaik";
                $result['color'] = "text-success"; // Hijau
                $result['icon'] = "📉";
            } else {
                $result['msg'] = "Kondisi Stabil";
                $result['color'] = "text-primary"; // Biru
                $result['icon'] = "➡️";
            }
        }

        return $result;
    }

    /**
     * Menentukan Warna Badge dan Teks Status berdasarkan AQI
     * @param int $aqi
     * @return array [class_css, text_status]
     */
    public function getStatusInfo($aqi) {
        if ($aqi <= 50) return ['bg-success', 'Baik (Good)'];
        if ($aqi <= 100) return ['bg-warning text-dark', 'Sedang (Moderate)'];
        if ($aqi <= 150) return ['bg-orange text-white', 'Tidak Sehat (Sensitif)'];
        if ($aqi <= 200) return ['bg-danger', 'Tidak Sehat'];
        return ['bg-dark', 'Berbahaya'];
    }
}
?>