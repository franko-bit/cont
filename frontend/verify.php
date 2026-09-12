<?php
require_once '../backend/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit;
}

$userId = $_SESSION['user_id'];
$attempt_id = (int)($_GET['attempt_id'] ?? 0);
$apiKey = "5llHSFsLPLkehuL671j-slRF6Hm5dqyvJkXapkve9bQ";

// Create verification session with Didit
$url = "https://verification.didit.me/v3/session/";

$callback_url = SITE_URL . "/frontend/check_verification.php";
if ($attempt_id > 0) {
    $callback_url .= "?attempt_id=" . $attempt_id;
}

$data = [
    "vendor_data" => (string)$userId,
    "workflow_id" => "e81518f7-7ca1-44a8-bfdb-7e98156c7467",
    "callback" => $callback_url
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "x-api-key: $apiKey"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 && $httpCode !== 201) {
    die("Error creating verification session. HTTP Code: " . $httpCode . ". Response: " . $response);
}

$result = json_decode($response, true);

if (!isset($result["session_id"]) || !isset($result["url"])) {
    die("Invalid response from Didit API");
}

// Save session ID to database
$stmt = $pdo->prepare("UPDATE users SET didit_session_id = ? WHERE id = ?");
$stmt->execute([$result["session_id"], $userId]);

if ($attempt_id > 0) {
    $stmt = $pdo->prepare("UPDATE exam_attempts SET didit_session_id = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$result["session_id"], $attempt_id, $userId]);
}

// Redirect user to Didit verification page
header("Location: " . $result["url"]);
exit;
?>