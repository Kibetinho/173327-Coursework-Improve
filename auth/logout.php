<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../services/Auth.php';

Auth::logout();
header('Location: ' . ROOT_URL . '/index.php');
exit;
?>
<link rel="stylesheet" href="/css/general.css">
<link rel="stylesheet" href="/css/user.css">


