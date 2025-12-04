<?php
session_start();
require_once __DIR__ . '/../Class/Database.php';

if (!isset($_SESSION['admin_login'])) { header("Location: ../Auth/login.php"); exit; }

$db = new Database();
$conn = $db->getConnection();

if (isset($_POST['hapus_log'])) {
    $conn->query("TRUNCATE TABLE notifikasi_log");
    $pesan = "Semua log berhasil dibersihkan.";
}

$logs = $conn->query("SELECT * FROM notifikasi_log ORDER BY waktu DESC LIMIT 200");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Notifikasi - Admin Panel</title>
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
            <li class="nav-item"><a class="nav-link text-dark" href="data.php"><i class="bi bi-database-fill me-2"></i>Data AQI</a></li>
            <li class="nav-item"><a class="nav-link active" href="logs.php"><i class="bi bi-envelope-paper-fill me-2"></i>Log Notifikasi</a></li>
        </ul>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-clock-history me-2"></i>Riwayat Pengiriman Email</h6>
                
                <?php if($logs->num_rows > 0): ?>
                <form method="POST" onsubmit="return confirm('Yakin hapus SEMUA log?');">
                    <button type="submit" name="hapus_log" class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i>Bersihkan Log</button>
                </form>
                <?php endif; ?>
            </div>
            
            <div class="card-body p-0">
                <?php if(isset($pesan)): ?>
                    <div class="alert alert-success m-3 py-2 small"><?php echo $pesan; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4">Waktu</th>
                                <th>Penerima</th>
                                <th>Pesan/Konteks</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($logs->num_rows == 0): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat notifikasi.</td></tr>
                            <?php endif; ?>

                            <?php while($row = $logs->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 text-muted small"><?php echo $row['waktu']; ?></td>
                                <td><?php echo htmlspecialchars($row['penerima']); ?></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($row['pesan']); ?></td>
                                <td class="text-center">
                                    <?php if($row['status'] == 'Sent'): ?>
                                        <span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Terikirim</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill"><i class="bi bi-x-circle me-1"></i>Gagal</span>
                                    <?php endif; ?>
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