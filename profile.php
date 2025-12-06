<?php
session_start();
require_once __DIR__ . '/Class/Database.php';
require_once __DIR__ . '/Class/UserRepository.php';

if (!isset($_SESSION['user_login'])) { header("Location: Auth/login.php"); exit; }

$db = new Database();
$conn = $db->getConnection();
$userRepo = new UserRepository($conn); 
$id_user = $_SESSION['user_id'];
$pesan = "";
$error = "";


if (isset($_POST['update_profile'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $is_sub = isset($_POST['is_subscribed']) ? 1 : 0; 
    $password = $_POST['password']; 

    $cek = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $cek->bind_param("si", $email, $id_user);
    $cek->execute();
    
    if ($cek->get_result()->num_rows > 0) {
        $error = "Email sudah digunakan orang lain.";
    } else {
        if (!empty($password)) {
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $perintahsql = $conn->prepare("UPDATE users SET nama_lengkap=?, email=?, password=?, is_subscribed=? WHERE id=?");
            $perintahsql->bind_param("sssii", $nama, $email, $passHash, $is_sub, $id_user);
        } else {
            $perintahsql = $conn->prepare("UPDATE users SET nama_lengkap=?, email=?, is_subscribed=? WHERE id=?");
            $perintahsql->bind_param("ssii", $nama, $email, $is_sub, $id_user);
        }

        if ($perintahsql->execute()) {
            $pesan = "Profil berhasil diperbarui!";
            $_SESSION['user_name'] = $nama; 
        } else {
            $error = "Gagal update profil.";
        }
    }
}

if (isset($_POST['delete_account'])) {
    $perintahsql = $conn->prepare("DELETE FROM users WHERE id = ?");
    $perintahsql->bind_param("i", $id_user);
    
    if ($perintahsql->execute()) {
        session_unset();
        session_destroy();
        header("Location: Auth/login.php?pesan=Akun berhasil dihapus. Sampai jumpa!");
        exit;
    } else {
        $error = "Gagal menghapus akun.";
    }
}

$user = $userRepo->getUserById($id_user);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">⬅ Kembali ke Dashboard</a>
    </div>
</nav>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 fw-bold text-primary"><i class="bi bi-person-circle me-2"></i>Profil Saya</h5>
                </div>
                <div class="card-body p-4">
                    
                    <?php if($pesan): ?><div class="alert alert-success py-2"><?php echo $pesan; ?></div><?php endif; ?>
                    <?php if($error): ?><div class="alert alert-danger py-2"><?php echo $error; ?></div><?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ganti Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti">
                        </div>

                        <div class="form-check mb-4 bg-light p-3 rounded border">
                            <input class="form-check-input" type="checkbox" name="is_subscribed" value="1" id="sub" <?php echo $user['is_subscribed'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sub">
                                <strong>Terima Notifikasi Email</strong><br>
                                <span class="text-muted small">Hilangkan centang jika Anda ingin berhenti berlangganan (Unsubscribe).</span>
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="update_profile" class="btn btn-primary fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-danger small fw-bold mb-2">Area Berbahaya</p>
                        <form method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? \nAkun yang dihapus tidak dapat dikembalikan!');">
                            <button type="submit" name="delete_account" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash me-1"></i> Hapus Akun Saya Permanen
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>