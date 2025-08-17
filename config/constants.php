<?php
// Database configuration constants
// Adjust these to match your local PostgreSQL setup

define('DB_HOST_NAME', 'localhost');
define('DB_USER_NAME', 'postgres');
define('DB_PASSWORD', '269696');
define('DB_PORT', 5432);
define('DB_NAME', 'coursework_db');

// Application settings
define('APP_NAME', 'Coursework CMS');
// ROOT_URL points to the site root (where index.php lives)
define('ROOT_URL', '/Coursework');
// BASE_URL points to this app folder (nested under ROOT_URL)
define('BASE_URL', '/Coursework/Coursework');

// Mail settings (using PHP mail())
define('MAIL_FROM_ADDRESS', 'no-reply@example.com');
define('MAIL_FROM_NAME', 'Coursework CMS');

// User types
define('USER_TYPE_SUPER', 'Super_User');
define('USER_TYPE_ADMIN', 'Administrator');
define('USER_TYPE_AUTHOR', 'Author');


