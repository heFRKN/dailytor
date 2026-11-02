-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı / Database: `dailytor` (yalnızca yapı / schema only)
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `aliskanliklar`
--

CREATE TABLE `aliskanliklar` (
  `id` int(11) NOT NULL,
  `kullanici_id` int(11) NOT NULL,
  `ad` varchar(100) NOT NULL COMMENT 'Alışkanlık adı',
  `tip` enum('checkbox','numeric','kalori','uyku','adim','kosu_bandi','bisiklet','agirlik','dis_kosu','snav','mekik','barfiks','muscle_up','plank') NOT NULL DEFAULT 'checkbox',
  `hedef_deger` int(11) DEFAULT NULL COMMENT 'Hedef değer',
  `birim` varchar(20) DEFAULT NULL COMMENT 'Birim (adım, ml, dk vb)',
  `renk` varchar(7) DEFAULT '#6366f1' COMMENT 'Renk kodu',
  `siralama` int(11) DEFAULT 0 COMMENT 'Sıralama',
  `kosu_bandi_hedef_hiz` decimal(4,1) DEFAULT NULL COMMENT 'Koşu bandı hedef hız (km/saat)',
  `kosu_bandi_hedef_egim` int(11) DEFAULT NULL COMMENT 'Koşu bandı hedef eğim (%)',
  `kosu_bandi_hedef_sure` int(11) DEFAULT NULL COMMENT 'Koşu bandı hedef süre (dakika)',
  `bisiklet_hedef_sure` int(11) DEFAULT NULL COMMENT 'Bisiklet hedef süre (dakika)',
  `olusturulma_zamani` datetime DEFAULT current_timestamp(),
  `tek_seferlik` tinyint(1) DEFAULT 0 COMMENT 'Tek seferlik aktivite mi? (1=evet, 0=hayır)',
  `plank_hedef_sure` int(11) DEFAULT NULL COMMENT 'Plank hedef süre (saniye)',
  `dis_kosu_hedef_tempo` decimal(4,2) DEFAULT NULL COMMENT 'Dış koşu hedef tempo (dk/km)',
  `hedef_adet` int(11) DEFAULT NULL COMMENT 'Hedef adet (şınav, mekik, barfiks, muscle-up için)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `aliskanlik_kayitlari`
--

CREATE TABLE `aliskanlik_kayitlari` (
  `id` int(11) NOT NULL,
  `aliskanlik_id` int(11) NOT NULL,
  `tarih` date NOT NULL,
  `deger` int(11) DEFAULT NULL COMMENT 'Ana değer (adım, kalori vb)',
  `deger2` int(11) DEFAULT NULL COMMENT 'Ek değer (süre, adım vb)',
  `deger3` decimal(5,1) DEFAULT NULL COMMENT 'Koşu bandı hız (km/sa)',
  `deger4` int(11) DEFAULT NULL COMMENT 'Koşu bandı eğim (%)',
  `tamamlandi` tinyint(1) DEFAULT 0 COMMENT 'Tamamlandı mı?',
  `yakilan_kalori` int(11) DEFAULT NULL COMMENT 'Hesaplanan yakılan kalori',
  `guncelleme_zamani` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `gunluk_notlar`
--

CREATE TABLE `gunluk_notlar` (
  `id` int(11) NOT NULL,
  `kullanici_id` int(11) NOT NULL,
  `tarih` date NOT NULL,
  `not_metni` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kilo_kayitlari`
--

CREATE TABLE `kilo_kayitlari` (
  `id` int(11) NOT NULL,
  `kullanici_id` int(11) NOT NULL,
  `kilo` decimal(5,2) NOT NULL COMMENT 'Kilo (kg)',
  `tarih` date NOT NULL COMMENT 'Tarih',
  `notlar` varchar(255) DEFAULT NULL COMMENT 'Notlar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `boy` decimal(5,2) DEFAULT NULL COMMENT 'Boy (cm)',
  `kilo` decimal(5,2) DEFAULT NULL COMMENT 'Kilo (kg)',
  `dogum_tarihi` date DEFAULT NULL COMMENT 'Doğum tarihi',
  `cinsiyet` enum('erkek','kadin') DEFAULT NULL,
  `aktivite_seviyesi` enum('sedenter','hafif','orta','yuksek','cok_yuksek') DEFAULT 'sedenter' COMMENT 'Aktivite seviyesi',
  `olusturulma_zamani` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yemek_kayitlari`
--

CREATE TABLE `yemek_kayitlari` (
  `id` int(11) NOT NULL,
  `kullanici_id` int(11) NOT NULL,
  `aliskanlik_id` int(11) NOT NULL,
  `tarih` date NOT NULL,
  `yemek_adi` varchar(255) NOT NULL,
  `emoji` varchar(10) DEFAULT '🍽️',
  `kalori` int(11) NOT NULL,
  `protein` int(11) DEFAULT 0,
  `karbonhidrat` decimal(5,1) DEFAULT 0.0,
  `yag` int(11) DEFAULT 0,
  `seker` int(11) DEFAULT 0,
  `sodyum` int(11) DEFAULT 0,
  `lif` int(11) DEFAULT 0,
  `foto_yolu` varchar(255) DEFAULT NULL,
  `olusturulma_zamani` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `aliskanliklar`
--
ALTER TABLE `aliskanliklar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kullanici` (`kullanici_id`);

--
-- Tablo için indeksler `aliskanlik_kayitlari`
--
ALTER TABLE `aliskanlik_kayitlari`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tarih` (`aliskanlik_id`,`tarih`),
  ADD KEY `idx_tarih` (`tarih`),
  ADD KEY `idx_aliskanlik_tarih` (`aliskanlik_id`,`tarih`);

--
-- Tablo için indeksler `gunluk_notlar`
--
ALTER TABLE `gunluk_notlar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_not` (`kullanici_id`,`tarih`);

--
-- Tablo için indeksler `kilo_kayitlari`
--
ALTER TABLE `kilo_kayitlari`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tarih` (`kullanici_id`,`tarih`),
  ADD KEY `idx_tarih` (`tarih`),
  ADD KEY `idx_kullanici` (`kullanici_id`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`);

--
-- Tablo için indeksler `yemek_kayitlari`
--
ALTER TABLE `yemek_kayitlari`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kullanici_id` (`kullanici_id`),
  ADD KEY `aliskanlik_id` (`aliskanlik_id`),
  ADD KEY `tarih` (`tarih`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `aliskanliklar`
--
ALTER TABLE `aliskanliklar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `aliskanlik_kayitlari`
--
ALTER TABLE `aliskanlik_kayitlari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `gunluk_notlar`
--
ALTER TABLE `gunluk_notlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `kilo_kayitlari`
--
ALTER TABLE `kilo_kayitlari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `yemek_kayitlari`
--
ALTER TABLE `yemek_kayitlari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `aliskanliklar`
--
ALTER TABLE `aliskanliklar`
  ADD CONSTRAINT `aliskanliklar_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `aliskanlik_kayitlari`
--
ALTER TABLE `aliskanlik_kayitlari`
  ADD CONSTRAINT `aliskanlik_kayitlari_ibfk_1` FOREIGN KEY (`aliskanlik_id`) REFERENCES `aliskanliklar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `gunluk_notlar`
--
ALTER TABLE `gunluk_notlar`
  ADD CONSTRAINT `gunluk_notlar_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `kilo_kayitlari`
--
ALTER TABLE `kilo_kayitlari`
  ADD CONSTRAINT `kilo_kayitlari_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
