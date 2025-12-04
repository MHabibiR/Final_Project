<?php
class KelolaAdmin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // READ: Ambil semua admin
    public function semua_admin() {
        $result = $this->conn->query("SELECT * FROM admin");
        return $result;
    }

    // READ (Single): Ambil 1 admin berdasarkan ID (untuk diedit)
    public function id_admin($id) {
        $perintah_sql = $this->conn->prepare("SELECT * FROM admin WHERE id = ?");
        $perintah_sql->bind_param("i", $id);
        $perintah_sql->execute();
        return $perintah_sql->get_result()->fetch_assoc();
    }

    // CREATE: Tambah Admin
    public function tambah_admin($username, $password) {
        // Cek duplicate
        $cek = $this->conn->query("SELECT id FROM admin WHERE username='$username'");
        if ($cek->num_rows > 0) return false;

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $perintah_sql = $this->conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $perintah_sql->bind_param("ss", $username, $hashed);
        return $perintah_sql->execute();
    }

    // UPDATE: Edit Password / Username
    public function update_admin($id, $username, $password = null) {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $perintah_sql = $this->conn->prepare("UPDATE admin SET username=?, password=? WHERE id=?");
            $perintah_sql->bind_param("ssi", $username, $hashed, $id);
        } else {
            $perintah_sql = $this->conn->prepare("UPDATE admin SET username=? WHERE id=?");
            $perintah_sql->bind_param("si", $username, $id);
        }
        return $perintah_sql->execute();
    }

    // DELETE: Hapus Admin
    public function hapus_admin($id) {
        $perintah_sql = $this->conn->prepare("DELETE FROM admin WHERE id = ?");
        $perintah_sql->bind_param("i", $id);
        return $perintah_sql->execute();
    }
}
?>