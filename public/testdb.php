<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=cerita_app;charset=utf8mb4', 'root', '');
    echo "Koneksi berhasil!<br>";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        echo "Hash di DB: " . $row['password_hash'] . "<br>";
        if (password_verify('admin123', $row['password_hash'])) {
            echo "Password admin123 BENAR!";
        } else {
            echo "Password admin123 SALAH!";
        }
    } else {
        echo "User admin tidak ditemukan di database.";
    }
} catch (PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
} 