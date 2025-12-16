<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

require_once __DIR__ . '/Class/Database.php';
require_once __DIR__ . '/Class/APIClient.php';
require_once __DIR__ . '/Class/Notifikasi.php';
require_once __DIR__ . '/Class/PengelolaDataUdara.php';
require_once __DIR__ . '/Class/AnalyticsService.php';

if (!getenv('API_TOKEN')) {
    require_once __DIR__ . '/Class/EnvLoader.php';
    EnvLoader::load(__DIR__ . '/.env');
}

$db = new Database();
$conn = $db->getConnection();

// Cek data terakhir
$queryCek = $conn->query("SELECT waktu_catat FROM riwayat_aqi ORDER BY id DESC LIMIT 1");
$lastData = $queryCek->fetch_assoc();
$harusUpdate = false;

if (!$lastData) {
    $harusUpdate = true; 
} else {
    $waktuTerakhir = strtotime($lastData['waktu_catat']);
    $selisihJam = (time() - $waktuTerakhir) / 3600;
    if ($selisihJam >= 1) $harusUpdate = true;
}

// Eksekusi Update ke API
if ($harusUpdate) {
    try {
        $api = new APIClient();
        $data = $api->getLatestAQI();

        if ($data && $data['status'] == 'ok') {
            $aqi = $data['data']['aqi'];
            $suhu = $data['data']['iaqi']['t']['v'] ?? 0;
            $waktu = date('Y-m-d H:i:s');
            $kota = "UBP Karawang"; 

            $perintahsql = $conn->prepare("INSERT INTO riwayat_aqi (kota, aqi_level, suhu, waktu_catat) VALUES (?, ?, ?, ?)");
            $perintahsql->bind_param("sdis", $kota, $aqi, $suhu, $waktu);
            $perintahsql->execute();

            $notifService = new Notifikasi($conn);
            $cekSet = $conn->query("SELECT threshold_bahaya FROM pengaturan WHERE id=1");
            $threshold = $cekSet->fetch_assoc()['threshold_bahaya'] ?? 150;

            if ($aqi > $threshold) {
                $notifService->sendEmailAlert($aqi, $kota);
            }
        }
    } catch (Exception $e) {
    }
}

// 1. Data Realtime
$resLat = $conn->query("SELECT * FROM riwayat_aqi ORDER BY waktu_catat DESC LIMIT 1");
$dataLat = $resLat->fetch_assoc();

if (!$dataLat) {
    $currentAQI = 0; $currentSuhu = 0; $lastUpdate = "Belum ada data";
} else {
    $currentAQI = $dataLat['aqi_level'];
    $currentSuhu = $dataLat['suhu'];
    $lastUpdate = date('d M Y, H:i', strtotime($dataLat['waktu_catat']));
}

// 2. Data Grafik & Tren 
try {
    $repo = new PengelolaDataUdara($conn);
    $history = $repo->ambilRiwayat(20);
    
    $labels = []; $values = []; $temp = [];
    foreach ($history as $row) { $temp[] = $row; }
    $temp = array_reverse($temp); 

    foreach ($temp as $d) {
        $labels[] = date('H:i', strtotime($d['waktu_catat']));
        $values[] = $d['aqi_level'];
    }

    // Pie Chart
    $stats = $repo->ambilStatistikKategori();
    $pieLabels = []; $pieValues = []; $pieColors = [];
    foreach ($stats as $row) {
        $pieLabels[] = $row['kategori'];
        $pieValues[] = $row['jumlah'];
        if (strpos($row['kategori'], 'Baik') !== false) $pieColors[] = '#198754';
        elseif (strpos($row['kategori'], 'Sedang') !== false) $pieColors[] = '#ffc107';
        elseif (strpos($row['kategori'], 'Tidak Sehat') !== false) $pieColors[] = '#fd7e14';
        else $pieColors[] = '#dc3545';
    }

} catch (Exception $e) {
    $labels = []; $values = [];
    $pieLabels = []; $pieValues = []; $pieColors = [];
}

// 3. Analisis Service (Trend & Badge)
$analytics = new AnalyticsService();

// Cek Tren 
if (!empty($values)) {
    $trendData = $analytics->analyzeTrend($values, $currentAQI);
} else {
    $trendData = [
        'msg' => 'Data tidak cukup',
        'color' => 'text-muted',
        'icon' => 'bi-dash',
        'prediction' => 0
    ];
}

$trendMsg       = $trendData['msg'];
$trendColor     = $trendData['color'];
$trendIcon      = $trendData['icon']; 
$predictionValue= $trendData['prediction'];

list($badgeClass, $statusText) = $analytics->getStatusInfo($currentAQI);

$qSet = $conn->query("SELECT threshold_bahaya FROM pengaturan WHERE id=1");
$set = $qSet->fetch_assoc();
$batasBahaya = $set['threshold_bahaya'] ?? 150;
$isDangerous = ($currentAQI >= $batasBahaya);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Monitoring Polusi Udara Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-hover:hover { transform: translateY(-5px); transition: 0.3s; }
        .hero-gradient { background: linear-gradient(135deg, #0d6efd, #0dcaf0); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-cloud-haze2-fill me-2"></i>UBP AirMonitor
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                        <?php if(isset($_SESSION['user_login']) || isset($_SESSION['admin_login'])): ?>
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle me-1"></i> 
                                    <?php 
                                        // Tampilkan Nama yang Sesuai
                                        if(isset($_SESSION['user_name'])) {
                                            echo htmlspecialchars($_SESSION['user_name']); 
                                        } elseif(isset($_SESSION['admin_name'])) {
                                            echo htmlspecialchars($_SESSION['admin_name']) . " (Admin)";
                                        } else {
                                            echo "Akun Saya";
                                        }
                                    ?>
                                </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-gear me-2"></i>Profil Saya</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="Auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-outline-light btn-sm px-4 rounded-pill" href="Auth/login.php">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm px-4 rounded-pill fw-bold" href="Auth/register.php">Daftar Akun</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-gradient text-white text-center py-5 mb-4" style="border-radius: 0 0 20px 20px;">
        <div class="container">
            <h1 class="display-6 fw-bold">Website Monitoring Polusi Udara Kampus</h1>
            <p class="lead mb-0">Universitas Buana Perjuangan Karawang</p>
            <div class="mt-3 badge bg-light text-dark shadow-sm">
                <i class="bi bi-clock me-1"></i> Update: <?php echo $lastUpdate; ?>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="card-body text-center p-4">
                        <h6 class="text-uppercase text-muted fw-bold mb-3">Kualitas Udara Saat Ini</h6>
                        <div class="display-1 fw-bold text-dark mb-0"><?php echo $currentAQI; ?></div>
                        <span class="badge rounded-pill px-3 py-2 mt-2 fs-6 <?php echo $badgeClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                        <div class="mt-4 pt-3 border-top d-flex justify-content-between text-muted">
                            <span><i class="bi bi-thermometer-half"></i> <?php echo $currentSuhu; ?>°C</span>
                            <span><i class="bi bi-geo-alt-fill"></i> UBP Karawang</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="card-body p-4 position-relative overflow-hidden">
                        <h6 class="text-uppercase text-muted fw-bold mb-3">Analisis Tren (1 Jam)</h6>
                        <h2 class="display-6 fw-bold <?php echo $trendColor; ?>">
                            <i class="<?php echo $trendIcon; ?>"></i> <?php echo $trendMsg; ?>
                        </h2>
                        <p class="text-muted small mt-2">
                            Estimasi AQI berikutnya: <strong><?php echo $predictionValue > 0 ? $predictionValue : '-'; ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm bg-dark text-white card-hover">
                    <div class="card-body p-4 d-flex flex-column justify-content-center text-center">
                        <i class="bi bi-file-earmark-spreadsheet display-4 mb-3 text-success"></i>
                        <h5 class="card-title">Data Laporan</h5>
                        <p class="text-white-50 small mb-4">Unduh riwayat lengkap data polusi untuk analisis lebih lanjut.</p>
                        <a href="export_csv.php" class="btn btn-success w-100 rounded-pill mt-auto">
                            <i class="bi bi-download me-2"></i> Download CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-activity me-2"></i>Tren Polusi (24 Jam)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="airChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-pie-chart-fill me-2"></i>Komposisi Kualitas</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px; position: relative;">
                            <canvas id="pieChart"></canvas>
                        </div>
                        <p class="text-center text-muted small mt-3">*Berdasarkan Data Terbaru</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-white py-4 mt-5 border-top">
        <div class="container text-center small text-muted">
            <p class="mb-1">&copy; 2025 Universitas Buana Perjuangan Karawang.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 1. Notifikasi Bahaya
        var statusBahaya = <?php echo $isDangerous ? 'true' : 'false'; ?>;
        var angkaBatas = <?php echo $batasBahaya; ?>;
        var aqiSekarang = <?php echo $currentAQI; ?>;

        if (statusBahaya) {
            Swal.fire({
                title: '⚠️ PERINGATAN BAHAYA!',
                html: 'Kualitas udara saat ini (<b>' + aqiSekarang + '</b>) melebihi batas aman (' + angkaBatas + ').<br>Harap gunakan masker!',
                icon: 'warning',
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#d33',
                allowOutsideClick: false
            });
        }

        // 2. Pie Chart
        const ctxPie = document.getElementById('pieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($pieLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($pieValues); ?>,
                    backgroundColor: <?php echo json_encode($pieColors); ?>,
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // 3. Line Chart
        const ctxLine = document.getElementById('airChart').getContext('2d');
        const gradient = ctxLine.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(13, 110, 253, 0.5)'); 
        gradient.addColorStop(1, 'rgba(13, 110, 253, 0.0)'); 

        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Index AQI',
                    data: <?php echo json_encode($values); ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>