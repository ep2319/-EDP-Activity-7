<?php
// config.php - Main configuration and Database Initialization
session_start();
require_once 'Database.php';

$database = new MySQLDatabase();
$pdo = $database->getConnection();
?>
