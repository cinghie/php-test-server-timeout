<?php

// Path to the log file where errors will be recorded
$logFile = __DIR__ . '/self_check.log';

// Timeout duration in seconds (high value to catch slow responses)
$timeout = 6000;

// Start time and timestamp for logging purposes
$startTime = microtime(true);
$startTimestamp = date('Y-m-d H:i:s');

// Build the full URL of this script (self-call)
$targetUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Initialize cURL session
$ch = curl_init($targetUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER    => true,    // Return the response as a string
    CURLOPT_TIMEOUT           => $timeout, // Max execution time
    CURLOPT_CONNECTTIMEOUT    => $timeout, // Max time to establish connection
    CURLOPT_FOLLOWLOCATION    => true,     // Follow redirects if any
    CURLOPT_HEADER            => true,     // Include headers in output (to extract body later)
    CURLOPT_NOBODY            => false,    // We want the full response body
]);

// Execute the HTTP request
$response = curl_exec($ch);

// Capture HTTP response details and any cURL error
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$error      = curl_error($ch);
$errno      = curl_errno($ch);

// Close cURL session
curl_close($ch);

// End time and timestamp
$endTime = microtime(true);
$endTimestamp = date('Y-m-d H:i:s');
$duration = round($endTime - $startTime, 3);

// Default: do not log unless an error is detected
$shouldLog = false;

// Begin building the log entry
$logEntry = "=== SERVER SELF-CHECK ERROR ===\n";
$logEntry .= "Inizio: $startTimestamp\n";
$logEntry .= "Fine:   $endTimestamp\n";
$logEntry .= "Durata: {$duration}s\n";
$logEntry .= "URL:    $targetUrl\n";

// Check for cURL-level errors
if ($errno) {
    $shouldLog = true;
    $logEntry .= "cURL ERROR [$errno]: $error\n";

    // Specifically identify timeouts
    if (str_contains(strtolower($error), 'timed out')) {
        $logEntry .= "→ Tipo: Timeout di connessione o risposta lenta\n";
    }

// Check for HTTP 500+ server errors
} elseif ($httpCode >= 500) {
    $shouldLog = true;
    $logEntry .= "HTTP ERROR: $httpCode\n";

    // Extract and summarize the body (often contains error detail)
    $body = substr($response, $headerSize);
    $bodySummary = strip_tags(substr($body, 0, 500));
    $logEntry .= "→ Dettaglio contenuto (inizio corpo risposta):\n$bodySummary\n";

// Check for HTTP 4xx client errors
} elseif ($httpCode >= 400) {
    $shouldLog = true;
    $logEntry .= "HTTP ERROR: $httpCode\n";

// Catch-all if response was false but no error code was set
} elseif ($response === false) {
    $shouldLog = true;
    $logEntry .= "Errore sconosciuto, nessuna risposta ricevuta.\n";
}

// Close log entry block
$logEntry .= "===============================\n\n";

// Write the log only if an error was detected
if ($shouldLog) {
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
