<?php

declare(strict_types=1);

require_once '../includes/functions.php';
$aiConfig = __DIR__ . '/../config/gemini.php';
if (!file_exists($aiConfig)) {
    $aiConfig = __DIR__ . '/../config/gemini.example.php';
}
require_once $aiConfig;

// Oturum kontrolü
oturumZorla();

header('Content-Type: application/json; charset=utf-8');

$metod = istekMetodu();

if ($metod !== 'POST') {
    jsonCikti(['hata' => 'Geçersiz istek metodu'], 405);
}

// Gelen veriyi oku
$veri = json_decode(file_get_contents('php://input'), true);
$resimBase64 = $veri['image'] ?? '';
$mimeType = $veri['mimeType'] ?? 'image/jpeg';

if (empty($resimBase64)) {
    jsonCikti(['hata' => 'Resim verisi bulunamadı'], 400);
}

// API Anahtarı kontrolü
if (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE' || empty(GEMINI_API_KEY)) {
    jsonCikti([
        'hata' => 'Gemini API anahtarı yapılandırılmamış. Lütfen config/gemini.php dosyasını düzenleyip geçerli bir API anahtarı girin.'
    ], 400);
}

// Yapay zekaya gönderilecek prompt
$promptText = "Sen bir diyetisyen yapay zekasın. Sana gönderilen yemek fotoğrafındaki yemeği tanımla. Porsiyon büyüklüğünü tahmin et. Toplam kalori (kcal), protein (g), karbonhidrat (g) ve yağ (g) miktarını hesapla. Ayrıca mikro besin olarak şeker (g), sodyum (mg) ve lif (g) değerlerini tahmin et. Yemeğe en uygun tek bir emojiyi (örneğin pizza için 🍕, çorba için 🥣, salata için 🥗, su için 💧 gibi) belirle. Türkiye mutfağını ve yerel yemekleri çok iyi bilirsin. Yanıtı yalnızca ve kesinlikle aşağıdaki JSON formatında ver, başka hiçbir açıklama metni veya markdown tagı ekleme. JSON formatı:\n" .
"{\n" .
"  \"yemek_adi\": \"yemeğin adı\",\n" .
"  \"emoji\": \"yemeğe en uygun tek bir emoji (örn: 🍕, 🥗, 🍏, 🥣)\",\n" .
"  \"porsiyon\": \"tahmini porsiyon miktarı (örn: 1 porsiyon, 200g)\",\n" .
"  \"kalori\": 450,\n" .
"  \"protein\": 15,\n" .
"  \"karbonhidrat\": 45,\n" .
"  \"yag\": 10,\n" .
"  \"seker\": 5,\n" .
"  \"sodyum\": 350,\n" .
"  \"lif\": 3,\n" .
"  \"aciklama\": \"Yemeğin içeriği ve kalori tahmini hakkında kısa açıklama\"\n" .
"}";

// API istek gövdesi
$requestData = [
    "contents" => [
        [
            "parts" => [
                ["text" => $promptText],
                [
                    "inlineData" => [
                        "mimeType" => $mimeType,
                        "data" => $resimBase64
                    ]
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.0
    ]
];

// cURL ile API isteği gönderme
$apiUrl = "https://generativelanguage.googleapis.com/v1/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
// Yerel sunucuda SSL sertifikası hatası olursa diye:
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    jsonCikti(['hata' => 'Gemini API bağlantı hatası: ' . $curlError], 500);
}

if ($httpCode !== 200) {
    $errorData = json_decode($response, true);
    $errorMessage = $errorData['error']['message'] ?? '';
    
    if ($httpCode === 503 || strpos(strtolower($errorMessage), 'high demand') !== false || strpos(strtolower($errorMessage), 'overloaded') !== false) {
        $mesaj = 'Şu anda yapay zeka sunucularında aşırı yoğunluk yaşanıyor. Lütfen tekrar deneyin.';
    } else {
        $mesaj = 'Yapay zeka analiz işlemi sırasında bir hata oluştu (Kod: ' . $httpCode . '). Lütfen tekrar deneyin.';
    }
    
    jsonCikti(['hata' => $mesaj], $httpCode);
}

$responseData = json_decode($response, true);
$rawText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

if (empty($rawText)) {
    jsonCikti(['hata' => 'Yapay zeka yemeği analiz edemedi. Lütfen daha net bir fotoğraf yükleyin.'], 422);
}

// Yanıtı temizle ve parse et (JSON modu açık olsa bile bazen ek tagler içerebilir)
$cleanJson = trim($rawText);
// Markdown kod bloğu temizleme
if (strpos($cleanJson, '```json') === 0) {
    $cleanJson = substr($cleanJson, 7);
    if (substr($cleanJson, -3) === '```') {
        $cleanJson = substr($cleanJson, 0, -3);
    }
} elseif (strpos($cleanJson, '```') === 0) {
    $cleanJson = substr($cleanJson, 3);
    if (substr($cleanJson, -3) === '```') {
        $cleanJson = substr($cleanJson, 0, -3);
    }
}
$cleanJson = trim($cleanJson);

$parsedFood = json_decode($cleanJson, true);

if (!$parsedFood || !isset($parsedFood['kalori'])) {
    jsonCikti([
        'hata' => 'Yapay zeka çıktısı çözümlenemedi. Ham yanıt: ' . $rawText
    ], 500);
}

// Başarılı analiz sonucunu döndür
jsonCikti($parsedFood);
