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
$inisial = strtoupper(substr($user['nama_lengkap'], 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengaturan Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f0f2f5 0%, #e2e6ea 100%);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }
        .card-profile {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .avatar-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 1rem auto;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        }
        .form-floating > label {
            color: #6c757d;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
            background-color: #f8faff;
        }
        .danger-zone {
            border: 1px dashed #dc3545;
            background-color: #fff5f5;
            border-radius: 0.75rem;
        }
        .nav-back:hover {
            transform: translateX(-5px);
            transition: transform 0.2s;
        }
    </style>
</head>
<body class="py-4">

<div class="container">
    <div class="row justify-content-center mb-3">
        <div class="col-md-8 col-lg-5">
            <a href="index.php" class="text-decoration-none text-muted fw-bold small nav-back d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="card card-profile bg-white">
                <div class="card-body p-4 p-md-5">
                    
                    <div class="text-center mb-4">
                        <div class="avatar-circle">
                            <?php echo $inisial; ?>
                        </div>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h4>
                        <p class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>

                    <?php if($pesan): ?>
                        <div class="alert alert-success alert-dismissible fade show small" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $pesan; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-floating mb-3">
                            <input type="text" name="nama" class="form-control" id="floatingNama" 
                                   value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" placeholder="Nama Lengkap" required>
                            <label for="floatingNama">Nama Lengkap</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="floatingEmail" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="name@example.com" required>
                            <label for="floatingEmail">Alamat Email</label>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input type="password" name="password" class="form-control" id="floatingPass" placeholder="Password Baru">
                            <label for="floatingPass">Password Baru (Opsional)</label>
                            <div class="form-text small ms-1">Kosongkan jika tidak ingin mengganti password.</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-3 mb-4">
                            <div>
                                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bell me-2"></i>Notifikasi Email</h6>
                                <small class="text-muted" style="font-size: 0.8rem;">Terima peringatan kualitas udara</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input fs-5" type="checkbox" role="switch" name="is_subscribed" value="1" 
                                       <?php echo $user['is_subscribed'] ? 'checked' : ''; ?> style="cursor: pointer;">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="update_profile" class="btn btn-primary py-2 rounded-3 fw-bold shadow-sm">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                    <div class="danger-zone p-3 mt-5 text-center">
                        <small class="text-danger fw-bold d-block mb-2">ZONE BERBAHAYA</small>
                        <p class="text-muted small mb-3" style="font-size: 0.75rem; line-height: 1.4;">
                            Menghapus akun akan menghilangkan semua data akses Anda secara permanen.
                        </p>
                        <form method="POST" onsubmit="return confirm('PERINGATAN TERAKHIR:\n\nApakah Anda yakin ingin menghapus akun ini secara permanen? Tindakan ini tidak dapat dibatalkan.');">
                            <button type="submit" name="delete_account" class="btn btn-sm btn-outline-danger w-100 rounded-pill">
                                <i class="bi bi-trash me-1"></i> Hapus Akun Saya
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>