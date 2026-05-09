<?php
// Central PDO connection used by all modules.
// Keep credentials here so auth, candidate, and recruiter code do not duplicate DB setup.
$host = "localhost";
$dbname = "recruitment_system";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Do not expose the real database error to users; it can leak server details.
    die("Database connection failed.");
}
