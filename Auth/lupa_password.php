<?php
require_once __DIR__ . '/../Config/koneksi.php';
require_once __DIR__ . '/../Class/Database.php';
require_once __DIR__ . '/../Class/Notifikasi.php';

$pesan = "";
$error = "";

if (isset($_POST['reset'])) {
    $db = new Database();
    $conn = $db->getConnection();
    $notif = new Notifikasi($conn);
    
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32)); 
    $expiry = date('Y-m-d H:i:s', time() + 3600); 
    
    $perintahsql = $conn->prepare("UPDATE admin SET reset_token=?, reset_expires=? WHERE email=?");
    $perintahsql->bind_param("sss", $token, $expiry, $email);
    $perintahsql->execute();

    if ($perintahsql->affected_rows == 0) {
        $perintahsql2 = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
        $perintahsql2->bind_param("sss", $token, $expiry, $email);
        $perintahsql2->execute();
        
        if ($perintahsql2->affected_rows == 0) {
            $error = "Email tidak ditemukan!";
        } else {
            if ($notif->ResetLink($email, $token)) {
                $pesan = "Link reset telah dikirim ke email Anda.";
            } else {
                $error = "Gagal mengirim email.";
            }
        }
    } else {
        if ($notif->ResetLink($email, $token)) {
            $pesan = "Link reset telah dikirim ke email Anda.";
        } else {
            $error = "Gagal mengirim email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Monitoring UBP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .card-custom {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
    
    <div class="card card-custom p-4 p-md-5" style="max-width: 450px; width: 100%;">
        <div class="text-center mb-4">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-key-fill fs-2"></i>
            </div>
            <h4 class="fw-bold text-secondary">Lupa Password?</h4>
            <p class="text-muted small">Jangan khawatir. Masukkan email Anda dan kami akan mengirimkan instruksi reset.</p>
        </div>
        
        <?php if($pesan): ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?php echo $pesan; ?></div>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary small">EMAIL TERDAFTAR</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="nama@email.com" required>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" name="reset" class="btn btn-primary btn-lg shadow-sm">
                    <i class="bi bi-send me-2"></i> Kirim Link Reset
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="login.php" class="text-decoration-none text-muted small fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
            </a>
        </div>
    </div>

</body>
</html>