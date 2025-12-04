<?php
session_start();
require_once __DIR__ . '/../Class/Database.php';
require_once __DIR__ . '/../Class/KelolaAdmin.php';

if (!isset($_SESSION['admin_login'])) { header("Location: ../Auth/login.php"); exit; }

$db = new Database();
$adminRepo = new KelolaAdmin($db->getConnection());

$id = $_GET['id'] ?? null;
$data_admin = $adminRepo->id_admin($id);

if (!$data_admin) { die("Admin tidak ditemukan."); }

$pesan = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin = $_POST['username'];
    $sandi = !empty($_POST['password']) ? $_POST['password'] : null;
    
    if ($adminRepo->update_admin($id, $admin, $sandi)) {
        header("Location: admin.php?pesan=updated"); 
        exit;
    } else {
        $error = "Gagal mengupdate data.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow border-0" style="width: 100%; max-width: 450px;">
        <div class="card-header bg-white py-3">
            <h5 class="m-0 fw-bold text-primary">✏️ Edit Administrator</h5>
        </div>
        <div class="card-body p-4">
            
            <?php if($error): ?>
                <div class="alert alert-danger py-2"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($data_admin['username']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti">
                    <div class="form-text small">Biarkan kosong jika hanya ingin mengganti username.</div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary fw-bold">Simpan Perubahan</button>
                    <a href="admin.php" class="btn btn-outline-secondary">Batal / Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>