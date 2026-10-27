<?php
declare(strict_types=1);

define('DB_HOST', getenv('STUDENTHUB_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', (int) (getenv('STUDENTHUB_DB_PORT') ?: 3306));
define('DB_NAME', getenv('STUDENTHUB_DB_NAME') ?: 'studenthub');
define('DB_USER', getenv('STUDENTHUB_DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('STUDENTHUB_DB_PASSWORD') ?: '');
define('SESSION_TIMEOUT_SECONDS', 15 * 60);
date_default_timezone_set(getenv('STUDENTHUB_TIMEZONE') ?: 'Asia/Kolkata');
