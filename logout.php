<?php
require_once 'config.php';

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit;
?>