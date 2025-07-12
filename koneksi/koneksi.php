<?php 
    require_once __DIR__ . '/db_config.php'; 
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE); 
    if (mysqli_connect_errno()) { 
    trigger_error('Koneksi ke database gagal: '  . 
    mysqli_connect_error(), E_USER_ERROR);  
    } 
?> 