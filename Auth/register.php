<?php
require_once __DIR__ . '/../Config/koneksi.php';
require_once __DIR__ . '/../Class/Database.php';

$pesan = "";
if (isset($_POST['register'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $nama  = $_POST['nama'];
    $email = $_POST['email'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $isSub = 1;
    
    // Cek email kembar
    $cek = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($cek->num_rows > 0) {
        $pesan = "Email sudah terdaftar!";
    } else {
        $perintah_sql = $conn->prepare("INSERT INTO users (nama_lengkap, email, password, is_subscribed) VALUES (?, ?, ?, ?)");
        $perintah_sql->bind_param("sssi", $nama, $email, $pass, $isSub);
        
        if ($perintah_sql->execute()) {
            header("Location: login.php?pesan=Registrasi Berhasil! Silakan Login.");
            exit;
        } else {
            $pesan = "Gagal daftar: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Mahasiswa - Monitoring UBP</title>
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
            <h3 class="text-center fw-bold mb-4 text-primary">Registrasi</h3>
            
            <?php if(!empty($pesan)): ?>
                <div class="alert alert-danger py-2 small text-center"><?php echo $pesan; ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['sukses'])): ?>
                <div class="alert alert-success py-2 small text-center">Registrasi Berhasil! Silakan Login.</div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" name="nama" id="nama" placeholder="Nama Lengkap" required>
                    <label for="nama">Nama Lengkap</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" required>
                    <label for="email">Email</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                
                <button type="submit" name="register" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">Daftar</button>
            </form>

            <div class="text-center mt-4 small">
                <p class="text-muted mb-1">Sudah punya akun?</p>
                <a href="login.php" class="text-decoration-none fw-bold">Masuk Sekarang</a>
                <div class="mt-3 border-top pt-3">
                    <a href="../index.php" class="text-secondary text-decoration-none">← Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>