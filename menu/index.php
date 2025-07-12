<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Monitoring Suhu">
    <meta name="author" content="Puderta">
    <link rel="icon" href="../favicon.ico">
    <title>Sistem Monitoring Suhu</title>

    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light"> <?php
                        include "../navbar.php";
                        ?>

    <div style="padding-top: 70px;"></div>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-primary">Sistem Monitoring Suhu</h2>
                <p class="card-text text-muted">Selamat datang di Sistem Monitoring Suhu Lingkungan Di Universitas Medan Area.</p>
                <p class="card-text">Gunakan navigasi di atas untuk melihat data suhu terkini, grafik, atau mengakses dashboard analisis. Atau klik Mulai Sekarang dibawah ini untuk menuju halaman Dashboard.</p>
                <a href="../datasuhu/beranda.php" class="btn btn-primary mt-3">Mulai Sekarang</a>
            </div>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-light fixed-bottom">
        <div class="container text-center">
            <span class="text-muted">© <?php echo date("Y"); ?> Sistem Monitoring Suhu. All rights reserved.</span>
        </div>
    </footer>

    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>