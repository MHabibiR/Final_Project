<?php
session_start();
require_once '../Class/Database.php';

if (isset($_POST['login'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $email = $_POST['email'];
    $pass  = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['nama_lengkap'];
            header("Location: ../index.php");
            exit;
        }
    }
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
        body { background: linear-gradient(135deg, #0d6efd, #0dcaf0); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .form-floating input:focus { box-shadow: none; border-color: #0d6efd; }
    </style>
</head>

<body>
    <div class="card login-card p-4 m-3">
        <div class="card-body">
            <h3 class="text-center fw-bold mb-4 text-primary">Login</h3>

            <?php if(isset($_GET['sukses'])): ?>
                <div class="alert alert-success py-2 small text-center">
                    ✅ Registrasi Berhasil! Silakan Login.
                </div>
            <?php endif; ?>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger py-2 small text-center">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" required>
                    <label for="email">Email</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">Masuk</button>
            </form>

            <div class="text-center mt-4 small">
                <p class="text-muted mb-1">Belum punya akun?</p>
                <a href="register.php" class="text-decoration-none fw-bold">Daftar Sekarang</a>
                <div class="mt-3 border-top pt-3">
                    <a href="../index.php" class="text-secondary text-decoration-none">← Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>