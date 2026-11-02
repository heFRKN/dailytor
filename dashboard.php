<?php
require_once 'includes/functions.php';

oturumZorla();

$kullanici = kullaniciGetir();
?>
<!DOCTYPE html>
<html lang="tr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dailytor - Dashboard</title>
    <link rel="icon" type="image/x-icon" href="img/dailytor.ico">
    <script>
        // Prevent theme flicker - set theme immediately
        (function() {
            const theme = localStorage.getItem('theme') ||
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="stylesheet" href="css/style.css?v=8">
    <style>
        /* Compact Hero - History Style */
        .calorie-balance {
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            border-radius: 20px;
            padding: 24px 32px;
            color: white;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.3);
        }
        .hero-left {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .hero-date-section {
            color: white;
        }
        .hero-date-section .date-large {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .hero-date-section .date-small {
            font-size: 13px;
            opacity: 0.8;
        }
        .hero-progress-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .hero-progress-circle {
            position: relative;
            width: 80px;
            height: 80px;
        }
        .hero-progress-circle svg {
            transform: rotate(-90deg);
        }
        .hero-progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 22px;
            font-weight: 700;
        }
        .hero-stats {
            display: flex;
            gap: 24px;
            color: white;
        }
        .hero-stat {
            text-align: center;
        }
        .hero-stat-value {
            font-size: 24px;
            font-weight: 700;
        }
        .hero-stat-label {
            font-size: 11px;
            text-transform: uppercase;
            opacity: 0.7;
            margin-top: 2px;
        }
        .hero-actions {
            display: flex;
            gap: 8px;
        }
        .hero-actions .btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            padding: 10px 16px;
        }
        .hero-actions .btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .calorie-balance.negative {
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            box-shadow: 0 10px 30px -10px rgba(239, 68, 68, 0.3);
        }
        .calorie-balance.positive {
            background: linear-gradient(135deg, #10b981 0%, #22c55e 100%);
            box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.3);
        }
        .modal-content {
            max-width: 650px;
            max-height: 85vh;
            overflow-y: auto;
        }
        /* Remove old unused styles */
        .habit-type-grid-compact {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
            gap: 8px;
        }
        .habit-type-card-mini {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 6px;
            border: 2px solid var(--border);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--bg-primary);
        }
        .habit-type-card-mini:hover {
            border-color: var(--primary);
            background: var(--bg-tertiary);
        }
        .habit-type-card-mini.selected {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.15);
        }
        .habit-type-card-mini svg {
            color: var(--text-secondary);
        }
        .habit-type-card-mini.selected svg {
            color: var(--primary);
        }
        .habit-type-card-mini span {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-align: center;
        }
        .type-fields {
            margin-bottom: 16px;
        }
        .field-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        @media (max-width: 480px) {
            .field-row-2 {
                grid-template-columns: 1fr;
            }
        }
        /* Mobile responsive - hero stack */
        @media (max-width: 767px) {
            .calorie-balance {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
                padding: 20px;
            }
            .hero-left {
                width: 100%;
                justify-content: space-between;
            }
            .hero-stats {
                width: 100%;
                justify-content: space-between;
                gap: 16px;
            }
            .hero-actions {
                width: 100%;
                position: relative;
                z-index: 10;
            }
            .hero-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Mobile responsive */
        @media (min-width: 600px) {
            .habit-type-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        @media (min-width: 768px) {
            .calorie-balance {
                flex-wrap: nowrap;
                padding: 24px 32px;
            }
            .hero-stats {
                gap: 24px;
            }
            .hero-progress-circle {
                width: 80px;
                height: 80px;
            }
            .hero-progress-text {
                font-size: 22px;
            }
            .hero-stat-value {
                font-size: 24px;
            }
        }
        @media (min-width: 1024px) {
            .modal-content {
                max-width: 650px;
            }
        }
        
        .new-habit-modal-content {
            width: 100%;
            max-height: 85vh;
            border-radius: 24px 24px 0 0;
            margin-top: auto;
        }
        @media (min-width: 768px) {
            .new-habit-modal-content {
                width: 90%;
                max-height: 80vh;
                border-radius: 24px;
                margin: auto;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <svg width="28" height="28" viewBox="0 0 48 48" fill="none" style="flex-shrink: 0;">
                    <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="3" stroke-dasharray="8 4"/>
                    <circle cx="24" cy="24" r="12" fill="currentColor"/>
                </svg>
                <span>Dailytor</span>
            </div>

            <div class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="history.php">Geçmiş</a>
                <a href="analytics.php">Analiz</a>
                <a href="profil.php">Profil</a>
            </div>

            <div class="nav-actions">
                <a href="profil.php" class="nav-username"><?= htmlspecialchars($kullanici['kullanici_adi']) ?></a>
                <button id="themeToggle" class="theme-toggle">
                    <svg class="sun-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                    <svg class="moon-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                </button>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div id="calorieBalance" class="calorie-balance">
            <!-- Left: Date & Progress -->
            <div class="hero-left">
                <div class="hero-date-section">
                    <div class="date-large" id="currentDate">-- -- ----</div>
                    <div class="date-small" id="currentDayName">--------</div>
                </div>

                <div class="hero-progress-section">
                    <div class="hero-progress-circle">
                        <svg width="80" height="80" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="36" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="6"/>
                            <circle id="progressRing" cx="40" cy="40" r="36" fill="none" stroke="#ffffff" stroke-width="6" stroke-linecap="round" stroke-dasharray="226.2" stroke-dashoffset="226.2" style="transition: stroke-dashoffset 1.5s cubic-bezier(0.1, 0.7, 0.1, 1);"/>
                        </svg>
                        <div class="hero-progress-text">
                            <span id="progressPercent">0</span>%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center: Stats -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-value" id="completedCount">0</div>
                    <div class="hero-stat-label">Tamamlanan</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value" id="calorieValue">--</div>
                    <div class="hero-stat-label">Kalori Dengesi</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value" id="caloriesExercise">--</div>
                    <div class="hero-stat-label">Egzersiz</div>
                </div>
            </div>

            <!-- Right: Actions -->
            <div class="hero-actions">
                <button id="addHabitBtn" class="btn" onclick="if(window.openNewHabitModal) window.openNewHabitModal();">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
                    </svg>
                    Yeni Alışkanlık
                </button>
            </div>
        </div>

        <div id="fixedFoodSection" class="fixed-food-section">
            <!-- Dinamik olarak js/app.js üzerinden doldurulacak -->
        </div>

        <div id="habitsContainer" class="habits-container">
            <div class="loading-spinner"></div>
        </div>

        <!-- Günlük Not Bölümü -->
        <div class="daily-note-section" style="margin-top: var(--space-xl);">
            <div class="stat-card-full" style="background: var(--bg-secondary); border: 1px solid var(--border); padding: var(--space-lg); border-radius: var(--radius);">
                <div class="stat-header" style="margin-bottom: var(--space-md); display: flex; align-items: center; gap: var(--space-sm);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span class="stat-label" style="font-weight: 600; font-size: 15px;">Bugünkü Notun</span>
                </div>
                <textarea id="dailyNoteInput" placeholder="Bugün neler başardın? Nasıl hissediyorsun?"
                    style="width: 100%; min-height: 120px; background: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: var(--space-md); font-family: inherit; font-size: 16px; resize: vertical; outline: none; transition: border-color 0.2s;"
                    onblur="saveDailyNote()"></textarea>
                <div style="margin-top: var(--space-sm); font-size: 11px; color: var(--text-secondary); text-align: right;">
                    * Otomatik kaydedilir (çıkınca/başka yere tıklayınca)
                </div>
            </div>
        </div>

        <div id="emptyState" class="empty-state" style="display: none;">
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                <circle cx="32" cy="32" r="24" stroke="var(--text-secondary)" stroke-width="2" stroke-dasharray="4 4"/>
                <circle cx="32" cy="32" r="8" fill="var(--text-secondary)"/>
            </svg>
            <h3>Henüz alışkanlık eklemedin</h3>
            <p>Günlük rutinini oluşturmak için yeni bir alışkanlık ekle.</p>
            <button id="addFirstHabitBtn" class="btn btn-primary" onclick="if(window.openNewHabitModal) window.openNewHabitModal();">İlk Alışkanlığı Ekle</button>
        </div>
    </main>

    <!-- Yeni Alışkanlık Modal - Modern Responsive -->
    <div id="habitModal" class="modal">
        <div class="modal-content new-habit-modal-content" style="max-width: 500px; display: flex; flex-direction: column; overflow: hidden;">
            <!-- Header - Sticky -->
            <div class="modal-header" style="flex-shrink: 0; padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                <h2 style="font-size: 18px; margin: 0;" id="modalTitle">Yeni Alışkanlık</h2>
                <button class="modal-close" id="closeModalBtn" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--bg-tertiary); border: none; font-size: 20px; cursor: pointer;">&times;</button>
            </div>

            <!-- Steps Indicator -->
            <div id="modalProgressBar" style="flex-shrink: 0; display: flex; gap: 6px; padding: 12px 20px 8px;">
                <div class="progress-dot active" data-step="1" style="flex: 1; height: 4px; border-radius: 2px; background: var(--primary); transition: opacity 0.3s;"></div>
                <div class="progress-dot" data-step="2" style="flex: 1; height: 4px; border-radius: 2px; background: var(--border); transition: opacity 0.3s;"></div>
            </div>

            <form id="habitForm" style="flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden; padding: 16px 20px 20px;">
                <input type="hidden" id="editHabitId" name="edit_id" value="">
                <input type="hidden" id="selectedType" name="tip" value="">

                <!-- Step 1: Tip Seçimi -->
                <div id="step1" class="modal-step">
                    <p style="color: var(--text-secondary); margin-bottom: 12px; font-size: 14px;">Ne tür bir alışkanlık eklemek istersin?</p>

                    <!-- Kategori Filtreleri -->
                    <div id="categoryFilters" style="display: flex; gap: 6px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 16px; scrollbar-width: none; -ms-overflow-style: none;">
                        <button class="filter-btn active" data-category="all" style="flex-shrink: 0; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid var(--primary); background: var(--primary); color: white;">Tümü</button>
                        <button class="filter-btn" data-category="basit" style="flex-shrink: 0; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-secondary);">Basit</button>
                        <button class="filter-btn" data-category="kardiyo" style="flex-shrink: 0; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-secondary);">Kardiyo</button>
                        <button class="filter-btn" data-category="guc" style="flex-shrink: 0; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-secondary);">Güç</button>
                    </div>

                    <!-- Arama -->
                    <div class="form-group" style="margin-bottom: 12px;">
                        <input type="text" id="habitSearchInput" placeholder="🔍 Ara... (koşu, şınav, bisiklet)"
                            style="width: 100%; padding: 14px 16px; border: 2px solid var(--border); border-radius: 12px; background: var(--bg-primary); color: var(--text-primary); font-size: 15px;">
                    </div>

                    <!-- Tip Listesi - Grid Layout -->
                    <div id="habitTypeList" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                        <!-- JS ile doldurulacak -->
                    </div>
                </div>

                <!-- Step 2: İsim + Ayarlar -->
                <div id="step2" class="modal-step" style="display: none;">
                    <p style="color: var(--text-secondary); margin-bottom: 12px; font-size: 14px;">Şimdi detayları gir</p>

                    <!-- Seçili Tip Bilgisi -->
                    <div id="selectedTypeInfo" style="display: flex; align-items: center; gap: 12px; padding: 14px; background: var(--bg-tertiary); border-radius: 12px; margin-bottom: 16px;">
                        <span id="selectedTypeIcon" style="font-size: 28px;">💪</span>
                        <div>
                            <div id="selectedTypeName" style="font-weight: 600; font-size: 15px; color: var(--text-primary);">Şınav</div>
                            <div id="selectedTypeDesc" style="font-size: 12px; color: var(--text-secondary);">Adet bazlı</div>
                        </div>
                        <button type="button" id="changeTypeBtn" style="margin-left: auto; padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-primary); font-size: 12px; cursor: pointer;">Değiştir</button>
                    </div>

                    <!-- İsim Girişi -->
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Alışkanlık Adı</label>
                        <input type="text" id="habitNameInput" name="ad" placeholder="Örn: Sabah koşusu, Su içmek, Kitap oku"
                            style="width: 100%; font-size: 16px; padding: 14px; border: 2px solid var(--border); border-radius: 12px; background: var(--bg-primary); color: var(--text-primary);">
                    </div>

                    <!-- Dinamik Ayarlar -->
                    <div id="dynamicSettings">
                        <!-- JS ile doldurulacak -->
                    </div>

                    <!-- Renk Seçimi -->
                    <div class="form-group" style="margin-top: 16px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Renk</label>
                        <div id="colorOptions" style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button type="button" class="color-btn active" data-color="#6366f1" style="width: 38px; height: 38px; border-radius: 50%; background: #6366f1; cursor: pointer;" title="İndigo"></button>
                            <button type="button" class="color-btn" data-color="#8b5cf6" style="width: 38px; height: 38px; border-radius: 50%; background: #8b5cf6; cursor: pointer;" title="Mor"></button>
                            <button type="button" class="color-btn" data-color="#ec4899" style="width: 38px; height: 38px; border-radius: 50%; background: #ec4899; cursor: pointer;" title="Pembe"></button>
                            <button type="button" class="color-btn" data-color="#10b981" style="width: 38px; height: 38px; border-radius: 50%; background: #10b981; cursor: pointer;" title="Yeşil"></button>
                            <button type="button" class="color-btn" data-color="#f59e0b" style="width: 38px; height: 38px; border-radius: 50%; background: #f59e0b; cursor: pointer;" title="Turuncu"></button>
                            <button type="button" class="color-btn" data-color="#ef4444" style="width: 38px; height: 38px; border-radius: 50%; background: #ef4444; cursor: pointer;" title="Kırmızı"></button>
                            <button type="button" class="color-btn" data-color="#3b82f6" style="width: 38px; height: 38px; border-radius: 50%; background: #3b82f6; cursor: pointer;" title="Mavi"></button>
                            <button type="button" class="color-btn" data-color="#64748b" style="width: 38px; height: 38px; border-radius: 50%; background: #64748b; cursor: pointer;" title="Gri"></button>
                            <button type="button" class="color-btn" data-color="#14b8a6" style="width: 38px; height: 38px; border-radius: 50%; background: #14b8a6; cursor: pointer;" title="Turkuaz"></button>
                            <button type="button" class="color-btn" data-color="#f97316" style="width: 38px; height: 38px; border-radius: 50%; background: #f97316; cursor: pointer;" title="Koyu Turuncu"></button>
                        </div>
                        <input type="hidden" id="selectedColor" name="renk" value="#6366f1">
                    </div>

                    <!-- Tek Seferlik -->
                    <div style="margin-top: 16px; padding: 14px; background: var(--bg-tertiary); border-radius: 12px;">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="tek_seferlik" id="tekSeferlikCheck" style="width: 20px; height: 20px; accent-color: var(--primary);">
                            <div>
                                <div style="font-weight: 600; font-size: 14px;">Tek Seferlik</div>
                                <div style="font-size: 12px; color: var(--text-secondary);">Sadece bugün için, tekrar etme</div>
                            </div>
                        </label>
                    </div>
                </div>
            </form>

            <!-- Sticky Footer Buttons -->
            <div id="modalFooter" style="flex-shrink: 0; padding: 12px 20px calc(32px + env(safe-area-inset-bottom, 0px)); border-top: 1px solid var(--border); display: none;">
                <div style="display: flex; gap: 10px;">
                    <button type="button" id="backToStep1" class="btn" style="flex: 1; padding: 14px; border-radius: 12px; font-weight: 600;">← Geri</button>
                    <button type="submit" form="habitForm" id="submitHabitBtn" class="btn btn-primary" style="flex: 2; padding: 14px; border-radius: 12px; font-weight: 600;">Oluştur ✓</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tip Listesi Verisi -->
    <script>
        const HABIT_TYPES = [
            // Basit
            { type: 'checkbox', name: 'Onay Kutusu', icon: '✅', category: 'basit', desc: 'Yapıldı / Yapılmadı' },
            { type: 'numeric', name: 'Sayısal', icon: '🔢', category: 'basit', desc: 'Hedef sayı gir' },
            { type: 'kalori', name: 'Kalori', icon: '🔥', category: 'basit', desc: 'Kalori takibi' },
            { type: 'uyku', name: 'Uyku', icon: '😴', category: 'basit', desc: 'Uyku süresi' },
            { type: 'adim', name: 'Adım', icon: '👟', category: 'basit', desc: 'Günlük adım' },
            // Kardiyo
            { type: 'kosu_bandi', name: 'Koşu Bandı', icon: '🏃', category: 'kardiyo', desc: 'Tempo + süre' },
            { type: 'dis_kosu', name: 'Dış Koşu', icon: '🌳', category: 'kardiyo', desc: 'Tempo + süre' },
            { type: 'bisiklet', name: 'Bisiklet', icon: '🚴', category: 'kardiyo', desc: 'Süre bazlı' },
            // Güç
            { type: 'snav', name: 'Şınav', icon: '💪', category: 'guc', desc: 'Adet say' },
            { type: 'mekik', name: 'Mekik', icon: '🔷', category: 'guc', desc: 'Adet say' },
            { type: 'barfiks', name: 'Barfiks', icon: '📍', category: 'guc', desc: 'Adet say' },
            { type: 'muscle_up', name: 'Muscle Up', icon: '⭐', category: 'guc', desc: 'Adet say' },
            { type: 'plank', name: 'Plank', icon: '🧱', category: 'guc', desc: 'Süre say' },
            { type: 'agirlik', name: 'Ağırlık', icon: '🏋️', category: 'guc', desc: 'Süre bazlı' },
        ];

        const TYPE_SETTINGS = {
            'checkbox': { html: `
                <p style="color: var(--text-secondary); text-align: center; padding: 10px;">Bu alışkanlık için sadece yapılıp yapılmadığını işaretleyeceksin.</p>
            ` },
            'numeric': { html: `
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Hedef Değer</label>
                    <input type="number" id="settingHedef" placeholder="10" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                </div>
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Birim</label>
                    <input type="text" id="settingBirim" placeholder="Örn: bardak, litre, sayfa" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                </div>
            `},
            'kalori': { html: `
                <p style="color: var(--text-secondary); text-align: center; padding: 10px;">Günlük kalori takibi yapacaksın. Yediğin kalorileri gireceksin.</p>
            ` },
            'uyku': { html: `
                <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Hedef Uyku Süresi</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <input type="number" id="settingSaat" min="0" max="24" value="8" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px; text-align: center;">
                        <div style="text-align: center; font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Saat</div>
                    </div>
                    <div>
                        <input type="number" id="settingDakika" min="0" max="59" value="0" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px; text-align: center;">
                        <div style="text-align: center; font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Dakika</div>
                    </div>
                </div>
            `},
            'adim': { html: `
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Hedef Adım Sayısı</label>
                    <input type="number" id="settingAdimHedef" value="10000" step="100" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                </div>
            `},
            'kosu_bandi': { html: `
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px; display: block; text-transform: uppercase;">Hız (km/s)</label>
                        <input type="number" id="settingKosuHiz" value="6" step="0.1" min="1" max="25" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px; display: block; text-transform: uppercase;">Eğim (%)</label>
                        <input type="number" id="settingKosuEgim" value="0" min="0" max="15" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px; display: block; text-transform: uppercase;">Süre (dk)</label>
                        <input type="number" id="settingKosuSure" value="30" min="1" max="180" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                    </div>
                </div>
            `},
            'dis_kosu': { html: `
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Tempo Seviyesi</label>
                    <div id="discoTempoOptions" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                        <button type="button" class="disco-tempo-btn" data-value="yavas" style="padding: 12px; border: 2px solid var(--border); border-radius: 10px; background: var(--bg-primary); cursor: pointer;">
                            <div style="font-size: 20px;">🚶</div>
                            <div style="font-size: 13px; font-weight: 600;">Yavaş</div>
                            <div style="font-size: 10px;">8+ dk/km</div>
                        </button>
                        <button type="button" class="disco-tempo-btn active" data-value="orta" style="padding: 12px; border: 2px solid var(--border); border-radius: 10px; background: var(--bg-primary); cursor: pointer;">
                            <div style="font-size: 20px;">🏃</div>
                            <div style="font-size: 13px; font-weight: 600;">Orta</div>
                            <div style="font-size: 10px;">6-7 dk/km</div>
                        </button>
                        <button type="button" class="disco-tempo-btn" data-value="hizli" style="padding: 12px; border: 2px solid var(--border); border-radius: 10px; background: var(--bg-primary); cursor: pointer;">
                            <div style="font-size: 20px;">⚡</div>
                            <div style="font-size: 13px; font-weight: 600;">Hızlı</div>
                            <div style="font-size: 10px;">5- dk/km</div>
                        </button>
                    </div>
                    <input type="hidden" id="settingDiscoTempo" value="orta">
                </div>
            `},
            'bisiklet': { html: `<p style="color: var(--text-secondary); text-align: center; padding: 10px;">Bisiklet süresini dakika olarak gireceksin. Kalori otomatik hesaplanacak.</p>` },
            'agirlik': { html: `<p style="color: var(--text-secondary); text-align: center; padding: 10px;">Ağırlık antrenmanı süresini dakika olarak gireceksin. Kalori otomatik hesaplanacak.</p>` },
            'snav': { html: `
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Hedef Adet</label>
                    <input type="number" id="settingAdet" value="20" min="1" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                </div>
            `},
            'mekik': { html: `
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Hedef Adet</label>
                    <input type="number" id="settingAdet" value="30" min="1" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                </div>
            `},
            'barfiks': { html: `
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Hedef Adet</label>
                    <input type="number" id="settingAdet" value="10" min="1" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                </div>
            `},
            'muscle_up': { html: `
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Hedef Adet</label>
                    <input type="number" id="settingAdet" value="5" min="1" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px;">
                </div>
            `},
            'plank': { html: `
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Hedef Süre</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <input type="number" id="settingPlankDakika" value="1" min="0" max="60" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px; text-align: center;">
                            <div style="text-align: center; font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Dakika</div>
                        </div>
                        <div>
                            <input type="number" id="settingPlankSaniye" value="0" min="0" max="59" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px; text-align: center;">
                            <div style="text-align: center; font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Saniye</div>
                        </div>
                    </div>
                </div>
            `},
        };

        const TEMPO_DEGERLERI = {
            'yavas': 8.5,   // 8.5 dk/km
            'orta': 6.5,    // 6.5 dk/km
            'hizli': 5.0    // 5.0 dk/km
        };

        const TEMPO_MET_DEGERLERI = {
            'yavas': 7,     // Yavaş koşu MET
            'orta': 9.5,    // Orta tempo koşu MET
            'hizli': 12     // Hızlı koşu MET
        };
    </script>

    <div id="toast"></div>

    <!-- Mobile Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="bottom-nav-item active" data-page="dashboard">
            <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span class="bottom-nav-label">Ana Sayfa</span>
        </a>
        <a href="history.php" class="bottom-nav-item" data-page="history">
            <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span class="bottom-nav-label">Geçmiş</span>
        </a>
        <a href="analytics.php" class="bottom-nav-item" data-page="analytics">
            <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            <span class="bottom-nav-label">Analiz</span>
        </a>
        <a href="profil.php" class="bottom-nav-item" data-page="profile">
            <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span class="bottom-nav-label">Profil</span>
        </a>
    </nav>

    <script src="js/app.js?v=8"></script>
</body>
</html>
