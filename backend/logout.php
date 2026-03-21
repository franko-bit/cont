<?php
// backend/logout.php

require_once 'auth.php';

$auth = new Auth($pdo);
$result = $auth->logout();

// Redirect to home page
header("Location: " . SITE_URL . "/index.php");
exit;
?>