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
                <p class="card-text">Visualisasi Semua Data Suhu Lingkungan UMA per Jam.</p>
                <div class="chart-container mt-4" style="position: relative; height:40vh; max-width: 800px; margin: auto;">
                    <canvas id="myTemperatureChart"></canvas>
                    <p id="noDataMessage" class="text-center text-muted mt-3" style="display: none;">Tidak ada data grafik tersedia.</p>
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
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        return response.json();
                    } else {
                        console.error("Received non-JSON response from get_chart.php. Check for PHP errors.");
                        return response.text().then(text => {
                            throw new Error("Server response was not JSON: " + text);
                        });
                    }
                })
                .then(data => {
                    console.log("Data fetched for chart:", data);

                    const noDataMessage = document.getElementById('noDataMessage');
                    const chartCanvas = document.getElementById('myTemperatureChart');

                    if (!Array.isArray(data) || data.length === 0) {
                        noDataMessage.style.display = 'block';
                        chartCanvas.style.display = 'none';
                        console.warn("No data or invalid data format received for chart. Displaying no data message.");
                        return;
                    } else {
                        noDataMessage.style.display = 'none';
                        chartCanvas.style.display = 'block';
                    }

                    const labels = data.map(row => row.tanggal); // 'tanggal' akan berisi 'YYYY-MM-DD HH:00'
                    const temperatures = data.map(row => parseFloat(row.avg_temperatur));

                    console.log("Chart labels:", labels);
                    console.log("Chart temperatures:", temperatures);

                    const ctx = chartCanvas.getContext('2d');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Suhu Rata-rata (°C)',
                                data: temperatures,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
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
                                    text: 'Grafik Semua Data Suhu Rata-rata per Jam', // TEKS BERUBAH DI SINI
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
                    document.getElementById('noDataMessage').style.display = 'block';
                    document.getElementById('myTemperatureChart').style.display = 'none';
                    document.getElementById('noDataMessage').innerText = 'Gagal memuat grafik: ' + error.message;
                });
        });
    </script>
</body>

</html>