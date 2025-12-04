<?php
session_start();
require_once __DIR__ . '/../Class/Database.php';

if (!isset($_SESSION['admin_login'])) { header("Location: ../Auth/login.php"); exit; }

$db = new Database();
$conn = $db->getConnection();

// --- HAPUS DATA ---
if (isset($_POST['hapus_id'])) {
    $id = $_POST['hapus_id'];
    $perintah_sql = $conn->prepare("DELETE FROM riwayat_aqi WHERE id = ?");
    $perintah_sql->bind_param("i", $id);
    if($perintah_sql->execute()){
        $pesan = "Data berhasil dihapus.";
    }
}

// Ambil Data (Limit 100)
$result = $conn->query("SELECT * FROM riwayat_aqi ORDER BY waktu_catat DESC LIMIT 100");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data AQI - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin.php">⚙️ Admin Panel</a>
            <div class="d-flex">
                <a href="../Auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm">
            <li class="nav-item"><a class="nav-link text-dark" href="admin.php"><i class="bi bi-people-fill me-2"></i>Admin & Config</a></li>
            <li class="nav-item"><a class="nav-link active" href="data.php"><i class="bi bi-database-fill me-2"></i>Data AQI</a></li>
            <li class="nav-item"><a class="nav-link text-dark" href="logs.php"><i class="bi bi-envelope-paper-fill me-2"></i>Log Notifikasi</a></li>
        </ul>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-table me-2"></i>Riwayat Data AQI (100 Terakhir)</h6>
                <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
            </div>
            <div class="card-body p-0">
                <?php if(isset($pesan)): ?>
                    <div class="alert alert-success m-3 py-2 small"><?php echo $pesan; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4">Waktu</th>
                                <th>Kota</th>
                                <th>AQI Level</th>
                                <th>Suhu</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): 
                                // Logika Warna Badge Sederhana
                                $aqi = $row['aqi_level'];
                                $warna_status = 'bg-secondary';
                                if($aqi <= 50) $warna_status = 'bg-success';
                                elseif($aqi <= 100) $warna_status = 'bg-warning text-dark';
                                elseif($aqi <= 150) $warna_status = 'bg-orange text-white';
                                else $warna_status = 'bg-danger';
                            ?>
                            <tr>
                                <td class="ps-4"><?php echo date('d M Y, H:i', strtotime($row['waktu_catat'])); ?></td>
                                <td><i class="bi bi-geo-alt text-muted me-1"></i><?php echo htmlspecialchars($row['kota']); ?></td>
                                <td>
                                    <span class="badge rounded-pill <?php echo $warna_status; ?>" style="<?php if($aqi > 100 && $aqi <= 150) echo 'background-color: #fd7e14;'; ?>">
                                        <?php echo $aqi; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['suhu']; ?>°C</td>
                                <td class="text-end pe-4">
                                    <form method="POST" onsubmit="return confirm('Hapus data ini permanen?');">
                                        <input type="hidden" name="hapus_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>