<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db_mysqli(): mysqli
{
    static $connection = null;
    if ($connection instanceof mysqli) {
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
    $connection->set_charset('utf8mb4');
    return $connection;
}
