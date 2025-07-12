<?php
// SUHU/export_data.php
require_once 'koneksi/koneksi.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="data_suhu_training.csv"');

$output = fopen('php://output', 'w');

// Tulis header CSV
fputcsv($output, ['id_perangkat', 'nilai_temperatur', 'tanggal', 'label_kondisi']); // Tambahkan kolom 'label_kondisi'

$sql = "SELECT id_perangkat, nilai_temperatur, tanggal FROM tbl_temperatur ORDER BY tanggal ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $temp_value = floatval($row['nilai_temperatur']);
        $label = 'Tidak Diketahui';
        if ($temp_value >= 20 && $temp_value <= 26) {
            $label = 'Sangat Nyaman';
        } elseif ($temp_value > 26) {
            $label = 'Kurang Nyaman';
        } else {
            $label = 'Tidak Nyaman';
        }
        fputcsv($output, [$row['id_perangkat'], $temp_value, $row['tanggal'], $label]);
    }
}

fclose($output);
$conn->close();
