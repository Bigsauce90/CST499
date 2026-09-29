<?php

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

$host = '127.0.0.1';
$port = 3307;
$username = 'root';
$password = '';
$database = 'cst499';

try {
    $conn = new mysqli(
        $host,
        $username,
        $password,
        $database,
        $port
    );

    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $error) {
    die(
        'Connection failed. Check db.php and XAMPP.'
    );
}
