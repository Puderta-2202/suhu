<?php
// SUHU/datasuhu/get_chart_data.php

require_once '../koneksi/koneksi.php';

header('Content-Type: application/json');

// Ambil data suhu rata-rata per jam dari 24 jam terakhir dari tabel 'tbl_temperatur'
// Menggunakan CAST untuk mengkonversi nilai_temperatur dari VARCHAR ke DECIMAL agar bisa dihitung rata-ratanya
$sql = "SELECT 
            DATE_FORMAT(tanggal, '%Y-%m-%d %H:00') AS timestamp_hour,  -- Beri alias yang berbeda untuk hasil DATE_FORMAT
            AVG(CAST(nilai_temperatur AS DECIMAL(5,2))) AS avg_temperatur 
        FROM 
            tbl_temperatur 
        WHERE 
            tanggal >= NOW() - INTERVAL 24 HOUR 
        GROUP BY 
            timestamp_hour  -- GROUP BY menggunakan alias kolom yang sudah diformat
        ORDER BY 
            timestamp_hour ASC";

$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Pastikan 'avg_temperatur' dikirim sebagai nilai float agar bisa digunakan oleh Chart.js
        $row['avg_temperatur'] = (float) $row['avg_temperatur'];
        // Ganti nama kolom 'tanggal' di output JSON menjadi 'timestamp_hour'
        $data[] = ['tanggal' => $row['timestamp_hour'], 'avg_temperatur' => $row['avg_temperatur']];
    }
}

echo json_encode($data);

$conn->close();
