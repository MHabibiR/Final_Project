<?php
require_once __DIR__ . '/../Class/Database.php';

$token = $_GET['token'] ?? '';
$error = "";

if (isset($_POST['password_baru'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $now = date('Y-m-d H:i:s');

    $perintahsql = $conn->prepare("UPDATE admin SET password=?, reset_token=NULL, reset_expires=NULL WHERE reset_token=? AND reset_expires > ?");
    $perintahsql->bind_param("sss", $pass, $token, $now);
    $perintahsql->execute();

    if ($perintahsql->affected_rows == 0) {
        $perintahsql2 = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE reset_token=? AND reset_expires > ?");
        $perintahsql2->bind_param("sss", $pass, $token, $now);
        $perintahsql2->execute();

        if ($perintahsql2->affected_rows > 0) {
            header("Location: login.php?pesan=Password berhasil diubah, silakan login.");
            exit;
        } else {
            $error = "Token tidak valid atau sudah kadaluarsa.";
        }
    } else {
        header("Location: login.php?pesan=Password Admin berhasil diubah.");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru</title>
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
        .cursor-pointer { cursor: pointer; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
    
    <div class="card card-custom p-4 p-md-5" style="max-width: 450px; width: 100%;">
        <div class="text-center mb-4">
            <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-shield-lock-fill fs-2"></i>
            </div>
            <h4 class="fw-bold text-secondary">Password Baru</h4>
            <p class="text-muted small">Silakan buat password baru yang aman untuk akun Anda.</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary small">PASSWORD BARU</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="inputPassword" class="form-control border-start-0 border-end-0 ps-0" placeholder="Minimal 6 karakter" required minlength="6">
                    <span class="input-group-text bg-white border-start-0 cursor-pointer" onclick="togglePassword()">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                    </span>
                </div>
                <div class="form-text small">Gunakan kombinasi huruf dan angka agar lebih aman.</div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" name="password_baru" class="btn btn-success btn-lg shadow-sm">
                    <i class="bi bi-check-lg me-2"></i> Simpan Password
                </button>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            var input = document.getElementById("inputPassword");
            var icon = document.getElementById("toggleIcon");
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                input.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }
    </script>
</body>
</html>