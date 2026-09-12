<?php
// Enhanced logging for exam-api.php submissions
// Add this at the beginning of the submitLessonExam function

error_log("=== EXAM SUBMISSION LOG ===");
error_log("Time: " . date('Y-m-d H:i:s'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Session User: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("POST data: " . file_get_contents('php://input'));

// Log the response that will be sent
error_log("Response being sent: " . json_encode($responseData ?? []));
error_log("=========================\n");
?>
