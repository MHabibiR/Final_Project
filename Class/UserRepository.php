<?php
class UserRepository {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserById($id) {
        $perintahsql = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $perintahsql->bind_param("i", $id);
        $perintahsql->execute();
        return $perintahsql->get_result()->fetch_assoc();
    }

    public function updateUser($id, $nama, $email, $is_subscribed, $password = null) {
        $cek = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $cek->bind_param("si", $email, $id);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) return false;

        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $perintahsql = $this->conn->prepare("UPDATE users SET nama_lengkap=?, email=?, is_subscribed=?, password=? WHERE id=?");
            $perintahsql->bind_param("ssisi", $nama, $email, $is_subscribed, $hash, $id);
        } else {
            $perintahsql = $this->conn->prepare("UPDATE users SET nama_lengkap=?, email=?, is_subscribed=? WHERE id=?");
            $perintahsql->bind_param("ssii", $nama, $email, $is_subscribed, $id);
        }
        return $perintahsql->execute();
    }

    public function deleteUser($id) {
        $perintahsql = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $perintahsql->bind_param("i", $id);
        return $perintahsql->execute();
    }
}
?>