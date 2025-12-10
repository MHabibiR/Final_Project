<?php
session_start();
include 'Config/koneksi.php';
require_once __DIR__ . '/Class/Database.php';
require_once __DIR__ . '/Class/PengelolaDataUdara.php';
require_once __DIR__ . '/Class/AnalyticsService.php';

// --- BAGIAN 1: AMBIL DATA REALTIME ---
$queryLatest = "SELECT * FROM riwayat_aqi ORDER BY waktu_catat DESC LIMIT 1";
$resultLatest = mysqli_query($conn, $queryLatest);
$dataLatest = mysqli_fetch_assoc($resultLatest);

if (!$dataLatest) {
    $currentAQI = 0; $currentSuhu = 0; $lastUpdate = "Belum ada data";
} else {
    $currentAQI = $dataLatest['aqi_level'];
    $currentSuhu = $dataLatest['suhu'];
    $lastUpdate = date('d M Y, H:i', strtotime($dataLatest['waktu_catat']));
}

// --- BAGIAN 2: GRAFIK & STATISTIK ---
$db = new Database();
try {
    $repo = new PengelolaDataUdara($db->getConnection());
    $history = $repo->ambilRiwayat(20);
    $labels = []; $values = []; $tempData = [];
    foreach ($history as $row) { $tempData[] = $row; }
    $tempData = array_reverse($tempData); 
    foreach ($tempData as $data) {
        $labels[] = date('H:i', strtotime($data['waktu_catat']));
        $values[] = $data['aqi_level'];
    }

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
} catch (Exception $e) { /* Error Handling */ }

// --- BAGIAN 3: ANALISIS ---
$analytics = new AnalyticsService();
$trendData = $analytics->analyzeTrend($values, $currentAQI);
$trendMsg = $trendData['msg']; $trendColor = $trendData['color']; $trendIcon = $trendData['icon']; $predictionValue = $trendData['prediction'];
list($badgeClass, $statusText) = $analytics->getStatusInfo($currentAQI);

// --- BAGIAN 4: THRESHOLD ---
$querySet = mysqli_query($conn, "SELECT threshold_bahaya FROM pengaturan WHERE id=1 LIMIT 1");
$setting = mysqli_fetch_assoc($querySet);
$batasBahaya = $setting['threshold_bahaya'] ?? 150; 
$isDangerous = ($currentAQI >= $batasBahaya) ? true : false;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Monitoring Polusi Udara Kampus UBP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .bg-orange { background-color: #fd7e14; }
        .card-hover:hover { transform: translateY(-5px); transition: 0.3s; }
        .hero-gradient { background: linear-gradient(135deg, #0d6efd, #0dcaf0); }

        @media (max-width: 991.98px) {
            .navbar-nav .dropdown-menu {
                position: static;      
                display: block;      
                border: none;           
                box-shadow: none;       
                background: transparent; 
                padding-left: 0;        
                margin-top: 0;
            }

            .navbar-nav .dropdown-item {
                color: rgba(255,255,255,0.55); 
                padding: 10px 0;              
                text-align: right;            
            }
            .navbar-nav .dropdown-item:hover {
                background: transparent;
                color: #fff;
            }
            .navbar-nav .dropdown-item i {
                margin-right: 5px;
            }

            .navbar-nav .dropdown-toggle {
                pointer-events: none; 
                color: #fff !important; 
                font-weight: bold;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                margin-bottom: 5px;
            }

            .navbar-nav .dropdown-toggle::after {
                display: none;
            }

            .dropdown-divider {
                display: none;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-cloud-haze2-fill me-2"></i>UBP AirMonitor
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto text-end align-items-lg-center">
                    
                    <?php if(isset($_SESSION['user_login'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                                <li>
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="bi bi-person-gear"></i> Profil Saya
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="Auth/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item mb-2 mb-lg-0 me-lg-2">
                            <a class="btn btn-outline-light btn-sm px-4 rounded-pill" href="Auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm px-4 rounded-pill fw-bold" href="Auth/register.php">Sign In</a>
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
                            <?php echo $trendIcon; ?> <?php echo $trendMsg; ?>
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
                        <p class="text-center text-muted small mt-3">*Berdasarkan 100 data terakhir</p>
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
        // Logic Chart & Alert (Tetap Sama)
        var statusBahaya = <?php echo $isDangerous ? 'true' : 'false'; ?>;
        var angkaBatas = <?php echo $batasBahaya; ?>;
        var aqiSekarang = <?php echo $currentAQI; ?>;

        if (statusBahaya) {
            Swal.fire({
                title: '⚠️ PERINGATAN BAHAYA!',
                text: 'Kualitas udara saat ini buruk!',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }

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
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>