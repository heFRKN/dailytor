<?php

declare(strict_types=1);

require_once '../includes/functions.php';

oturumZorla();

header('Content-Type: application/json; charset=utf-8');

global $pdo;
$kullaniciId = $_SESSION['kullanici_id'];
$metod = istekMetodu();

if ($metod === 'POST') {
    // Yeni yemek ekleme
    $veri = json_decode(file_get_contents('php://input'), true);
    
    $aliskanlikId = (int) ($veri['aliskanlik_id'] ?? 0);
    $tarih = $veri['tarih'] ?? date('Y-m-d');
    $yemekAdi = trim($veri['yemek_adi'] ?? '');
    $kalori = (int) ($veri['kalori'] ?? 0);
    
    // Opsiyonel makro ve mikrolar
    $protein = isset($veri['protein']) ? (int) $veri['protein'] : 0;
    $karbonhidrat = isset($veri['karbonhidrat']) ? (float) $veri['karbonhidrat'] : 0.0;
    $yag = isset($veri['yag']) ? (int) $veri['yag'] : 0;
    $seker = isset($veri['seker']) ? (int) $veri['seker'] : 0;
    $sodyum = isset($veri['sodyum']) ? (int) $veri['sodyum'] : 0;
    $lif = isset($veri['lif']) ? (int) $veri['lif'] : 0;
    $emoji = trim($veri['emoji'] ?? '🍽️');
    if (empty($emoji)) {
        $emoji = '🍽️';
    }

    if (!$aliskanlikId || empty($yemekAdi) || $kalori <= 0) {
        jsonCikti(['hata' => 'Alışkanlık ID, Yemek Adı ve Kalori (sıfırdan büyük) değerleri zorunludur.'], 400);
    }

    // Alışkanlığın kullanıcıya ait olduğunu doğrula
    $kontrol = $pdo->prepare("SELECT id FROM aliskanliklar WHERE id = ? AND kullanici_id = ? AND tip = 'kalori'");
    $kontrol->execute([$aliskanlikId, $kullaniciId]);
    if (!$kontrol->fetch()) {
        jsonCikti(['hata' => 'Geçersiz kalori alışkanlığı'], 403);
    }

    $fotoYolu = null;

    // Yemek kaydını ekle
    $stmt = $pdo->prepare("
        INSERT INTO yemek_kayitlari 
        (kullanici_id, aliskanlik_id, tarih, yemek_adi, emoji, kalori, protein, karbonhidrat, yag, seker, sodyum, lif, foto_yolu)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $kullaniciId, $aliskanlikId, $tarih, $yemekAdi, $emoji, $kalori, 
        $protein, $karbonhidrat, $yag, $seker, $sodyum, $lif, $fotoYolu
    ]);
    
    $yemekId = (int) $pdo->lastInsertId();

    // Günün toplam kalori ve makro değerlerini hesapla
    $toplamStmt = $pdo->prepare("
        SELECT 
            SUM(kalori) as toplam_kalori,
            SUM(protein) as toplam_protein,
            SUM(karbonhidrat) as toplam_karbonhidrat,
            SUM(yag) as toplam_yag
        FROM yemek_kayitlari
        WHERE aliskanlik_id = ? AND tarih = ?
    ");
    $toplamStmt->execute([$aliskanlikId, $tarih]);
    $toplamlar = $toplamStmt->fetch();

    $totalCal = (int) ($toplamlar['toplam_kalori'] ?? 0);
    $totalProt = (int) ($toplamlar['toplam_protein'] ?? 0);
    $totalCarb = (float) ($toplamlar['toplam_karbonhidrat'] ?? 0.0);
    $totalFat = (int) ($toplamlar['toplam_yag'] ?? 0);

    // Ana aliskanlik_kayitlari tablosunu güncelle
    $updateStmt = $pdo->prepare("
        INSERT INTO aliskanlik_kayitlari (aliskanlik_id, tarih, deger, deger2, deger3, deger4, tamamlandi, yakilan_kalori)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            deger = VALUES(deger),
            deger2 = VALUES(deger2),
            deger3 = VALUES(deger3),
            deger4 = VALUES(deger4),
            tamamlandi = VALUES(tamamlandi)
    ");
    $updateStmt->execute([$aliskanlikId, $tarih, $totalCal, $totalProt, $totalCarb, $totalFat, $totalCal > 0, 0]);

    // Yenilenen yemeği geri dön
    $yemekStmt = $pdo->prepare("SELECT * FROM yemek_kayitlari WHERE id = ?");
    $yemekStmt->execute([$yemekId]);
    $yeniYemek = $yemekStmt->fetch();

    jsonCikti([
        'mesaj' => 'Yemek başarıyla eklendi',
        'yemek' => $yeniYemek,
        'tamamlanma_yuzdesi' => tamamlanmaYuzdesi($kullaniciId, $tarih),
        'kalori_dengesi' => gunlukKaloriDengesi($kullaniciId, $tarih)
    ]);
}

if ($metod === 'DELETE') {
    // Yemek silme
    $id = (int) ($_GET['id'] ?? 0);

    if (!$id) {
        jsonCikti(['hata' => 'Yemek ID belirtilmelidir.'], 400);
    }

    // Yemek kaydını ve kullanıcı kontrolünü gerçekleştir
    $stmt = $pdo->prepare("SELECT kullanici_id, aliskanlik_id, tarih, foto_yolu FROM yemek_kayitlari WHERE id = ?");
    $stmt->execute([$id]);
    $yemek = $stmt->fetch();

    if (!$yemek) {
        jsonCikti(['hata' => 'Yemek kaydı bulunamadı.'], 404);
    }

    if ((int) $yemek['kullanici_id'] !== $kullaniciId) {
        jsonCikti(['hata' => 'Bu işlemi yapmaya yetkiniz yok.'], 403);
    }

    // Fotoğraf dosyasını sunucudan sil
    if (!empty($yemek['foto_yolu']) && file_exists('../' . $yemek['foto_yolu'])) {
        unlink('../' . $yemek['foto_yolu']);
    }

    // Yemek kaydını sil
    $deleteStmt = $pdo->prepare("DELETE FROM yemek_kayitlari WHERE id = ?");
    $deleteStmt->execute([$id]);

    $aliskanlikId = (int) $yemek['aliskanlik_id'];
    $tarih = $yemek['tarih'];

    // Günün toplam değerlerini yeniden hesapla
    $toplamStmt = $pdo->prepare("
        SELECT 
            SUM(kalori) as toplam_kalori,
            SUM(protein) as toplam_protein,
            SUM(karbonhidrat) as toplam_karbonhidrat,
            SUM(yag) as toplam_yag
        FROM yemek_kayitlari
        WHERE aliskanlik_id = ? AND tarih = ?
    ");
    $toplamStmt->execute([$aliskanlikId, $tarih]);
    $toplamlar = $toplamStmt->fetch();

    $totalCal = (int) ($toplamlar['toplam_kalori'] ?? 0);
    $totalProt = (int) ($toplamlar['toplam_protein'] ?? 0);
    $totalCarb = (float) ($toplamlar['toplam_karbonhidrat'] ?? 0.0);
    $totalFat = (int) ($toplamlar['toplam_yag'] ?? 0);

    // Ana aliskanlik_kayitlari tablosunu güncelle
    $updateStmt = $pdo->prepare("
        INSERT INTO aliskanlik_kayitlari (aliskanlik_id, tarih, deger, deger2, deger3, deger4, tamamlandi, yakilan_kalori)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            deger = VALUES(deger),
            deger2 = VALUES(deger2),
            deger3 = VALUES(deger3),
            deger4 = VALUES(deger4),
            tamamlandi = VALUES(tamamlandi)
    ");
    $updateStmt->execute([$aliskanlikId, $tarih, $totalCal, $totalProt, $totalCarb, $totalFat, $totalCal > 0, 0]);

    jsonCikti([
        'mesaj' => 'Yemek başarıyla silindi',
        'tamamlanma_yuzdesi' => tamamlanmaYuzdesi($kullaniciId, $tarih),
        'kalori_dengesi' => gunlukKaloriDengesi($kullaniciId, $tarih)
    ]);
}

jsonCikti(['hata' => 'Geçersiz metod'], 405);
