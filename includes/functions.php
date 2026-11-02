<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

session_start();

function oturumKontrol(): bool
{
    return isset($_SESSION['kullanici_id']);
}

function kullaniciGetir(): ?array
{
    global $pdo;

    if (!oturumKontrol()) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id, kullanici_adi, olusturulma_zamani FROM kullanicilar WHERE id = ?");
    $stmt->execute([$_SESSION['kullanici_id']]);

    return $stmt->fetch() ?: null;
}

function oturumZorla(): void
{
    if (!oturumKontrol()) {
        header('Location: /index.php');
        exit;
    }
}

function jsonCikti(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function bugununKayitlariniOlustur(int $kullaniciId, string $tarih): void
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT a.id
        FROM aliskanliklar a
        LEFT JOIN aliskanlik_kayitlari k ON k.aliskanlik_id = a.id AND k.tarih = ?
        WHERE a.kullanici_id = ? AND k.id IS NULL AND a.tek_seferlik = 0
    ");
    $stmt->execute([$tarih, $kullaniciId]);
    $alinanIdler = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($alinanIdler)) {
        return;
    }

    $insert = $pdo->prepare("INSERT INTO aliskanlik_kayitlari (aliskanlik_id, tarih, deger, tamamlandi) VALUES (?, ?, 0, FALSE)");

    foreach ($alinanIdler as $id) {
        $insert->execute([$id, $tarih]);
    }
}

function tamamlanmaYuzdesi(int $kullaniciId, string $tarih): float
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as toplam,
            SUM(CASE WHEN tamamlandi = TRUE THEN 1 ELSE 0 END) as tamamlanan
        FROM aliskanlik_kayitlari k
        INNER JOIN aliskanliklar a ON a.id = k.aliskanlik_id
        WHERE a.kullanici_id = ? AND k.tarih = ? AND a.tek_seferlik = 0
    ");
    $stmt->execute([$kullaniciId, $tarih]);
    $sonuc = $stmt->fetch();

    if (!$sonuc || $sonuc['toplam'] == 0) {
        return 0.0;
    }

    return round(($sonuc['tamamlanan'] / $sonuc['toplam']) * 100, 1);
}

function aliskanlikKaydiGuncelle(int $alinanId, string $tarih, ?int $deger, bool $tamamlandi): void
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO aliskanlik_kayitlari (aliskanlik_id, tarih, deger, tamamlandi)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            deger = VALUES(deger),
            tamamlandi = VALUES(tamamlandi)
    ");
    $stmt->execute([$alinanId, $tarih, $deger, $tamamlandi]);
}

function istekMetodu(): string
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

function csrfTokenOlustur(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfDogrula(): bool
{
    $headers = getallheaders();
    $token = $headers['X-CSRF-Token'] ?? '';

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function kullaniciProfilGetir(int $kullaniciId): ?array
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT boy, kilo, dogum_tarihi, cinsiyet, aktivite_seviyesi FROM kullanicilar WHERE id = ?");
    $stmt->execute([$kullaniciId]);

    return $stmt->fetch() ?: null;
}

function bmrHesapla(float $kilo, float $boy, int $yas, string $cinsiyet): int
{
    // Mifflin-St Jeor Formülü
    if ($cinsiyet === 'erkek') {
        return (int) ((10 * $kilo) + (6.25 * $boy) - (5 * $yas) + 5);
    }
    return (int) ((10 * $kilo) + (6.25 * $boy) - (5 * $yas) - 161);
}

function aktiviteCarpani(string $seviye): float
{
    return match($seviye) {
        'sedenter' => 1.2,
        'hafif' => 1.375,
        'orta' => 1.55,
        'yuksek' => 1.725,
        'cok_yuksek' => 1.9,
        default => 1.2
    };
}

function gunlukKaloriIhtiyaci(int $kullaniciId): int
{
    global $pdo;

    $profil = kullaniciProfilGetir($kullaniciId);
    if (!$profil || !$profil['boy'] || !$profil['kilo'] || !$profil['dogum_tarihi'] || !$profil['cinsiyet']) {
        return 2000;
    }

    $yas = (int) date('Y') - (int) date('Y', strtotime($profil['dogum_tarihi']));

    $bmr = bmrHesapla(
        (float) $profil['kilo'],
        (float) $profil['boy'],
        $yas,
        $profil['cinsiyet']
    );

    $carpan = aktiviteCarpani($profil['aktivite_seviyesi'] ?? 'sedenter');

    return (int) ($bmr * $carpan);
}

function adimKaloriHesapla(int $adim, float $kilo): int
{
    // Ortalama 0.0005 kalori/adım/kg (~ 10 bin adımda 80 kiloluk biri için 400 kalori)
    return (int) ($adim * 0.0005 * $kilo);
}

function kosuBandiKaloriHesapla(float $kilo, float $hiz, int $egim, int $sure): int
{
    // Yürüme/Koşma formülü
    $hizMMin = $hiz * 16.6667; // km/sa -> m/min
    $egimFraksiyon = $egim / 100.0;
    
    if ($hiz < 6.5) {
        // Yürüme MET değeri
        $vo2 = (0.1 * $hizMMin) + (1.8 * $hizMMin * $egimFraksiyon) + 3.5;
    } else {
        // Koşma MET değeri
        $vo2 = (0.2 * $hizMMin) + (0.9 * $hizMMin * $egimFraksiyon) + 3.5;
    }

    $met = $vo2 / 3.5;
    return (int) ($met * $kilo * ($sure / 60));
}

function bisikletKaloriHesapla(float $kilo, int $sure): int
{
    // Ortalama MET: 5.5 (doğal/hafif orta tempoda bisiklet)
    return (int) (5.5 * $kilo * ($sure / 60));
}

function agirlikKaloriHesapla(float $kilo, int $sure): int
{
    // Ortalama ağırlık antrenmanı (dinlenmeler dahil): 4.0 MET
    // 4.0 MET × kg × saat
    return (int) (4.0 * $kilo * ($sure / 60));
}

// Yeni egzersiz kalori hesaplama fonksiyonları
// Gerçekçi MET değerlerine dayalı katsayılar

function disKosuKaloriHesapla(float $kilo, float $tempo, int $sure): int
{
    // Dışarıda koşu - tempo:dk/km (örn: 5.5 = 5dk30sn/km)
    // Tempoyu km/sa'a çevirelim: tempo dk/km -> km/sa = 60 / tempo
    $hiz = 60 / $tempo; // km/sa
    // Koşu MET değerleri:
    // 8 km/sa = 11.5 MET, 10 km/sa = 14 MET, 12 km/sa = 16 MET
    $met = 8.0 + ($hiz - 8) * 0.8;
    if ($met < 6) $met = 6;
    if ($met > 18) $met = 18;
    return (int) ($met * $kilo * ($sure / 60));
}

function snavKaloriHesapla(float $kilo, int $adet): int
{
    // Şınav - 0.007 kcal/adet/kg
    // 70kg kişi için: 70 × 0.007 = 0.49 kcal/şınav
    return (int) ($adet * $kilo * 0.007);
}

function mekikKaloriHesapla(float $kilo, int $adet): int
{
    // Mekik (crunch) - 0.005 kcal/adet/kg
    // 70kg kişi için: 70 × 0.005 = 0.35 kcal/mekik
    return (int) ($adet * $kilo * 0.005);
}

function barfiksKaloriHesapla(float $kilo, int $adet): int
{
    // Barfiks - Şınavın ~1.5-2 katı zorlayıcı
    // Ortalama 8 MET, 70kg için ~0.7-0.8 kcal/barfiks
    // Katsayı: 0.011 kcal/adet/kg
    return (int) ($adet * $kilo * 0.011);
}

function muscleUpKaloriHesapla(float $kilo, int $adet): int
{
    // Muscle-up - 0.040 kcal/adet/kg
    // 70kg kişi için: 70 × 0.040 = 2.8 kcal/muscle-up
    // Patlayıcı barfiks + transition + dips = çok yüksek enerji harcaması
    return (int) ($adet * $kilo * 0.040);
}

function plankKaloriHesapla(float $kilo, int $sure): int
{
    // Plank - Izometrik core egzersizi
    // MET: ~3.5 (modifikasyonlarla birlikte değişebilir)
    // 70kg kişi, 1 dakika: 3.5 × 70 × 1/60 ≈ 4 kcal
    $met = 3.5;
    return (int) ($met * $kilo * ($sure / 60));
}

function uykuKaloriHesapla(int $kullaniciId, int $saat): int
{
    $profil = kullaniciProfilGetir($kullaniciId);
    if (!$profil || !$profil['kilo']) {
        return 0;
    }

    // Uyku zaten günlük BMR hesabına (24 saatlik baz) dahil edildiği için kalori olarak tekrar eklemiyoruz
    return 0;
}

function gunlukKaloriDengesi(int $kullaniciId, string $tarih): array
{
    global $pdo;

    $alinanKalori = 0;
    $yakilanKalori = 0;
    $profil = kullaniciProfilGetir($kullaniciId);
    
    // Check if requested date is before registration
    $userStmt = $pdo->prepare("SELECT olusturulma_zamani FROM kullanicilar WHERE id = ?");
    $userStmt->execute([$kullaniciId]);
    $kayitTarihi = $userStmt->fetchColumn();
    if ($kayitTarihi && $tarih < date('Y-m-d', strtotime($kayitTarihi))) {
        return [
            'alinan' => 0,
            'yakilan' => 0,
            'bmr' => 0,
            'toplam_yakilan' => 0,
            'fark' => 0,
            'gecersiz' => true
        ];
    }

    $kilo = $profil['kilo'] ?? 70;

    $stmt = $pdo->prepare("
        SELECT k.tip, ak.deger, ak.deger2, ak.deger3, ak.deger4, ak.yakilan_kalori
        FROM aliskanlik_kayitlari ak
        INNER JOIN aliskanliklar k ON k.id = ak.aliskanlik_id
        WHERE k.kullanici_id = ? AND ak.tarih = ?
    ");
    $stmt->execute([$kullaniciId, $tarih]);
    $kayitlar = $stmt->fetchAll();

    foreach ($kayitlar as $kayit) {
        switch ($kayit['tip']) {
            case 'kalori':
                $alinanKalori += (int) $kayit['deger'];
                break;
            case 'adim':
                $yakilanKalori += adimKaloriHesapla((int) $kayit['deger'], (float) $kilo);
                break;
            case 'kosu_bandi':
                // Eğer hiç süre girilmemişse (0 veya NULL), kalori hesaplaması yapılmaz
                $sure = isset($kayit['deger2']) && $kayit['deger2'] > 0 ? (int) $kayit['deger2'] : 0;
                if ($sure > 0) {
                    $yakilanKalori += kosuBandiKaloriHesapla(
                        (float) $kilo,
                        (float) ($kayit['deger3'] ?? 5),
                        (int) ($kayit['deger4'] ?? 0),
                        $sure
                    );
                }
                break;
            case 'bisiklet':
                // Eğer hiç süre girilmemişse, kalori hesaplaması yapılmaz
                $sure = isset($kayit['deger2']) && $kayit['deger2'] > 0 ? (int) $kayit['deger2'] : 0;
                if ($sure > 0) {
                    $yakilanKalori += bisikletKaloriHesapla((float) $kilo, $sure);
                }
                break;
            case 'agirlik':
                // Eğer hiç süre girilmemişse, kalori hesaplaması yapılmaz
                $sure = isset($kayit['deger2']) && $kayit['deger2'] > 0 ? (int) $kayit['deger2'] : 0;
                if ($sure > 0) {
                    $yakilanKalori += agirlikKaloriHesapla((float) $kilo, $sure);
                }
                break;
            case 'uyku':
                $yakilanKalori += uykuKaloriHesapla($kullaniciId, (int) ($kayit['deger'] ?? 0));
                break;
            case 'dis_kosu':
                // deger: tempo (dk/km), deger2: sure (dk)
                $tempo = (float) ($kayit['deger'] ?? 6);
                $sure = (int) ($kayit['deger2'] ?? 0);
                if ($sure > 0 && $tempo > 0) {
                    $yakilanKalori += disKosuKaloriHesapla((float) $kilo, $tempo, $sure);
                }
                break;
            case 'snav':
                // deger: adet
                $adet = (int) ($kayit['deger'] ?? 0);
                if ($adet > 0) {
                    $yakilanKalori += snavKaloriHesapla((float) $kilo, $adet);
                }
                break;
            case 'mekik':
                // deger: adet
                $adet = (int) ($kayit['deger'] ?? 0);
                if ($adet > 0) {
                    $yakilanKalori += mekikKaloriHesapla((float) $kilo, $adet);
                }
                break;
            case 'barfiks':
                // deger: adet
                $adet = (int) ($kayit['deger'] ?? 0);
                if ($adet > 0) {
                    $yakilanKalori += barfiksKaloriHesapla((float) $kilo, $adet);
                }
                break;
            case 'muscle_up':
                // deger: adet
                $adet = (int) ($kayit['deger'] ?? 0);
                if ($adet > 0) {
                    $yakilanKalori += muscleUpKaloriHesapla((float) $kilo, $adet);
                }
                break;
            case 'plank':
                // deger: sure (saniye)
                $sure = (int) ($kayit['deger'] ?? 0);
                if ($sure > 0) {
                    $yakilanKalori += plankKaloriHesapla((float) $kilo, $sure);
                }
                break;
        }
    }

    $bmr = gunlukKaloriIhtiyaci($kullaniciId);
    $toplamYakilan = $bmr + $yakilanKalori;
    $fark = $alinanKalori - $toplamYakilan;

    return [
        'alinan' => $alinanKalori,
        'yakilan' => $yakilanKalori,
        'bmr' => $bmr,
        'toplam_yakilan' => $toplamYakilan,
        'fark' => $fark
    ];
}

function kiloTahminiHesapla(int $kullaniciId, string $baslangicTarihi, string $bitisTarihi): array
{
    global $pdo;

    $profil = kullaniciProfilGetir($kullaniciId);
    $mevcutKilo = $profil['kilo'] ?? 70;

    $stmt = $pdo->prepare("
        SELECT tarih, alinan, toplam_yakilan, fark
        FROM kalori_dengeleri
        WHERE kullanici_id = ? AND tarih BETWEEN ? AND ?
        ORDER BY tarih
    ");

    $toplamFark = 0;
    $gun = (int) $baslangicTarihi;
    $bitis = (int) $bitisTarihi;

    while ($gun <= $bitis) {
        $tarihStr = date('Y-m-d', $gun);
        $denge = gunlukKaloriDengesi($kullaniciId, $tarihStr);
        $toplamFark += $denge['fark'];
        $gun += 86400;
    }

    // 7700 kalori ≈ 1 kg yağ
    $kiloFarki = $toplamFark / 7700;
    $tahminiKilo = $mevcutKilo + $kiloFarki;

    return [
        'baslangic_kilo' => (float) $mevcutKilo,
        'tahmini_kilo' => round($tahminiKilo, 1),
        'fark' => round($kiloFarki, 1),
        'toplam_kalori_farki' => $toplamFark
    ];
}
