<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Data Suhu Lingkungan">
    <meta name="author" content="Puderta">
    <link rel="icon" href="../favicon.ico">
    <title>Data Suhu - Sistem Monitoring Suhu</title>

    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php
    include '../navbar.php';
    require_once '../koneksi/koneksi.php'; // Sertakan koneksi database
    ?>

    <div style="padding-top: 70px;"></div>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-primary">Informasi Data Suhu</h2>
                <p class="card-text">Sistem Monitoring Suhu Lingkungan - Universitas Medan Area</p>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-primary text-white">
                            <tr>
                                <th>NO</th>
                                <th>ID</th>
                                <th>ID PERANGKAT</th>
                                <th>NILAI TEMPERATUR (°C)</th>
                                <th>WAKTU</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mengambil semua data dari database dan mengurutkan berdasarkan ID secara menaik (ASC)
                            $sql = "SELECT id, id_perangkat, nilai_temperatur, tanggal FROM tbl_temperatur ORDER BY id ASC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $no = 1; // Penomoran urut dari 1
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . $no++ . '</td>'; // Nomor urut baris
                                    echo '<td>' . $row['id'] . '</td>'; // ID dari database
                                    echo '<td>' . $row['id_perangkat'] . '</td>';
                                    echo '<td>' . htmlspecialchars($row['nilai_temperatur']) . '</td>';
                                    echo '<td>' . $row['tanggal'] . '</td>';
                                    echo '<td><a href="proses_delete.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Anda yakin ingin menghapus data ini?\')">Delete</a></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center">Tidak ada data suhu tersedia.</td></tr>';
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
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
</body>

</html>