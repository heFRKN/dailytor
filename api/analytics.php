<?php

declare(strict_types=1);

require_once '../includes/functions.php';

oturumZorla();

header('Content-Type: application/json');

global $pdo;
$kullaniciId = $_SESSION['kullanici_id'];
$tip = $_GET['tip'] ?? '';
$gunSayisi = (int) ($_GET['gun_sayisi'] ?? 30);

// Kullanıcı kayıt tarihini getir
$userStmt = $pdo->prepare("SELECT olusturulma_zamani FROM kullanicilar WHERE id = ?");
$userStmt->execute([$kullaniciId]);
$kayitTarihi = $userStmt->fetchColumn();
$kayitGunuStr = date('Y-m-d', strtotime($kayitTarihi));

if ($tip === 'gunluk-tamamlanma') {
    $stmt = $pdo->prepare("
        SELECT
            k.tarih,
            COUNT(*) as toplam,
            SUM(CASE WHEN k.tamamlandi = TRUE THEN 1 ELSE 0 END) as tamamlanan
        FROM aliskanlik_kayitlari k
        INNER JOIN aliskanliklar a ON a.id = k.aliskanlik_id
        WHERE a.kullanici_id = ? 
          AND k.tarih >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
          AND k.tarih >= ?
        GROUP BY k.tarih
        ORDER BY k.tarih
    ");
    $stmt->execute([$kullaniciId, $gunSayisi, $kayitGunuStr]);

    $veriler = [];
    $etiketler = [];

    while ($satir = $stmt->fetch()) {
        $etiketler[] = date('d.m', strtotime($satir['tarih']));
        $veriler[] = $satir['toplam'] > 0
            ? round(($satir['tamamlanan'] / $satir['toplam']) * 100, 1)
            : 0;
    }

    jsonCikti([
        'etiketler' => $etiketler,
        'veriler' => $veriler
    ]);
}

if ($tip === 'haftalik-karsilastirma') {
    $stmt = $pdo->prepare("
        SELECT
            k.tarih,
            COUNT(*) as toplam,
            SUM(CASE WHEN k.tamamlandi = TRUE THEN 1 ELSE 0 END) as tamamlanan
        FROM aliskanlik_kayitlari k
        INNER JOIN aliskanliklar a ON a.id = k.aliskanlik_id
        WHERE a.kullanici_id = ? 
          AND k.tarih >= DATE_SUB(CURDATE(), INTERVAL 7 WEEK)
          AND k.tarih >= ?
        GROUP BY k.tarih
        ORDER BY k.tarih
    ");
    $stmt->execute([$kullaniciId, $kayitGunuStr]);

    $haftalar = ['Bu Hafta', 'Geçen Hafta', '2 Hafta Önce', '3 Hafta Önce', '4 Hafta Önce', '5 Hafta Önce', '6 Hafta Önce'];
    $haftaVerileri = array_fill(0, 7, 0);
    $haftaToplamlari = array_fill(0, 7, 0);

    while ($satir = $stmt->fetch()) {
        $gununZamani = strtotime($satir['tarih']);
        $buHafta = (int) date('W', $gununZamani);
        $simdikiHafta = (int) date('W');
        $fark = $simdikiHafta - $buHafta;

        if ($fark >= 0 && $fark < 7) {
            if ($satir['toplam'] > 0) {
                $haftaVerileri[$fark] += ($satir['tamamlanan'] / $satir['toplam']) * 100;
                $haftaToplamlari[$fark]++;
            }
        }
    }

    $sonVeriler = [];
    for ($i = 0; $i < 7; $i++) {
        $sonVeriler[] = $haftaToplamlari[$i] > 0 ? round($haftaVerileri[$i] / $haftaToplamlari[$i], 1) : 0;
    }

    jsonCikti([
        'etiketler' => array_reverse($haftalar),
        'veriler' => array_reverse($sonVeriler)
    ]);
}

if ($tip === 'alinan-ilerleme') {
    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.ad,
            a.tip,
            a.hedef_deger,
            a.birim,
            a.renk
        FROM aliskanliklar a
        WHERE a.kullanici_id = ?
        ORDER BY a.siralama, a.id
    ");
    $stmt->execute([$kullaniciId]);
    $alinanlar = $stmt->fetchAll();

    $alinanVerileri = [];

    foreach ($alinanlar as $alinan) {
        $stmt2 = $pdo->prepare("
            SELECT
                COUNT(*) as toplam,
                SUM(CASE WHEN tamamlandi = TRUE THEN 1 ELSE 0 END) as tamamlanan
            FROM aliskanlik_kayitlari
            WHERE aliskanlik_id = ? 
              AND tarih >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              AND tarih >= ?
        ");
        $stmt2->execute([$alinan['id'], $gunSayisi, $kayitGunuStr]);
        $istatistik = $stmt2->fetch();

        $alinanVerileri[] = [
            'ad' => $alinan['ad'],
            'renk' => $alinan['renk'],
            'yuzde' => $istatistik['toplam'] > 0
                ? round(($istatistik['tamamlanan'] / $istatistik['toplam']) * 100, 1)
                : 0
        ];
    }

    jsonCikti(['alinanlar' => $alinanVerileri]);
}

if ($tip === 'aylik-ozet') {
    $stmt = $pdo->prepare("
        SELECT
            DATE_FORMAT(tarih, '%Y-%m') as ay,
            COUNT(*) as toplam,
            SUM(CASE WHEN tamamlandi = TRUE THEN 1 ELSE 0 END) as tamamlanan
        FROM aliskanlik_kayitlari k
        INNER JOIN aliskanliklar a ON a.id = k.aliskanlik_id
        WHERE a.kullanici_id = ? 
          AND tarih >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
          AND tarih >= ?
        GROUP BY DATE_FORMAT(tarih, '%Y-%m')
        ORDER BY ay
    ");
    $stmt->execute([$kullaniciId, $kayitGunuStr]);

    $veriler = [];
    $etiketler = [];

    while ($satir = $stmt->fetch()) {
        $ayParcalari = explode('-', $satir['ay']);
        $yil = $ayParcalari[0];
        $ay = (int) $ayParcalari[1];

        $ayAdlari = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
        $etiketler[] = $ayAdlari[$ay - 1] . ' ' . substr($yil, -2);
        $veriler[] = $satir['toplam'] > 0
            ? round(($satir['tamamlanan'] / $satir['toplam']) * 100, 1)
            : 0;
    }

    jsonCikti([
        'etiketler' => $etiketler,
        'veriler' => $veriler
    ]);
}

if ($tip === 'kalori-dengesi') {
    $stmt = $pdo->prepare("
        SELECT
            k.tarih,
            SUM(CASE WHEN a.tip = 'kalori' THEN k.deger ELSE 0 END) as alinan,
            SUM(k.yakilan_kalori) as egzersiz
        FROM aliskanlik_kayitlari k
        INNER JOIN aliskanliklar a ON a.id = k.aliskanlik_id
        WHERE a.kullanici_id = ? 
          AND k.tarih >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
          AND k.tarih >= ?
        GROUP BY k.tarih
        ORDER BY k.tarih
    ");
    $stmt->execute([$kullaniciId, $gunSayisi, $kayitGunuStr]);

    $profil = kullaniciProfilGetir($kullaniciId);
    $bmr = gunlukKaloriIhtiyaci($kullaniciId);

    $veriler = [];
    $etiketler = [];

    while ($satir = $stmt->fetch()) {
        $alinan = (int) $satir['alinan'];
        $yakilan = (int) $satir['egzersiz'];
        $toplamYakilan = $bmr + $yakilan;
        $fark = $alinan - $toplamYakilan;

        $etiketler[] = date('d.m', strtotime($satir['tarih']));
        $veriler[] = $fark;
    }

    jsonCikti([
        'etiketler' => $etiketler,
        'veriler' => $veriler,
        'bmr' => $bmr
    ]);
}

if ($tip === 'kilo-tahmini') {
    $profil = kullaniciProfilGetir($kullaniciId);
    $mevcutKilo = $profil['kilo'] ?? 70;

    // Kullanıcının kayıt tarihinden bugüne kadar geçen gerçek gün sayısını hesapla (maks 30 gün)
    $tsKayit = strtotime($kayitGunuStr);
    $tsBugün = time();
    $gecenGun = floor(($tsBugün - $tsKayit) / 86400) + 1;
    $hesapGunSayisi = min(30, (int)$gecenGun);

    // Gerçekleşen ortalama verileri çek (sadece kayıt tarihinden itibaren)
    $stmt = $pdo->prepare("
        SELECT
            SUM(CASE WHEN a.tip = 'kalori' THEN k.deger ELSE 0 END) as toplam_alinan,
            SUM(k.yakilan_kalori) as toplam_egzersiz,
            COUNT(DISTINCT k.tarih) as dolu_gun_sayisi
        FROM aliskanlik_kayitlari k
        INNER JOIN aliskanliklar a ON a.id = k.aliskanlik_id
        WHERE a.kullanici_id = ? 
          AND k.tarih >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
          AND k.tarih >= ?
    ");
    $stmt->execute([$kullaniciId, $kayitGunuStr]);
    $veri = $stmt->fetch();

    $bmr = gunlukKaloriIhtiyaci($kullaniciId);
    
    // Eğer kullanıcı henüz yeni kayıt olduysa ve hiç kaydı yoksa, varsayılan olarak BMR kullanılır
    $gunSayisi = max(1, $hesapGunSayisi); 

    $gunlukOrtalamaAlinan = ($veri['toplam_alinan'] ?: 0) / $gunSayisi;
    $gunlukOrtalamaEgzersiz = ($veri['toplam_egzersiz'] ?: 0) / $gunSayisi;
    $gunlukOrtalamaYakilan = $bmr + $gunlukOrtalamaEgzersiz;
    $gunlukOrtalamaFark = $gunlukOrtalamaAlinan - $gunlukOrtalamaYakilan;

    // 30 günlük tahmin
    $tahminler = [];
    $mevcutK = $mevcutKilo;

    for ($i = 1; $i <= 4; $i++) {
        $günSayisiTahmin = ($i === 4) ? 30 : (7 * $i); // 1 Ay için 30 gün, diğerleri için 7, 14, 21 gün
        $kaloriFarki = $gunlukOrtalamaFark * $günSayisiTahmin;
        $kiloFarki = $kaloriFarki / 7700;
        $tahminiKilo = $mevcutKilo + $kiloFarki;

        $haftaAdlari = ['1 Hafta', '2 Hafta', '3 Hafta', '1 Ay'];
        $tahminler[] = [
            'label' => $haftaAdlari[$i - 1],
            'kilo' => round($tahminiKilo, 1),
            'fark' => round($kiloFarki, 1),
            'kalori_farki' => round($kaloriFarki)
        ];
    }

    jsonCikti([
        'mevcut_kilo' => $mevcutKilo,
        'tahminler' => $tahminler,
        'gunluk_ortalama' => [
            'alinan' => round($gunlukOrtalamaAlinan),
            'yakilan' => round($gunlukOrtalamaYakilan),
            'fark' => round($gunlukOrtalamaFark)
        ]
    ]);
}

jsonCikti(['hata' => 'Geçersiz analiz tipi'], 400);
