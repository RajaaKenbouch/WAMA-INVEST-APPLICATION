<?php
require_once 'db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM cv WHERE id = ?");
$stmt->execute([$id]);
header("Location: liste_cv.php");