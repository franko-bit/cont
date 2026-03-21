<?php
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success'=>true,'message'=>'test-add.php is reachable','session_id'=>session_id()]);