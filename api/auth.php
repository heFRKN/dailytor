<?php

declare(strict_types=1);

require_once '../includes/functions.php';

header('Content-Type: application/json');

global $pdo;
$aksiyon = $_GET['aksiyon'] ?? '';

if ($aksiyon === 'kayit' && istekMetodu() === 'POST') {
    $veri = json_decode(file_get_contents('php://input'), true);
    $kullaniciAdi = trim($veri['kullanici_adi'] ?? '');
    $sifre = $veri['sifre'] ?? '';
    $boy = !empty($veri['boy']) ? (float) $veri['boy'] : null;
    $kilo = !empty($veri['kilo']) ? (float) $veri['kilo'] : null;
    $dogumTarihi = !empty($veri['dogum_tarihi']) ? $veri['dogum_tarihi'] : null;
    $cinsiyet = $veri['cinsiyet'] ?? 'erkek';
    $aktiviteSeviyesi = $veri['aktivite_seviyesi'] ?? 'sedenter';

    if (strlen($kullaniciAdi) < 3 || strlen($kullaniciAdi) > 50) {
        jsonCikti(['hata' => 'Kullanıcı adı 3-50 karakter arasında olmalı'], 400);
    }

    if (strlen($sifre) < 6) {
        jsonCikti(['hata' => 'Şifre en az 6 karakter olmalı'], 400);
    }

    if (!in_array($cinsiyet, ['erkek', 'kadin'])) {
        jsonCikti(['hata' => 'Geçersiz cinsiyet'], 400);
    }

    $aktiviteSeviyeleri = ['sedenter', 'hafif', 'orta', 'yuksek', 'cok_yuksek'];
    if (!in_array($aktiviteSeviyesi, $aktiviteSeviyeleri)) {
        jsonCikti(['hata' => 'Geçersiz aktivite seviyesi'], 400);
    }

    $stmt = $pdo->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ?");
    $stmt->execute([$kullaniciAdi]);

    if ($stmt->fetch()) {
        jsonCikti(['hata' => 'Bu kullanıcı adı zaten alınmış'], 409);
    }

    $sifreHash = password_hash($sifre, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO kullanicilar (kullanici_adi, sifre, boy, kilo, dogum_tarihi, cinsiyet, aktivite_seviyesi)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt->execute([$kullaniciAdi, $sifreHash, $boy, $kilo, $dogumTarihi, $cinsiyet, $aktiviteSeviyesi])) {
        $_SESSION['kullanici_id'] = (int) $pdo->lastInsertId();
        jsonCikti(['mesaj' => 'Kayıt başarılı', 'kullanici_id' => $_SESSION['kullanici_id']], 201);
    }

    jsonCikti(['hata' => 'Kayıt başarısız'], 500);
}

if ($aksiyon === 'giris' && istekMetodu() === 'POST') {
    $veri = json_decode(file_get_contents('php://input'), true);
    $kullaniciAdi = trim($veri['kullanici_adi'] ?? '');
    $sifre = $veri['sifre'] ?? '';

    if (empty($kullaniciAdi) || empty($sifre)) {
        jsonCikti(['hata' => 'Tüm alanları doldurun'], 400);
    }

    $stmt = $pdo->prepare("SELECT id, kullanici_adi, sifre FROM kullanicilar WHERE kullanici_adi = ?");
    $stmt->execute([$kullaniciAdi]);
    $kullanici = $stmt->fetch();

    if (!$kullanici || !password_verify($sifre, $kullanici['sifre'])) {
        jsonCikti(['hata' => 'Kullanıcı adı veya şifre hatalı'], 401);
    }

    $_SESSION['kullanici_id'] = $kullanici['id'];

    jsonCikti([
        'mesaj' => 'Giriş başarılı',
        'kullanici' => [
            'id' => $kullanici['id'],
            'kullanici_adi' => $kullanici['kullanici_adi']
        ]
    ]);
}

if ($aksiyon === 'profil' && istekMetodu() === 'GET') {
    oturumZorla();

    $stmt = $pdo->prepare("SELECT kullanici_adi, boy, kilo, dogum_tarihi, cinsiyet, aktivite_seviyesi FROM kullanicilar WHERE id = ?");
    $stmt->execute([$_SESSION['kullanici_id']]);

    jsonCikti(['profil' => $stmt->fetch()]);
}

if ($aksiyon === 'profil-guncelle' && istekMetodu() === 'POST') {
    oturumZorla();

    $veri = json_decode(file_get_contents('php://input'), true);
    $alanlar = [];
    $degerler = [];

    if (!empty($veri['boy'])) {
        $alanlar[] = 'boy = ?';
        $degerler[] = (float) $veri['boy'];
    }
    if (!empty($veri['kilo'])) {
        $alanlar[] = 'kilo = ?';
        $degerler[] = (float) $veri['kilo'];
    }
    if (!empty($veri['dogum_tarihi'])) {
        $alanlar[] = 'dogum_tarihi = ?';
        $degerler[] = $veri['dogum_tarihi'];
    }
    if (!empty($veri['cinsiyet'])) {
        $alanlar[] = 'cinsiyet = ?';
        $degerler[] = $veri['cinsiyet'];
    }
    if (!empty($veri['aktivite_seviyesi'])) {
        $alanlar[] = 'aktivite_seviyesi = ?';
        $degerler[] = $veri['aktivite_seviyesi'];
    }

    if (empty($alanlar)) {
        jsonCikti(['hata' => 'Güncellenecek alan yok'], 400);
    }

    $degerler[] = $_SESSION['kullanici_id'];
    $sql = "UPDATE kullanicilar SET " . implode(', ', $alanlar) . " WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($degerler)) {
        jsonCikti(['mesaj' => 'Profil güncellendi']);
    }

    jsonCikti(['hata' => 'Güncelleme başarısız'], 500);
}

if ($aksiyon === 'cikis') {
    session_destroy();
    jsonCikti(['mesaj' => 'Çıkış yapıldı']);
}

jsonCikti(['hata' => 'Geçersiz istek'], 400);
