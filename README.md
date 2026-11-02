# Dailytor

A self-hosted daily habit, calorie and workout tracker built with PHP and MySQL. Log meals (or snap a photo and let AI estimate the nutrition), track water, sleep, steps and exercises, and see your progress over time. Works as an installable PWA on phones.

[English](#english) · [Türkçe](#türkçe)

---

## English

### Features
- **Habits** — checkbox and numeric habits (water, vitamins, …) with targets, colors, drag-and-drop ordering and one-time activities
- **Calories** — daily BMR (Mifflin-St Jeor) plus calories burned by each activity, compared with what you eat
- **Meals with AI photo analysis** — take a photo of your food; the meal name, portion, calories, protein, carbs, fat, sugar, sodium and fiber are estimated automatically
- **Exercises** — treadmill (speed / incline / time), cycling, weight training, outdoor running (pace), push-ups, sit-ups, pull-ups, muscle-ups, plank, steps and sleep, all with MET-based calorie estimates
- **History** — weekly strip, per-day details, edit past entries, daily notes
- **Analytics** — charts for weight, calories and habits, plus a 1/2/3-week and 1-month weight forecast
- **Profile** — height, weight, birth date, activity level, weight history, password change, delete all data
- Light / dark / automatic theme, mobile-first layout with bottom navigation
- Installable **PWA** with an offline page

### Requirements
- PHP 8.0+ with `pdo_mysql` and `curl`
- MySQL 5.7+ or MariaDB 10.4+
- A web server (Apache / Nginx), e.g. XAMPP, Laragon or any shared hosting
- *(Optional)* a Google Gemini API key for photo-based meal analysis

### Installation
1. Clone the repository into your web root:
   ```bash
   git clone https://github.com/heFRKN/dailytor.git
   ```
   With XAMPP that is `htdocs/dailytor`, so the app opens at `http://localhost/dailytor/`.
2. Create the database: open **phpMyAdmin → Import**, choose [`database/dailytor.sql`](database/dailytor.sql) and run it. Create an empty `dailytor` database first and import into it. The file contains the table structure only — no data.
   Or from the command line:
   ```bash
   mysql -u root -p -e "CREATE DATABASE dailytor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   mysql -u root -p dailytor < database/dailytor.sql
   ```
3. Set your database credentials in [`config/database.php`](config/database.php) (defaults: `localhost`, `root`, empty password).
4. *(Optional, for AI meal analysis)* copy `config/gemini.example.php` to `config/gemini.php` and put your API key in it. `config/gemini.php` is git-ignored, so your key never ends up in the repository.
5. Make sure `img/uploads/` is writable by the web server.
6. Open the site, create an account on the register page and start tracking.

> The app runs either at the domain root (`/`) or under `/dailytor/`. If you use another sub-folder, update the paths in `sw.js` and `manifest.json`.

### Project structure
```
api/            JSON endpoints (auth, habits, records, meals, notes, profile, analytics, detect-food)
config/         database and AI configuration
includes/       shared helpers and calorie formulas
js/             front-end logic (app.js), charts (charts.js)
css/            styles
database/       dailytor.sql — database structure for phpMyAdmin import
img/uploads/    meal photos (not tracked by git)
*.php           pages: login, register, dashboard, history, analytics, profile
sw.js, manifest.json, offline.html   PWA files
```

### Security notes
- Passwords are stored with PHP's `password_hash`.
- Database queries use PDO prepared statements.
- Never commit real API keys or database passwords. Report vulnerabilities privately — see [SECURITY.md](SECURITY.md).

### Disclaimer
Calorie and nutrition values are estimates and are not medical advice.

### License
**PolyForm Noncommercial 1.0.0** — see [LICENSE.md](LICENSE.md).

You are free to read, study, modify and use this project for **non-commercial** purposes (personal use, learning, hobby projects, research). **Any commercial use is not allowed** — including selling it, offering it as a paid service, or using it in a commercial product. For commercial licensing, contact the author via GitHub.

---

## Türkçe

PHP ve MySQL ile yazılmış, kendi sunucunda çalıştırabileceğin günlük alışkanlık, kalori ve antrenman takip uygulaması. Yediklerini kaydet (ya da fotoğrafını çek, besin değerlerini yapay zeka tahmin etsin), su, uyku, adım ve egzersizlerini takip et, ilerlemeni zaman içinde gör. Telefonda uygulama gibi kurulabilen bir PWA'dır.

### Özellikler
- **Alışkanlıklar** — hedefli onay kutusu ve sayısal alışkanlıklar (su, vitamin, …), renkler, sürükle-bırak sıralama, tek seferlik aktiviteler
- **Kalori** — günlük bazal metabolizma (Mifflin-St Jeor) ve her aktivitede yakılan kalori, yediklerinle karşılaştırmalı
- **Yapay zeka ile yemek analizi** — yemeğin fotoğrafını çek; yemek adı, porsiyon, kalori, protein, karbonhidrat, yağ, şeker, sodyum ve lif otomatik tahmin edilir
- **Egzersizler** — koşu bandı (hız / eğim / süre), bisiklet, ağırlık antrenmanı, dışarıda koşu (tempo), şınav, mekik, barfiks, muscle-up, plank, adım ve uyku; hepsi MET tabanlı kalori hesabıyla
- **Geçmiş** — haftalık şerit, gün detayları, geçmiş kayıtları düzenleme, günlük notlar
- **Analiz** — kilo, kalori ve alışkanlık grafikleri; 1/2/3 hafta ve 1 aylık kilo tahmini
- **Profil** — boy, kilo, doğum tarihi, aktivite seviyesi, kilo geçmişi, şifre değiştirme, tüm verileri silme
- Açık / koyu / otomatik tema, alt menülü mobil öncelikli tasarım
- Kurulabilir **PWA** ve çevrimdışı sayfası

### Gereksinimler
- `pdo_mysql` ve `curl` eklentili PHP 8.0+
- MySQL 5.7+ veya MariaDB 10.4+
- Bir web sunucusu (Apache / Nginx): XAMPP, Laragon ya da herhangi bir hosting
- *(İsteğe bağlı)* fotoğrafla yemek analizi için Google Gemini API anahtarı

### Kurulum
1. Depoyu web klasörüne klonla:
   ```bash
   git clone https://github.com/heFRKN/dailytor.git
   ```
   XAMPP'te bu `htdocs/dailytor` klasörüdür; uygulama `http://localhost/dailytor/` adresinde açılır.
2. Veritabanını oluştur: phpMyAdmin'de önce boş bir `dailytor` veritabanı oluştur, sonra **İçe Aktar** bölümünden [`database/dailytor.sql`](database/dailytor.sql) dosyasını seçip çalıştır. Dosyada yalnızca tablo yapısı vardır, içinde veri yoktur.
   Komut satırından:
   ```bash
   mysql -u root -p -e "CREATE DATABASE dailytor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   mysql -u root -p dailytor < database/dailytor.sql
   ```
3. Veritabanı bilgilerini [`config/database.php`](config/database.php) dosyasına gir (varsayılan: `localhost`, `root`, boş şifre).
4. *(İsteğe bağlı, yapay zeka analizi için)* `config/gemini.example.php` dosyasını `config/gemini.php` olarak kopyala ve API anahtarını yaz. `config/gemini.php` git tarafından yok sayılır, anahtarın depoya hiçbir zaman girmez.
5. `img/uploads/` klasörünün web sunucusu tarafından yazılabilir olduğundan emin ol.
6. Siteyi aç, kayıt sayfasından hesap oluştur ve takibe başla.

> Uygulama alan adının kökünde (`/`) ya da `/dailytor/` altında çalışır. Başka bir alt klasör kullanırsan `sw.js` ve `manifest.json` içindeki yolları güncelle.

### Klasör yapısı
```
api/            JSON uç noktaları (auth, habits, records, meals, notes, profile, analytics, detect-food)
config/         veritabanı ve yapay zeka ayarları
includes/       ortak yardımcılar ve kalori formülleri
js/             arayüz mantığı (app.js), grafikler (charts.js)
css/            stiller
database/       dailytor.sql — phpMyAdmin'e aktarılacak veritabanı yapısı
img/uploads/    yemek fotoğrafları (git'e dahil değil)
*.php           sayfalar: giriş, kayıt, panel, geçmiş, analiz, profil
sw.js, manifest.json, offline.html   PWA dosyaları
```

### Güvenlik notları
- Şifreler PHP `password_hash` ile saklanır.
- Veritabanı sorguları PDO hazır ifadeleriyle çalışır.
- Gerçek API anahtarlarını veya veritabanı şifrelerini asla commit etme. Açıkları gizli olarak bildir — bkz. [SECURITY.md](SECURITY.md).

### Uyarı
Kalori ve besin değerleri tahminidir, tıbbi tavsiye yerine geçmez.

### Lisans
**PolyForm Noncommercial 1.0.0** — bkz. [LICENSE.md](LICENSE.md).

Bu projeyi **ticari olmayan** amaçlarla (kişisel kullanım, öğrenme, hobi projeleri, araştırma) inceleyebilir, değiştirebilir ve kullanabilirsin. **Her türlü ticari kullanım yasaktır** — satmak, ücretli hizmet olarak sunmak veya ticari bir üründe kullanmak dahil. Ticari lisans için GitHub üzerinden yazara ulaş.
