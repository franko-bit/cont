<?php
$host = "localhost";
$dbname = "playmates";
$username = "root";
$password = ""; // Change this to your MySQL password

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>