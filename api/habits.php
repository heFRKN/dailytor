<?php

declare(strict_types=1);

require_once '../includes/functions.php';

oturumZorla();

header('Content-Type: application/json');

global $pdo;
$kullaniciId = $_SESSION['kullanici_id'];
$metod = istekMetodu();

$gecerliTipler = ['checkbox', 'numeric', 'uyku', 'adim', 'kosu_bandi', 'bisiklet', 'agirlik', 'dis_kosu', 'snav', 'mekik', 'barfiks', 'muscle_up', 'plank'];

if ($metod === 'GET') {
    $stmt = $pdo->prepare("
        SELECT
            id, ad, tip, hedef_deger, birim, renk, siralama, tek_seferlik,
            kosu_bandi_hedef_hiz, kosu_bandi_hedef_egim, kosu_bandi_hedef_sure,
            bisiklet_hedef_sure, plank_hedef_sure, dis_kosu_hedef_tempo, hedef_adet
        FROM aliskanliklar
        WHERE kullanici_id = ?
        ORDER BY siralama, id
    ");
    $stmt->execute([$kullaniciId]);

    jsonCikti(['alinanlar' => $stmt->fetchAll()]);
}

if ($metod === 'POST') {
    $veri = json_decode(file_get_contents('php://input'), true);
    $ad = trim($veri['ad'] ?? '');
    $tip = $veri['tip'] ?? 'checkbox';
    $hedefDeger = !empty($veri['hedef_deger']) ? (int) $veri['hedef_deger'] : null;
    $birim = !empty($veri['birim']) ? trim($veri['birim']) : null;
    $renk = $veri['renk'] ?? '#6366f1';

    if (empty($ad)) {
        jsonCikti(['hata' => 'Alışkanlık adı gerekli'], 400);
    }

    if (!in_array($tip, $gecerliTipler)) {
        jsonCikti(['hata' => 'Geçersiz alışkanlık tipi'], 400);
    }

    if (in_array($tip, ['numeric', 'uyku', 'adim']) && $hedefDeger === null) {
        jsonCikti(['hata' => 'Bu tip için hedef değer gerekli'], 400);
    }

    $ekAlanlar = '';
    $ekDegerler = [];
    $tekSeferlik = !empty($veri['tek_seferlik']) ? 1 : 0;

    if ($tip === 'kosu_bandi') {
        $hiz = !empty($veri['hiz']) ? (float) $veri['hiz'] : 5.0;
        $egim = !empty($veri['egim']) ? (int) $veri['egim'] : 0;
        $sure = !empty($veri['sure']) ? (int) $veri['sure'] : 30;

        $ekAlanlar = ', kosu_bandi_hedef_hiz, kosu_bandi_hedef_egim, kosu_bandi_hedef_sure';
        $ekDegerler = [$hiz, $egim, $sure];
    } elseif ($tip === 'bisiklet') {
        $sure = !empty($veri['sure']) ? (int) $veri['sure'] : 30;

        $ekAlanlar = ', bisiklet_hedef_sure';
        $ekDegerler = [$sure];
    } elseif ($tip === 'agirlik') {
        $sure = !empty($veri['sure']) ? (int) $veri['sure'] : 60;
        $hedefDeger = $sure;

        // Weight training doesn't need extra fields, uses hedef_deger for duration
        $ekAlanlar = '';
        $ekDegerler = [];
    } elseif ($tip === 'dis_kosu') {
        $tempo = !empty($veri['tempo']) ? (float) $veri['tempo'] : 6.0;
        $hedefDeger = 0; // Hedef kullanılmıyor, tempo belirleyici

        $ekAlanlar = ', dis_kosu_hedef_tempo, tek_seferlik';
        $ekDegerler = [$tempo, $tekSeferlik];
    } elseif ($tip === 'plank') {
        $sure = !empty($veri['plank_sure']) ? (int) $veri['plank_sure'] : 60;
        $hedefDeger = $sure;

        $ekAlanlar = ', plank_hedef_sure, tek_seferlik';
        $ekDegerler = [$sure, $tekSeferlik];
    } elseif (in_array($tip, ['snav', 'mekik', 'barfiks', 'muscle_up'])) {
        $adet = !empty($veri['hedef_adet']) ? (int) $veri['hedef_adet'] : 10;
        $hedefDeger = $adet;
        $birim = 'adet';

        $ekAlanlar = ', hedef_adet, tek_seferlik';
        $ekDegerler = [$adet, $tekSeferlik];
    }

    // Eğer ekAlanlar'da zaten tek_seferlik yoksa, ekle
    if (strpos($ekAlanlar, 'tek_seferlik') === false) {
        $ekAlanlar .= ', tek_seferlik';
        $ekDegerler[] = $tekSeferlik;
    }

    $stmt = $pdo->prepare("
        INSERT INTO aliskanliklar (kullanici_id, ad, tip, hedef_deger, birim, renk$ekAlanlar)
        VALUES (?, ?, ?, ?, ?, ?" . ($ekDegerler ? str_repeat(',?', count($ekDegerler)) : '') . ")
    ");

    $params = [$kullaniciId, $ad, $tip, $hedefDeger, $birim, $renk, ...$ekDegerler];

    if ($stmt->execute($params)) {
        $yeniId = $pdo->lastInsertId();

        // Tek seferlik aktivite ise, bugün için otomatik kayıt oluştur
        if ($tekSeferlik) {
            $bugun = date('Y-m-d');
            $kayitStmt = $pdo->prepare("INSERT INTO aliskanlik_kayitlari (aliskanlik_id, tarih, deger, tamamlandi) VALUES (?, ?, 0, FALSE)");
            $kayitStmt->execute([$yeniId, $bugun]);
        }

        jsonCikti(['mesaj' => 'Alışkanlık oluşturuldu', 'id' => $yeniId, 'tek_seferlik' => $tekSeferlik], 201);
    }

    jsonCikti(['hata' => 'Oluşturma başarısız'], 500);
}

if ($metod === 'PUT') {
    $veri = json_decode(file_get_contents('php://input'), true);
    $id = (int) ($veri['id'] ?? 0);

    if (!$id) {
        jsonCikti(['hata' => 'ID gerekli'], 400);
    }

    $kontrol = $pdo->prepare("SELECT id FROM aliskanliklar WHERE id = ? AND kullanici_id = ?");
    $kontrol->execute([$id, $kullaniciId]);

    if (!$kontrol->fetch()) {
        jsonCikti(['hata' => 'Alışkanlık bulunamadı'], 404);
    }

    $alanlar = [];
    $degerler = [];

    $guncelTip = $veri['tip'] ?? '';
    if ($guncelTip === 'kosu_bandi') {
        if (isset($veri['hiz'])) $veri['kosu_bandi_hedef_hiz'] = $veri['hiz'];
        if (isset($veri['egim'])) $veri['kosu_bandi_hedef_egim'] = $veri['egim'];
        if (isset($veri['sure'])) $veri['kosu_bandi_hedef_sure'] = $veri['sure'];
    } elseif ($guncelTip === 'bisiklet') {
        if (isset($veri['sure'])) $veri['bisiklet_hedef_sure'] = $veri['sure'];
    } elseif ($guncelTip === 'plank') {
        if (isset($veri['plank_sure'])) $veri['plank_hedef_sure'] = $veri['plank_sure'];
    } elseif ($guncelTip === 'dis_kosu') {
        if (isset($veri['tempo'])) $veri['dis_kosu_hedef_tempo'] = $veri['tempo'];
    } elseif (in_array($guncelTip, ['snav', 'mekik', 'barfiks', 'muscle_up'])) {
        if (isset($veri['hedef_adet'])) $veri['hedef_adet'] = $veri['hedef_adet'];
    }
    $turler = [
        'ad' => 's', 'tip' => 's', 'hedef_deger' => 'i', 'birim' => 's',
        'renk' => 's', 'siralama' => 'i', 'tek_seferlik' => 'i',
        'kosu_bandi_hedef_hiz' => 'd', 'kosu_bandi_hedef_egim' => 'i',
        'kosu_bandi_hedef_sure' => 'i', 'bisiklet_hedef_sure' => 'i',
        'plank_hedef_sure' => 'i', 'dis_kosu_hedef_tempo' => 'd',
        'hedef_adet' => 'i'
    ];

    foreach ($turler as $alan => $tur) {
        if (array_key_exists($alan, $veri)) {
            $alanlar[] = "$alan = ?";
            $degerler[] = $veri[$alan];
        }
    }

    if (empty($alanlar)) {
        jsonCikti(['hata' => 'Güncellenecek alan yok'], 400);
    }

    $degerler[] = $id;
    $sql = "UPDATE aliskanliklar SET " . implode(', ', $alanlar) . " WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($degerler)) {
        jsonCikti(['mesaj' => 'Güncellendi']);
    }

    jsonCikti(['hata' => 'Güncelleme başarısız'], 500);
}

if ($metod === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);

    if (!$id) {
        jsonCikti(['hata' => 'ID gerekli'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM aliskanliklar WHERE id = ? AND kullanici_id = ?");

    if ($stmt->execute([$id, $kullaniciId])) {
        if ($stmt->rowCount() > 0) {
            jsonCikti(['mesaj' => 'Silindi']);
        }
        jsonCikti(['hata' => 'Alışkanlık bulunamadı'], 404);
    }

    jsonCikti(['hata' => 'Silme başarısız'], 500);
}

jsonCikti(['hata' => 'Geçersiz metod'], 405);
