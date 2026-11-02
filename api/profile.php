<?php
declare(strict_types=1);
require_once '../includes/functions.php';
oturumZorla();
header('Content-Type: application/json');

global $pdo;
$kullaniciId = $_SESSION['kullanici_id'];
$metod = $_SERVER['REQUEST_METHOD'];

// Profile Update
if ($metod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        jsonCikti(['hata' => 'Veri alınamadı'], 400);
    }

    $boy = (float)($data['boy'] ?? 0);
    $kilo = (float)($data['kilo'] ?? 0);
    $dogumTarihi = $data['dogum_tarihi'] ?? '';
    $cinsiyet = $data['cinsiyet'] ?? 'erkek';
    $aktiviteSeviyesi = $data['aktivite_seviyesi'] ?? 'sedenter';
    $weightUpdateType = $data['weight_update_type'] ?? null;

    if ($boy <= 0 || $kilo <= 0 || !$dogumTarihi) {
        jsonCikti(['hata' => 'Lütfen tüm alanları geçerli şekilde doldurun'], 400);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE kullanicilar
            SET boy = ?, kilo = ?, dogum_tarihi = ?, cinsiyet = ?, aktivite_seviyesi = ?
            WHERE id = ?
        ");
        $stmt->execute([$boy, $kilo, $dogumTarihi, $cinsiyet, $aktiviteSeviyesi, $kullaniciId]);

        // If it's a progress update, record it in history
        if ($weightUpdateType === 'progress') {
            $stmt = $pdo->prepare("
                INSERT INTO kilo_kayitlari (kullanici_id, kilo, tarih)
                VALUES (?, ?, CURRENT_DATE)
                ON DUPLICATE KEY UPDATE kilo = VALUES(kilo)
            ");
            $stmt->execute([$kullaniciId, $kilo]);
        }

        $pdo->commit();
        jsonCikti(['mesaj' => 'Profil güncellendi']);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        jsonCikti(['hata' => 'Veritabanı hatası'], 500);
    }
}

// Change Password
if ($metod === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword)) {
        jsonCikti(['hata' => 'Şifre alanları gerekli'], 400);
    }

    if (strlen($newPassword) < 6) {
        jsonCikti(['hata' => 'Yeni şifre en az 6 karakter olmalı'], 400);
    }

    // Verify current password
    $stmt = $pdo->prepare("SELECT sifre FROM kullanicilar WHERE id = ?");
    $stmt->execute([$kullaniciId]);
    $kullanici = $stmt->fetch();

    if (!$kullanici || !password_verify($currentPassword, $kullanici['sifre'])) {
        jsonCikti(['hata' => 'Mevcut şifre hatalı'], 401);
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE kullanicilar SET sifre = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $kullaniciId]);

    jsonCikti(['mesaj' => 'Şifre başarıyla değiştirildi']);
}

// Weight History & Profile Info
if ($metod === 'GET') {
    if (isset($_GET['gecmis'])) {
        $stmt = $pdo->prepare("SELECT kilo, tarih FROM kilo_kayitlari WHERE kullanici_id = ? ORDER BY tarih ASC");
        $stmt->execute([$kullaniciId]);
        jsonCikti(['kayitlar' => $stmt->fetchAll()]);
    }
}

// Delete All Data
if ($metod === 'DELETE') {
    try {
        $pdo->beginTransaction();

        // Delete habit records
        $stmt = $pdo->prepare("DELETE FROM aliskanlik_kayitlari WHERE aliskanlik_id IN (SELECT id FROM aliskanliklar WHERE kullanici_id = ?)");
        $stmt->execute([$kullaniciId]);

        // Delete habits
        $stmt = $pdo->prepare("DELETE FROM aliskanliklar WHERE kullanici_id = ?");
        $stmt->execute([$kullaniciId]);

        // Delete notes
        $stmt = $pdo->prepare("DELETE FROM gunluk_notlar WHERE kullanici_id = ?");
        $stmt->execute([$kullaniciId]);

        // Delete weight records
        $stmt = $pdo->prepare("DELETE FROM kilo_kayitlari WHERE kullanici_id = ?");
        $stmt->execute([$kullaniciId]);

        // Delete user account
        $stmt = $pdo->prepare("DELETE FROM kullanicilar WHERE id = ?");
        $stmt->execute([$kullaniciId]);

        $pdo->commit();

        jsonCikti(['mesaj' => 'Tüm veriler silindi']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        jsonCikti(['hata' => 'Silme işlemi başarısız'], 500);
    }
}

jsonCikti(['hata' => 'Geçersiz metod'], 405);
