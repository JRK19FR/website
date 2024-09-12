<?php
$dsn = 'mysql:host=mysql4.ouiheberg.com;dbname=s8774_jrk;port=3306';
$username = 'u8774_NOhMmG1JEe';
$password = 'D.@gUMyPi3Jkq+NCVtJULl@E';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['message' => 'Database connection failed']));
}
