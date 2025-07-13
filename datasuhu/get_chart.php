<?php
// SUHU/datasuhu/get_chart_data.php

require_once '../koneksi/koneksi.php';

header('Content-Type: application/json');

// Mengambil SEMUA data suhu rata-rata per jam dari tabel 'tbl_temperatur'
// Tidak ada lagi batasan 24 jam terakhir
$sql = "SELECT 
            DATE_FORMAT(tanggal, '%Y-%m-%d %H:00') AS timestamp_hour,  
            AVG(CAST(nilai_temperatur AS DECIMAL(5,2))) AS avg_temperatur 
        FROM 
            tbl_temperatur 
        GROUP BY 
            timestamp_hour  
        ORDER BY 
            timestamp_hour ASC"; 

$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['avg_temperatur'] = (float) $row['avg_temperatur'];
        $data[] = ['tanggal' => $row['timestamp_hour'], 'avg_temperatur' => $row['avg_temperatur']];
    }
}

if (empty($data)) {
    // Opsional: Anda bisa mengirimkan data dummy atau pesan jika tidak ada data
}

echo json_encode($data);

$conn->close();
?>