<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Grafik Suhu Realtime">
    <meta name="author" content="Puderta">
    <link rel="icon" href="../favicon.ico">
    <title>Grafik Suhu - Sistem Monitoring Suhu</title>

    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="../js/chart.js"></script>
</head>

<body>
    <?php
    include '../navbar.php';

    ?>

    <div style="padding-top: 70px;"></div>
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-primary">Grafik Suhu Rata-rata per Jam</h2>
                <p class="card-text">Visualisasi Suhu Lingkungan UMA selama 24 Jam Terakhir</p>

                <div class="chart-container mt-4" style="position: relative; height:40vh; max-width: 800px; margin: auto;">
                    <canvas id="myTemperatureChart"></canvas>
                    <p id="noDataMessage" class="text-center text-muted mt-3" style="display: none;">Tidak ada data grafik tersedia untuk 24 jam terakhir.</p>
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
                    // Cek apakah respons OK dan Content-Type adalah JSON
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status + ' ' + response.statusText);
                    }
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        return response.json();
                    } else {
                        // Jika bukan JSON, mungkin ada error PHP tersembunyi
                        console.error("Received non-JSON response from get_chart_data.php. Check for PHP errors.");
                        return response.text().then(text => {
                            throw new Error("Server response was not JSON: " + text);
                        });
                    }
                })
                .then(data => {
                    console.log("Data fetched for chart:", data); // Log data yang diterima

                    const noDataMessage = document.getElementById('noDataMessage');
                    const chartCanvas = document.getElementById('myTemperatureChart');

                    // Jika tidak ada data atau data kosong
                    if (!Array.isArray(data) || data.length === 0) {
                        noDataMessage.style.display = 'block'; // Tampilkan pesan tidak ada data
                        chartCanvas.style.display = 'none'; // Sembunyikan canvas grafik
                        console.warn("No data or invalid data format received for chart. Displaying no data message.");
                        return; // Hentikan proses Chart.js
                    } else {
                        noDataMessage.style.display = 'none'; // Sembunyikan pesan jika ada data
                        chartCanvas.style.display = 'block'; // Pastikan canvas terlihat
                    }

                    const labels = data.map(row => row.tanggal); // Menggunakan 'tanggal' dari JSON
                    const temperatures = data.map(row => parseFloat(row.avg_temperatur)); // Menggunakan 'avg_temperatur'

                    console.log("Chart labels:", labels);
                    console.log("Chart temperatures:", temperatures);

                    const ctx = chartCanvas.getContext('2d');

                    new Chart(ctx, {
                        type: 'line', // Line chart lebih cocok untuk data time-series
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Suhu Rata-rata (°C)',
                                data: temperatures,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)', // Area di bawah garis
                                borderColor: 'rgba(75, 192, 192, 1)', // Garis grafik
                                borderWidth: 2,
                                fill: true, // Isi area di bawah garis
                                tension: 0.4 // Membuat garis sedikit melengkung
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // Penting untuk kontrol tinggi/lebar di container
                            scales: {
                                y: {
                                    beginAtZero: false, // Biarkan Chart.js menentukan skala y yang optimal
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
                                        text: 'Waktu (Jam)',
                                        font: {
                                            size: 14
                                        }
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Data Suhu Rata-rata per Jam (24 Jam Terakhir)',
                                    font: {
                                        size: 18,
                                        weight: 'bold'
                                    }
                                },
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 12
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching or parsing data for chart:', error);
                    document.getElementById('noDataMessage').style.display = 'block'; // Tampilkan pesan error
                    document.getElementById('myTemperatureChart').style.display = 'none'; // Sembunyikan canvas
                    document.getElementById('noDataMessage').innerText = 'Gagal memuat grafik: ' + error.message; // Tampilkan detail error
                });
        });
    </script>
</body>

</html>