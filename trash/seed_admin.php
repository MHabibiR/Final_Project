<?php
include '../Config/koneksi.php';

$username = "admin";
$passwordAsli = "admin123";

$passwordHashed = password_hash($passwordAsli, PASSWORD_DEFAULT);

$query = "INSERT INTO admin (username, password) VALUES ('$username', '$passwordHashed')";

if (mysqli_query($conn, $query)) {
    echo "<h1>Sukses!</h1>";
    echo "User Admin berhasil dibuat.<br>";
    echo "Username: <b>$username</b><br>";
    echo "Password: <b>$passwordAsli</b><br>";
    echo "Hash Database: $passwordHashed (Ini yang disimpan)";
} else {
    echo "Gagal: " . mysqli_error($conn);
}
?>