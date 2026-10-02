<?php
/**
 * TasteBook MySQL connection.
 * Set TASTEBOOK_DB_* environment variables to override local Wamp/XAMPP defaults.
 */
$db_host = getenv('TASTEBOOK_DB_HOST') ?: 'localhost';
$db_user = getenv('TASTEBOOK_DB_USER') ?: 'root';
$db_pass = getenv('TASTEBOOK_DB_PASSWORD') ?: '';
$db_name = getenv('TASTEBOOK_DB_NAME') ?: 'tastebook';
$configured_port = getenv('TASTEBOOK_DB_PORT');
$db_ports = ($configured_port !== false && $configured_port !== '')
    ? [(int)$configured_port]
    : [3306, 3308, 3307];

mysqli_report(MYSQLI_REPORT_OFF);

$conn = null;
$connection_error = '';
foreach ($db_ports as $db_port) {
    $candidate = @new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    if (!$candidate->connect_error) {
        $conn = $candidate;
        break;
    }
    $connection_error = $candidate->connect_error;
}

if (!$conn) {
    error_log('TasteBook database connection failed: ' . $connection_error);
    http_response_code(500);
    exit(
        '<main style="max-width:680px;margin:3rem auto;padding:2rem;font-family:system-ui,sans-serif">' .
        '<h1>TasteBook is temporarily unavailable</h1>' .
        '<p>We could not connect to the database. Start MySQL and confirm the database settings in ' .
        '<code>includes/db.php</code> or your environment variables, then reload the page.</p>' .
        '<p>For setup steps, see <code>README.md</code>.</p></main>'
    );
}

$conn->set_charset('utf8mb4');
