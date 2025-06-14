<?php

$logFile = __DIR__ . '/self_check.log';
$timeout = 6000;

$startTime = microtime(true);
$startTimestamp = date('Y-m-d H:i:s');
$targetUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$ch = curl_init($targetUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER    => true,
    CURLOPT_TIMEOUT           => $timeout,
    CURLOPT_CONNECTTIMEOUT    => $timeout,
    CURLOPT_FOLLOWLOCATION    => true,
    CURLOPT_HEADER            => true,   // includi intestazioni per debug
    CURLOPT_NOBODY            => false,  // vogliamo il corpo
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$error = curl_error($ch);
$errno = curl_errno($ch);
curl_close($ch);

$endTime = microtime(true);
$endTimestamp = date('Y-m-d H:i:s');
$duration = round($endTime - $startTime, 3);

$shouldLog = false;
$logEntry = "=== SERVER SELF-CHECK ERROR ===\n";
$logEntry .= "Inizio: $startTimestamp\n";
$logEntry .= "Fine:   $endTimestamp\n";
$logEntry .= "Durata: {$duration}s\n";
$logEntry .= "URL:    $targetUrl\n";

if ($errno) {
    $shouldLog = true;
    $logEntry .= "cURL ERROR [$errno]: $error\n";
    if (str_contains(strtolower($error), 'timed out')) {
        $logEntry .= "→ Tipo: Timeout di connessione o risposta lenta\n";
    }
} elseif ($httpCode >= 500) {
    $shouldLog = true;
    $logEntry .= "HTTP ERROR: $httpCode\n";
    $body = substr($response, $headerSize);
    $bodySummary = strip_tags(substr($body, 0, 500));
    $logEntry .= "→ Dettaglio contenuto (inizio corpo risposta):\n$bodySummary\n";
} elseif ($httpCode >= 400) {
    $shouldLog = true;
    $logEntry .= "HTTP ERROR: $httpCode\n";
} elseif ($response === false) {
    $shouldLog = true;
    $logEntry .= "Errore sconosciuto, nessuna risposta ricevuta.\n";
}

$logEntry .= "===============================\n\n";

if ($shouldLog) {
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
