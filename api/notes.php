<?php
declare(strict_types=1);
require_once '../includes/functions.php';
oturumZorla();
header('Content-Type: application/json');

global $pdo;
$kullaniciId = $_SESSION['kullanici_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $tarih = $_GET['tarih'] ?? date('Y-m-d');
    $stmt = $pdo->prepare("SELECT not_metni FROM gunluk_notlar WHERE kullanici_id = ? AND tarih = ?");
    $stmt->execute([$kullaniciId, $tarih]);
    $not = $stmt->fetchColumn();
    jsonCikti(['not' => $not ?: '']);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $tarih = $data['tarih'] ?? date('Y-m-d');
    $not = $data['not'] ?? '';

    $stmt = $pdo->prepare("
        INSERT INTO gunluk_notlar (kullanici_id, tarih, not_metni) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE not_metni = VALUES(not_metni)
    ");
    $stmt->execute([$kullaniciId, $tarih, $not]);
    jsonCikti(['mesaj' => 'Not kaydedildi']);
    exit;
}

jsonCikti(['hata' => 'Geçersiz istek'], 405);
