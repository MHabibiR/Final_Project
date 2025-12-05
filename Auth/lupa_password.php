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

    // Cek di Admin 
    $perintahsql = $conn->prepare("UPDATE admin SET reset_token=?, reset_expires=? WHERE email=?");
    $perintahsql->bind_param("sss", $token, $expiry, $email);
    $perintahsql->execute();

    // Cek di User
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
    <title>Lupa Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height:100vh;">
    <div class="card p-4 shadow-sm" style="max-width:400px; width:100%;">
        <h4 class="text-center mb-3">Lupa Password?</h4>
        
        <?php if($pesan): ?><div class="alert alert-success py-2 small"><?php echo $pesan; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-danger py-2 small"><?php echo $error; ?></div><?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Masukkan Email Terdaftar</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" name="reset" class="btn btn-primary w-100">Kirim Link Reset</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>