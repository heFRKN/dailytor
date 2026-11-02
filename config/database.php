<?php

declare(strict_types=1);

$host = 'localhost';
$dbname = 'dailytor';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Yemek kayıtları tablosunu otomatik oluştur
    $pdo->exec("CREATE TABLE IF NOT EXISTS yemek_kayitlari (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kullanici_id INT NOT NULL,
        aliskanlik_id INT NOT NULL,
        tarih DATE NOT NULL,
        yemek_adi VARCHAR(255) NOT NULL,
        emoji VARCHAR(10) DEFAULT '🍽️',
        kalori INT NOT NULL,
        protein INT DEFAULT 0,
        karbonhidrat DECIMAL(5,1) DEFAULT 0.0,
        yag INT DEFAULT 0,
        seker INT DEFAULT 0,
        sodyum INT DEFAULT 0,
        lif INT DEFAULT 0,
        foto_yolu VARCHAR(255) DEFAULT NULL,
        olusturulma_zamani TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (kullanici_id),
        INDEX (aliskanlik_id),
        INDEX (tarih)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Tabloya emoji sütunu ekle (yoksa)
    $stmt = $pdo->query("SHOW COLUMNS FROM yemek_kayitlari LIKE 'emoji'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE yemek_kayitlari ADD COLUMN emoji VARCHAR(10) DEFAULT '🍽️' AFTER yemek_adi");
    }

} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
