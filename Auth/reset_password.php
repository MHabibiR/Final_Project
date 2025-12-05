<?php
require_once __DIR__ . '/../Class/Database.php';

$token = $_GET['token'] ?? '';
$error = "";

if (isset($_POST['password_baru'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $now = date('Y-m-d H:i:s');

    // Cek Token di Admin
    $perintahsql = $conn->prepare("UPDATE admin SET password=?, reset_token=NULL, reset_expires=NULL WHERE reset_token=? AND reset_expires > ?");
    $perintahsql->bind_param("sss", $pass, $token, $now);
    $perintahsql->execute();

    if ($perintahsql->affected_rows == 0) {
        // Cek Token di User
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
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height:100vh;">
    <div class="card p-4 shadow-sm" style="max-width:400px; width:100%;">
        <h4 class="text-center mb-3">Buat Password Baru</h4>
        
        <?php if($error): ?>
            <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
            </div>
            <button type="submit" name="password_baru" class="btn btn-success w-100">Simpan Password</button>
        </form>
    </div>
</body>
</html>