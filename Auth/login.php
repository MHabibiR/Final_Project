<?php
session_start();
include '../Config/koneksi.php';

if (isset($_SESSION['admin_login'])) {
    header("Location: ../Admin/admin.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $admin = $_POST['username'];
    $sandi = $_POST['password'];

    // Cari username di database
    $perintah_sql = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $perintah_sql->bind_param("s", $admin);
    $perintah_sql->execute();
    $result = $perintah_sql->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($sandi, $row['password'])) {
            $_SESSION['admin_login'] = true;
            header("Location: ../Admin/admin.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login Admin - Monitoring UBP</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #2c3e50, #4ca1af); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .form-floating input:focus { box-shadow: none; border-color: #2c3e50; }
        .btn-primary { background-color: #2c3e50; border-color: #2c3e50; }
        .btn-primary:hover { background-color: #1a252f; }
    </style>
</head>
<body>
    <div class="card login-card p-4 m-3">
        <div class="card-body">
            <h3 class="text-center fw-bold mb-4" style="color: #2c3e50;">Login Admin</h3>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                    <label for="username">Username</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">Masuk Dashboard</button>
            </form>

            <div class="text-center mt-4 small">
                <div class="mt-3 border-top pt-3">
                    <a href="../index.php" class="text-secondary text-decoration-none">← Kembali ke Halaman Utama</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>