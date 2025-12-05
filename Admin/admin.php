<?php
session_start();

require_once '../Class/Database.php';
require_once '../Class/KelolaAdmin.php';
require_once '../Class/PengelolaDataUdara.php';

// Cek Login
if (!isset($_SESSION['admin_login'])) { header("Location: ../Auth/login.php"); exit; }

$db = new Database();
$conn = $db->getConnection();
$adminRepo = new KelolaAdmin($conn);
$airRepo = new PengelolaDataUdara($conn); 

$pesan = "";

// LOGIKA: MENANGANI REQUEST POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. UPDATE KONFIGURASI API 
    if (isset($_POST['perbarui_data'])) {
        $token = $_POST['API_token'];
        $lat   = $_POST['latitude'];
        $lon   = $_POST['longitude'];
        $thres = $_POST['threshold'];

        // Query Update
        $perintah_sql = $conn->prepare("UPDATE pengaturan SET api_token=?, latitude=?, longitude=?, threshold_bahaya=? WHERE id=1");
        $perintah_sql->bind_param("sssi", $token, $lat, $lon, $thres);
        
        if ($perintah_sql->execute()) {
            $pesan = "✅ Konfigurasi berhasil diperbarui!";
        } else {
            $pesan = "❌ Gagal menyimpan konfigurasi: " . $conn->error;
        }
    }

    // 2. Tambah Admin
    if (isset($_POST['tambah_admin'])) {
        if ($adminRepo->tambah_admin($_POST['username_baru'], $_POST['email_baru'], $_POST['password_baru'])) {
            $pesan = "✅ Admin berhasil ditambahkan!";
        } else {
            $pesan = "❌ Gagal (Username atau email sudah ada).";
        }
    }

    // 3. Hapus Admin
    if (isset($_POST['hapus_id'])) {
        if ($_POST['hapus_id'] == 1) {
            $pesan = "❌ Admin Utama tidak boleh dihapus!";
        } else {
            $adminRepo->hapus_admin($_POST['hapus_id']);
            $pesan = "✅ Admin berhasil dihapus.";
        }
    }
}

/* Mengambil data pengaturan di database */
$data = $airRepo->ambilPengaturan();

// Jaga-jaga jika database kosong
if (!$data) {
    $data = ['api_token' => '', 'latitude' => '', 'longitude' => '', 'threshold_bahaya' => ''];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Monitoring Udara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin.php">⚙️ Admin Panel</a>
            <div class="d-flex">
                <a href="../Auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm">
            <li class="nav-item">
                <a class="nav-link active" href="admin.php"><i class="bi bi-people-fill me-2"></i>Admin & Config</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="data.php"><i class="bi bi-database-fill me-2"></i>Data AQI</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="logs.php"><i class="bi bi-envelope-paper-fill me-2"></i>Log Notifikasi</a>
            </li>
        </ul>

        <?php if ($pesan): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo $pesan; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-sliders me-2"></i>Konfigurasi API
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Token API AQICN</label>
                                <input type="text" name="API_token" class="form-control font-monospace bg-light" value="<?php echo htmlspecialchars($data['api_token']); ?>" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label fw-bold">Latitude</label>
                                    <input type="text" name="latitude" class="form-control" value="<?php echo htmlspecialchars($data['latitude']); ?>" required>
                                </div>
                                <div class="col">
                                    <label class="form-label fw-bold">Longitude</label>
                                    <input type="text" name="longitude" class="form-control" value="<?php echo htmlspecialchars($data['longitude']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Threshold Bahaya (AQI)</label>
                                <input type="number" name="threshold" class="form-control" value="<?php echo htmlspecialchars($data['threshold_bahaya']); ?>" required>
                                <div class="form-text">Batas angka untuk memicu notifikasi email.</div>
                            </div>
                            <button type="submit" name="perbarui_data" class="btn btn-primary w-100">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-secondary text-white fw-bold">
                        <i class="bi bi-person-plus-fill me-2"></i>Manajemen Admin
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-2 mb-4">
                            <div class="col-md-4">
                                <input type="text" name="username_baru" class="form-control" placeholder="Username Baru" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="email_baru" class="form-control" placeholder="Email Baru" required>
                            </div>
                            <div class="col-md-3">
                                <input type="password" name="password_baru" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" name="tambah_admin" class="btn btn-success w-100"><i class="bi bi-plus-lg"></i></button>
                            </div>
                        </form>

                        <h6 class="fw-bold border-bottom pb-2">Daftar Admin</h6>
                        <ul class="list-group list-group-flush">
                            <?php 
                            $admins = $adminRepo->semua_admin();
                            while($row = $admins->fetch_assoc()): 
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>
                                    <i class="bi bi-person-badge me-2 text-muted"></i>
                                    <?php echo htmlspecialchars($row['username']); ?>
                                </span>
                                <div>
                                    <a href="edit_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil-fill"></i></a>
                                    <?php if($row['id'] != 1): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus admin ini?');">
                                            <input type="hidden" name="hapus_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash-fill"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>