<?php

declare(strict_types=1);

// Yapay zeka (yemek fotoğrafı analizi) yapılandırması
// Bu dosyayı config/gemini.php olarak kopyalayıp kendi API anahtarınızı girin.
// Copy this file to config/gemini.php and put your own API key in it.
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');

// Kullanılacak model (görsel ve JSON çıktısı destekleyen bir model)
define('GEMINI_MODEL', 'gemini-3.5-flash');
