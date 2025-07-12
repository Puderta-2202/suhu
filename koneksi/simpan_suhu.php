<?php
header('Content-Type: application/json'); // Mengatur header respons sebagai JSON

// Sertakan file koneksi database Anda
require_once __DIR__ . '/koneksi.php';

// Pastikan variabel $conn sudah tersedia dan koneksi berhasil dibuat oleh koneksi.php
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi ke database gagal: " . (isset($conn) ? $conn->connect_error : "Variabel koneksi tidak diinisialisasi.")]);
    exit(); // Hentikan eksekusi jika koneksi gagal
}

// Hanya menerima request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data JSON dari body request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true); // true untuk mendapatkan array asosiatif

    // Memeriksa apakah data yang dibutuhkan tersedia
    if (isset($data['id_perangkat']) && isset($data['suhu'])) {
        $id_perangkat = $data['id_perangkat'];
        $nilai_temperatur = $data['suhu'];

        // Validasi input
        if (!is_numeric($nilai_temperatur)) {
            echo json_encode(["status" => "error", "message" => "Nilai temperatur tidak valid."]);
            $conn->close();
            exit();
        }

        // Dapatkan waktu saat ini dari server PHP
        $tanggal_sekarang = date('Y-m-d H:i:s');

        // Menyiapkan statement SQL untuk mencegah SQL Injection
        $stmt = $conn->prepare("INSERT INTO tbl_temperatur (id_perangkat, nilai_temperatur, tanggal) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $id_perangkat, $nilai_temperatur, $tanggal_sekarang); // "sss" untuk tiga string

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Data suhu berhasil disimpan."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error saat menyimpan data: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Parameter 'id_perangkat' atau 'suhu' tidak ditemukan dalam request POST."]);
    }
} else {
    // Respons jika metode request bukan POST (misalnya GET dari browser)
    echo json_encode(["status" => "error", "message" => "Metode request tidak diizinkan. Gunakan POST."]);
}

$conn->close(); // Tutup koneksi database
