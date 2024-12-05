<?php
$connect = mysqli_connect("localhost", "root", "", "dreamforce");

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
