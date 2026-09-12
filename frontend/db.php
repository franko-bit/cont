<?php
$host = "localhost";
$dbname = "playmates";
$username = "root";
$password = ""; // Change this to your MySQL password

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>