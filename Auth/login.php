<?php
session_start();
require_once __DIR__ . '/../Config/koneksi.php';
require_once __DIR__ . '/../Class/Database.php';

// Jika sudah login, lempar sesuai role
if (isset($_SESSION['admin_login'])) { header("Location: ../Admin/admin.php"); exit; }
if (isset($_SESSION['user_login'])) { header("Location: ../index.php"); exit; }

$error = "";

if (isset($_POST['login'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    // 1. CEK KE TABEL ADMIN 
    $perintahsql = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $perintahsql->bind_param("s", $email);
    $perintahsql->execute();
    $admin = $perintahsql->get_result()->fetch_assoc();

    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_login'] = true;
        header("Location: ../Admin/admin.php");
        exit;
    }

    // 2. JIKA BUKAN ADMIN, CEK TABEL USER
    $perintahsql2 = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $perintahsql2->bind_param("s", $email);
    $perintahsql2->execute();
    $user = $perintahsql2->get_result()->fetch_assoc();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_login'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nama_lengkap'];
        header("Location: ../index.php");
        exit;
    }

    // 3. JIKA TIDAK KETEMU DI KEDUANYA
    $error = "Email atau Password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoring UBP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #0d6efd, #0dcaf0); 
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .login-card { width: 100%; max-width: 400px; border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="card-body">
        <h3 class="text-center fw-bold mb-4 text-primary">Login</h3>
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['pesan'])): ?>
            <div class="alert alert-success py-2 small text-center"><?php echo htmlspecialchars($_GET['pesan']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            
            <div class="text-end mb-3">
                <a href="lupa_password.php" class="text-decoration-none small">Lupa Password?</a>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">Masuk</button>
        </form>

        <div class="text-center mt-4 border-top pt-3 small">
            Belum punya akun? <a href="register.php" class="text-decoration-none fw-bold">Daftar</a>
            <br>
            <a href="../index.php" class="text-muted text-decoration-none mt-2 d-inline-block">← Kembali ke Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>