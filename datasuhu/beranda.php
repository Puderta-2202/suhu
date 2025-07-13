<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Monitoring Suhu Realtime">
    <meta name="author" content="Puderta">
    <link rel="icon" href="../favicon.ico">
    <title>Dashboard - Sistem Monitoring Suhu</title>

    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="../js/chart.js"></script>

    <style>
        .large-temp-text {
            font-size: 3.5rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .large-time-text {
            font-size: 1.5rem;
            color: #6c757d;
        }

        .equal-height-card {
            height: 100%;
        }

        /* Kelas warna baru untuk kondisi prediksi */
        .bg-sangat-nyaman {
            background-color: #d4edda;
            /* Light green */
            color: #155724;
            /* Dark green text */
        }

        .bg-kurang-nyaman {
            background-color: #fff3cd;
            /* Light yellow */
            color: #856404;
            /* Dark yellow text */
        }

        .bg-tidak-nyaman {
            background-color: #f8d7da;
            /* Light red */
            color: #721c24;
            /* Dark red text */
        }
    </style>
</head>

<body>
    <?php
    include '../navbar.php';
    require_once '../koneksi/koneksi.php';

    // --- LOGIKA PHP UNTUK MENGAMBIL DATA SUHU TERBARU ---
    $suhuTerakhir = "N/A";
    $waktuTerakhir = "N/A";
    $kondisiKenyamananPrediksi = "Memproses...";
    $bgPrediksiClass = "bg-light";

    $sql_terakhir = "SELECT nilai_temperatur, tanggal FROM tbl_temperatur ORDER BY tanggal DESC LIMIT 1";
    $result_terakhir = $conn->query($sql_terakhir);

    if ($result_terakhir && $result_terakhir->num_rows > 0) {
        $row_terakhir = $result_terakhir->fetch_assoc();
        $suhuTerakhir = htmlspecialchars($row_terakhir['nilai_temperatur']);
        $waktuTerakhir = date('H:i A', strtotime($row_terakhir['tanggal']));

        // --- MEMANGGIL SKRIP PYTHON UNTUK PREDIKSI ---
        $python_script_path = escapeshellarg('D:\laragon\www\suhu\python\predict_temp_condition.py');
        $suhu_untuk_python = escapeshellarg($suhuTerakhir);

        $command = "python " . $python_script_path . " " . $suhu_untuk_python;

        $output_json = shell_exec($command);

        // Dekode output JSON dari Python
        $prediction_result = json_decode($output_json, true);

        if ($prediction_result && isset($prediction_result['status']) && $prediction_result['status'] == 'success') {
            $kondisiKenyamananPrediksi = $prediction_result['predicted_condition'];
            switch ($kondisiKenyamananPrediksi) {
                case 'Sangat Nyaman':
                    $bgPrediksiClass = "bg-sangat-nyaman";
                    break;
                case 'Kurang Nyaman':
                    $bgPrediksiClass = "bg-kurang-nyaman";
                    break;
                case 'Tidak Nyaman':
                    $bgPrediksiClass = "bg-tidak-nyaman";
                    break;
                default:
                    $bgPrediksiClass = "bg-light"; // Fallback
            }
        } else {
            // Tangani error jika skrip Python tidak mengembalikan JSON sukses
            $kondisiKenyamananPrediksi = "Error Prediksi: " . ($prediction_result['error'] ?? "Output Python tidak valid atau kosong.");
            $bgPrediksiClass = "bg-danger text-white";
            error_log("Python Prediction Error: " . $output_json);
        }
    } else {
        // Jika tidak ada data suhu terbaru dari database
        $kondisiKenyamananPrediksi = "Tidak ada data suhu terbaru untuk prediksi.";
        $bgPrediksiClass = "bg-info text-white"; // Latar belakang biru untuk info
    }

    // --- LOGIKA PHP UNTUK STATISTIK KESELURUHAN DARI DATABASE ---
    $suhuRata2Global = "N/A"; // Mengubah nama variabel dari 'HariIni' menjadi 'Global'
    $suhuMinimalGlobal = "N/A";
    $suhuMaksimalGlobal = "N/A";

    // Perbaikan query SQL untuk statistik: Hapus klausa WHERE DATE(tanggal) = CURDATE()
    $sql_statistik_global = "SELECT 
                                AVG(CAST(nilai_temperatur AS DECIMAL(5,2))) AS avg_total,
                                MIN(CAST(nilai_temperatur AS DECIMAL(5,2))) AS min_total,
                                MAX(CAST(nilai_temperatur AS DECIMAL(5,2))) AS max_total
                             FROM tbl_temperatur";
    $result_statistik_global = $conn->query($sql_statistik_global);

    if ($result_statistik_global) { // Pastikan query tidak error
        $stats_global = $result_statistik_global->fetch_assoc();
        // Pastikan nilai agregat tidak NULL (jika tidak ada data sama sekali, AVG/MIN/MAX akan NULL)
        if ($stats_global['avg_total'] !== null) {
            $suhuRata2Global = number_format($stats_global['avg_total'], 2) . "°C";
            $suhuMinimalGlobal = number_format($stats_global['min_total'], 2) . "°C";
            $suhuMaksimalGlobal = number_format($stats_global['max_total'], 2) . "°C";
        } else {
            // Jika query berhasil tapi tidak ada data sama sekali
            $suhuRata2Global = "Tidak Ada Data";
            $suhuMinimalGlobal = "Tidak Ada Data";
            $suhuMaksimalGlobal = "Tidak Ada Data";
        }
    } else {
        // Jika query statistik gagal
        error_log("SQL Error for global statistics: " . $conn->error);
        $suhuRata2Global = "Error DB";
        $suhuMinimalGlobal = "Error DB";
        $suhuMaksimalGlobal = "Error DB";
    }

    $conn->close();
    ?>

    <div style="padding-top: 70px;"></div>

    <div class="container mt-4">
        <h2 class="mb-4 text-primary">Dashboard Pemantauan Suhu</h2>
        <p class="text-muted">Menampilkan Data untuk Awal Prediktif Terukur</p>

        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="card shadow-sm equal-height-card">
                    <div class="card-body">
                        <h5 class="card-title text-muted mb-2">Suhu Terakhir</h5>
                        <h6 class="card-subtitle text-muted mb-3"><?php echo $waktuTerakhir; ?></h6>
                        <div class="text-center">
                            <span class="large-temp-text"><?php echo $suhuTerakhir; ?>°C</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm equal-height-card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted mb-2">Analisis Kenyamanan Suhu</h5>
                        <div class="mt-4 p-3 rounded <?php echo $bgPrediksiClass; ?>">
                            <h3 class="mb-0 fw-bold"><?php echo $kondisiKenyamananPrediksi; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm equal-height-card">
                    <div class="card-body">
                        <h5 class="card-title text-muted mb-3">Statistik Keseluruhan Data</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Rata-rata Total
                                <span class="fw-bold text-primary"><?php echo $suhuRata2Global; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Suhu Minimal Total
                                <span class="fw-bold text-success"><?php echo $suhuMinimalGlobal; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Suhu Maksimal Total
                                <span class="fw-bold text-danger"><?php echo $suhuMaksimalGlobal; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title text-primary">Grafik Suhu Harian</h5>
                <p class="card-text text-muted">Perubahan suhu lingkungan secara keseluruhan.</p>
                <div class="chart-container" style="position: relative; height:350px; width:100%;">
                    <canvas id="dashboardSuhuChart"></canvas>
                    <p id="noDataChartMessage" class="text-center text-muted mt-3" style="display: none;">Tidak ada data grafik tersedia.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© <?php echo date("Y"); ?> Sistem Monitoring Suhu. All rights reserved.</span>
        </div>
    </footer>

    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('get_chart.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status + ' ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    const noDataChartMessage = document.getElementById('noDataChartMessage');
                    const chartCanvas = document.getElementById('dashboardSuhuChart');

                    if (!Array.isArray(data) || data.length === 0) {
                        noDataChartMessage.style.display = 'block';
                        chartCanvas.style.display = 'none';
                        return;
                    } else {
                        noDataChartMessage.style.display = 'none';
                        chartCanvas.style.display = 'block';
                    }

                    const labels = data.map(row => row.tanggal.substring(11, 16));
                    const temperatures = data.map(row => parseFloat(row.avg_temperatur));

                    const ctx = chartCanvas.getContext('2d');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Suhu (°C)',
                                data: temperatures,
                                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                                borderColor: 'rgba(13, 110, 253, 1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    title: {
                                        display: true,
                                        text: 'Suhu (°C)',
                                        font: {
                                            size: 14
                                        }
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Waktu'
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Grafik Semua Data Suhu Rata-rata per Jam',
                                    font: {
                                        size: 18,
                                        weight: 'bold'
                                    }
                                },
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching or parsing data for dashboard chart:', error);
                    document.getElementById('noDataChartMessage').style.display = 'block';
                    document.getElementById('noDataChartMessage').innerText = 'Gagal memuat grafik: ' + error.message;
                    document.getElementById('dashboardSuhuChart').style.display = 'none';
                });
        });
    </script>
</body>

</html>