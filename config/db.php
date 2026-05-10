<?php
// Central PDO connection used by all modules.
// Keep credentials here so auth, candidate, and recruiter code do not duplicate DB setup.
$host = "127.0.0.1";
$port = "8889";
$dbname = "recruitment_system";
$username = "root";
$password = "root";
$socket = "/Applications/MAMP/tmp/mysql/mysql.sock";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    try {
        // MAMP on Mac can also connect through its MySQL socket.
        $dsn = "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database connection failed. Check that MAMP MySQL is running and that the database recruitment_system exists.");
    }
}
