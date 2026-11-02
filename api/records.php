<?php

declare(strict_types=1);

require_once '../includes/functions.php';

oturumZorla();

header('Content-Type: application/json');

global $pdo;
$kullaniciId = $_SESSION['kullanici_id'];
$metod = istekMetodu();

if ($metod === 'GET') {
    $tarih = $_GET['tarih'] ?? date('Y-m-d');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
        jsonCikti(['hata' => 'Geçersiz tarih formatı'], 400);
    }

    // Kullanıcının sabit "Yemek Kaydı" (kalori) alışkanlığı yoksa otomatik oluştur
    $kaloriKontrol = $pdo->prepare("SELECT id FROM aliskanliklar WHERE kullanici_id = ? AND tip = 'kalori'");
    $kaloriKontrol->execute([$kullaniciId]);
    if (!$kaloriKontrol->fetch()) {
        $pdo->prepare("INSERT INTO aliskanliklar (kullanici_id, ad, tip, hedef_deger, siralama) VALUES (?, 'Günlük Yemek', 'kalori', 2000, 0)")->execute([$kullaniciId]);
    }

    bugununKayitlariniOlustur($kullaniciId, $tarih);

    // Tek seferlik olmayanlar için otomatik kayıt oluştur
    $stmt = $pdo->prepare("
        SELECT
            k.id as kayit_id,
            k.aliskanlik_id,
            k.deger,
            k.deger2,
            k.deger3,
            k.deger4,
            k.yakilan_kalori,
            k.tamamlandi,
            a.ad,
            a.tip,
            a.hedef_deger,
            a.birim,
            a.renk,
            a.tek_seferlik,
            a.kosu_bandi_hedef_hiz,
            a.kosu_bandi_hedef_egim,
            a.kosu_bandi_hedef_sure,
            a.bisiklet_hedef_sure,
            a.plank_hedef_sure,
            a.dis_kosu_hedef_tempo,
            a.hedef_adet
        FROM aliskanlik_kayitlari k
        INNER JOIN aliskanliklar a ON a.id = k.aliskanlik_id
        WHERE a.kullanici_id = ? AND k.tarih = ?
        ORDER BY a.siralama, a.id
    ");
    $stmt->execute([$kullaniciId, $tarih]);
    $kayitlar = $stmt->fetchAll();

    $normalKayitlar = [];
    $yemekKaydi = null;

    // Kayıtları ayır ve kalori alışkanlığı için yemekleri getir
    foreach ($kayitlar as &$kayit) {
        if ($kayit['tip'] === 'kalori') {
            $yemeklerStmt = $pdo->prepare("
                SELECT id, yemek_adi, emoji, kalori, protein, karbonhidrat, yag, seker, sodyum, lif, foto_yolu 
                FROM yemek_kayitlari 
                WHERE aliskanlik_id = ? AND tarih = ? 
                ORDER BY id
            ");
            $yemeklerStmt->execute([$kayit['aliskanlik_id'], $tarih]);
            $kayit['yemekler'] = $yemeklerStmt->fetchAll();
            $yemekKaydi = $kayit;
        } else {
            $kayit['yemekler'] = [];
            $normalKayitlar[] = $kayit;
        }
    }
    unset($kayit);

    $yuzde = tamamlanmaYuzdesi($kullaniciId, $tarih);
    $kaloriDengesi = gunlukKaloriDengesi($kullaniciId, $tarih);

    // Günlük notu getir
    $notStmt = $pdo->prepare("SELECT not_metni FROM gunluk_notlar WHERE kullanici_id = ? AND tarih = ?");
    $notStmt->execute([$kullaniciId, $tarih]);
    $notKayit = $notStmt->fetch();
    $not = $notKayit ? $notKayit['not_metni'] : '';

    jsonCikti([
        'kayitlar' => $normalKayitlar,
        'yemek_kaydi' => $yemekKaydi,
        'tamamlanma_yuzdesi' => $yuzde,
        'kalori_dengesi' => $kaloriDengesi,
        'not' => $not,
        'tarih' => $tarih
    ]);
}

if ($metod === 'POST') {
    $veri = json_decode(file_get_contents('php://input'), true);
    $alinanId = (int) ($veri['aliskanlik_id'] ?? 0);
    $tarih = $veri['tarih'] ?? date('Y-m-d');
    $deger = isset($veri['deger']) ? (int) $veri['deger'] : null;
    $deger2 = isset($veri['deger2']) ? (int) $veri['deger2'] : null;
    $deger3 = isset($veri['deger3']) ? (float) $veri['deger3'] : null;
    $deger4 = isset($veri['deger4']) ? (int) $veri['deger4'] : null;

    if (!$alinanId) {
        jsonCikti(['hata' => 'Alışkanlık ID gerekli'], 400);
    }

    $stmt = $pdo->prepare("SELECT tip, hedef_deger FROM aliskanliklar WHERE id = ? AND kullanici_id = ?");
    $stmt->execute([$alinanId, $kullaniciId]);
    $alinan = $stmt->fetch();

    if (!$alinan) {
        jsonCikti(['hata' => 'Alışkanlık bulunamadı'], 404);
    }

    $tamamlandi = false;
    $yakilanKalori = 0;

    switch ($alinan['tip']) {
        case 'checkbox':
            $tamamlandi = isset($veri['tamamlandi']) ? (bool)$veri['tamamlandi'] : false;
            break;
        case 'numeric':
        case 'uyku':
            $tamamlandi = $alinan['hedef_deger'] && $deger >= $alinan['hedef_deger'];
            break;
        case 'adim':
            $tamamlandi = $alinan['hedef_deger'] && $deger >= $alinan['hedef_deger'];
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $yakilanKalori = adimKaloriHesapla($deger ?? 0, (float) $kilo);
            break;
        case 'kalori':
            $tamamlandi = $deger > 0;
            break;
        case 'kosu_bandi':
            $tamamlandi = $deger2 > 0;
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $yakilanKalori = kosuBandiKaloriHesapla((float) $kilo, $deger3 ?? 5.0, $deger4 ?? 0, $deger2 ?? 0);
            break;
        case 'bisiklet':
            $tamamlandi = $deger2 > 0;
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $yakilanKalori = bisikletKaloriHesapla((float) $kilo, $deger2 ?? 0);
            break;
        case 'agirlik':
            $tamamlandi = $deger2 > 0;
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $yakilanKalori = agirlikKaloriHesapla((float) $kilo, $deger2 ?? 0);
            break;
        case 'dis_kosu':
            // deger: tempo, deger2: sure
            $tamamlandi = $deger2 > 0;
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $tempo = $deger ?? 6.0;
            $yakilanKalori = disKosuKaloriHesapla((float) $kilo, (float) $tempo, (int) ($deger2 ?? 0));
            break;
        case 'snav':
            $tamamlandi = $alinan['hedef_deger'] && $deger >= $alinan['hedef_deger'];
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $yakilanKalori = snavKaloriHesapla((float) $kilo, (int) ($deger ?? 0));
            break;
        case 'mekik':
            $tamamlandi = $alinan['hedef_deger'] && $deger >= $alinan['hedef_deger'];
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $yakilanKalori = mekikKaloriHesapla((float) $kilo, (int) ($deger ?? 0));
            break;
        case 'barfiks':
            $tamamlandi = $alinan['hedef_deger'] && $deger >= $alinan['hedef_deger'];
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $yakilanKalori = barfiksKaloriHesapla((float) $kilo, (int) ($deger ?? 0));
            break;
        case 'muscle_up':
            $tamamlandi = $alinan['hedef_deger'] && $deger >= $alinan['hedef_deger'];
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $yakilanKalori = muscleUpKaloriHesapla((float) $kilo, (int) ($deger ?? 0));
            break;
        case 'plank':
            $tamamlandi = $alinan['hedef_deger'] && $deger >= $alinan['hedef_deger'];
            $profil = kullaniciProfilGetir($kullaniciId);
            $kilo = $profil['kilo'] ?? 70;
            $yakilanKalori = plankKaloriHesapla((float) $kilo, (int) ($deger ?? 0));
            break;
    }

    $stmt = $pdo->prepare("
        INSERT INTO aliskanlik_kayitlari (aliskanlik_id, tarih, deger, deger2, deger3, deger4, tamamlandi, yakilan_kalori)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            deger = VALUES(deger),
            deger2 = VALUES(deger2),
            deger3 = VALUES(deger3),
            deger4 = VALUES(deger4),
            tamamlandi = VALUES(tamamlandi),
            yakilan_kalori = VALUES(yakilan_kalori)
    ");
    $stmt->execute([$alinanId, $tarih, $deger, $deger2, $deger3, $deger4, $tamamlandi, $yakilanKalori]);

    $yuzde = tamamlanmaYuzdesi($kullaniciId, $tarih);
    $kaloriDengesi = gunlukKaloriDengesi($kullaniciId, $tarih);

    jsonCikti([
        'mesaj' => 'Kayıt güncellendi',
        'tamamlandi' => $tamamlandi,
        'tamamlanma_yuzdesi' => $yuzde,
        'kalori_dengesi' => $kaloriDengesi
    ]);
}

if ($metod === 'PUT') {
    $veri = json_decode(file_get_contents('php://input'), true);
    $tarih = $veri['tarih'] ?? date('Y-m-d');
    $kayitlar = $veri['kayitlar'] ?? [];

    if (empty($kayitlar)) {
        jsonCikti(['hata' => 'Kayıt verisi gerekli'], 400);
    }

    foreach ($kayitlar as $kayit) {
        $alinanId = (int) $kayit['aliskanlik_id'];
        $deger = isset($kayit['deger']) ? (int) $kayit['deger'] : null;
        $deger2 = isset($kayit['deger2']) ? (int) $kayit['deger2'] : null;
        $deger3 = isset($kayit['deger3']) ? (float) $kayit['deger3'] : null;
        $deger4 = isset($kayit['deger4']) ? (int) $kayit['deger4'] : null;
        $tamamlandi = (bool) $kayit['tamamlandi'];

        $kontrol = $pdo->prepare("SELECT tip, hedef_deger FROM aliskanliklar WHERE id = ? AND kullanici_id = ?");
        $kontrol->execute([$alinanId, $kullaniciId]);

        if ($kontrol->fetch()) {
            $stmt = $pdo->prepare("
                INSERT INTO aliskanlik_kayitlari (aliskanlik_id, tarih, deger, deger2, deger3, deger4, tamamlandi)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    deger = VALUES(deger),
                    deger2 = VALUES(deger2),
                    deger3 = VALUES(deger3),
                    deger4 = VALUES(deger4),
                    tamamlandi = VALUES(tamamlandi)
            ");
            $stmt->execute([$alinanId, $tarih, $deger, $deger2, $deger3, $deger4, $tamamlandi]);
        }
    }

    $yuzde = tamamlanmaYuzdesi($kullaniciId, $tarih);
    $kaloriDengesi = gunlukKaloriDengesi($kullaniciId, $tarih);

    jsonCikti([
        'mesaj' => 'Kayıtlar güncellendi',
        'tamamlanma_yuzdesi' => $yuzde,
        'kalori_dengesi' => $kaloriDengesi
    ]);
}

jsonCikti(['hata' => 'Geçersiz metod'], 405);
