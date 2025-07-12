<?php
    include "../koneksi/koneksi.php";
    $id=$_GET['id'];
    $namatable=$_GET['namatable'];
    // Perbaikan: 'kd_bar' menjadi 'id' dan pengalihan ke 'datasuhu_main.php'
    $result=mysqli_query($conn,"delete from $namatable where id='$id'");
    header('location:datasuhu_main.php');
?>