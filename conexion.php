<?php
$hostDB = "127.0.0.1";
$nombreDB = "chombis";
$usuarioDB = "root";
$claveDB = "";

$hostPDO = "mysql:host=$hostDB;dbname=$nombreDB";

$pdo = new PDO($hostPDO, $usuarioDB, $claveDB, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);