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
    <title>Dailytor - Geçmiş</title>
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
        :root {
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
        }

        /* Hero Section - Compact */
        .history-hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 20px;
            padding: 24px 32px;
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

        .hero-content-right {
            display: flex;
            flex-direction: column;
            gap: 16px;
            align-items: flex-end;
        }

        @media (min-width: 768px) {
            .hero-content-right {
                flex-direction: row;
                align-items: center;
                gap: 24px;
            }
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

        .hero-date-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            background: rgba(0,0,0,0.15);
            padding: 6px;
            border-radius: 12px;
            flex-direction: row;
        }

        @media (min-width: 768px) {
            .hero-date-controls {
                justify-content: center;
            }
        }

        .hero-date-controls button {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: white;
            transition: all 0.2s;
        }

        .hero-date-controls button:hover {
            background: rgba(255,255,255,0.15);
        }

        .hero-date-controls input {
            background: transparent;
            border: none;
            color: white;
            font-weight: 600;
            font-size: 14px;
            width: 100%;
            min-width: 130px;
            max-width: 180px;
            text-align: center;
            color-scheme: dark;
        }

        /* Weekly Strip - Compact */
        .weekly-strip {
            display: flex;
            gap: 6px;
            margin-bottom: 24px;
            overflow-x: auto;
            padding: 4px;
            scrollbar-width: none;
        }

        .weekly-strip::-webkit-scrollbar {
            display: none;
        }

        .day-card {
            flex: 0 0 60px;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .day-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .day-card.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .day-name {
            font-size: 10px;
            font-weight: 600;
            opacity: 0.7;
            margin-bottom: 4px;
        }

        .day-number {
            font-size: 18px;
            font-weight: 700;
        }

        .day-card.active .day-name {
            opacity: 0.9;
        }

        /* History Summary - Compact Overview */
        .history-summary {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .summary-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .summary-stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .summary-stat {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .summary-stat-content {
            display: flex;
            flex-direction: column;
        }

        .summary-stat-value {
            font-size: 16px;
            font-weight: 600;
            line-height: 1.2;
        }

        .summary-stat-label {
            font-size: 11px;
            color: var(--text-secondary);
        }

        /* Category Sections */
        .history-category {
            background: var(--bg-secondary);
            border-radius: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid transparent;
        }

        .category-header:hover {
            background: var(--bg-tertiary);
        }

        .category-header.expanded {
            border-bottom-color: var(--border);
        }

        .category-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .category-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .category-name {
            font-size: 15px;
            font-weight: 600;
        }

        .category-count {
            font-size: 12px;
            color: var(--text-secondary);
            margin-left: 8px;
        }

        .category-toggle {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: all 0.2s;
        }

        .category-toggle svg {
            transition: transform 0.2s;
        }

        .category-header.expanded .category-toggle svg {
            transform: rotate(180deg);
        }

        .category-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .category-content.expanded {
            max-height: 2000px;
        }

        /* Compact History Items */
        .history-items {
            padding: 12px;
        }

        .history-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 8px;
            background: var(--bg-tertiary);
            transition: all 0.2s;
        }

        .history-item:hover {
            background: var(--bg-primary);
            transform: translateX(4px);
        }

        .history-item.completed {
            background: rgba(16, 185, 129, 0.05);
            border-left: 3px solid var(--success);
        }

        .history-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            flex-shrink: 0;
        }

        .history-item-content {
            flex: 1;
            min-width: 0;
        }

        .history-item-name {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .history-item-details {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .history-item-value {
            font-weight: 600;
            color: var(--text-primary);
        }

        .history-item-status {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-dot.completed {
            background: var(--success);
        }

        .status-dot.pending {
            background: var(--warning);
        }

        .history-item-actions {
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        /* Mobile: always show action buttons */
        @media (max-width: 767px) {
            .history-item-actions {
                opacity: 1;
            }

            .item-action-btn {
                width: 38px;
                height: 38px;
            }
        }

        .history-item:hover .history-item-actions {
            opacity: 1;
        }

        .item-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: all 0.2s;
        }

        .item-action-btn:hover {
            background: var(--bg-secondary);
            color: var(--primary);
        }

        .item-action-btn:active {
            transform: scale(0.95);
        }

        /* Compact Edit Modal */
        .compact-edit-panel {
            display: none;
            padding: 12px;
            background: var(--bg-secondary);
            border-top: 1px solid var(--border);
        }

        .compact-edit-panel.active {
            display: block;
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
            }
            to {
                opacity: 1;
                max-height: 200px;
            }
        }

        .compact-edit-row {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .compact-edit-input {
            flex: 1;
            min-width: 80px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .compact-edit-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Mobile: better touch targets */
        @media (max-width: 767px) {
            .compact-edit-row {
                flex-direction: column;
                gap: 10px;
            }

            .compact-edit-input {
                width: 100%;
                flex: none;
                padding: 12px 14px;
                font-size: 16px;
            }
        }

        /* Empty State */
        .history-empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px dashed var(--border);
        }

        .history-empty-state svg {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            opacity: 0.3;
        }

        .history-empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .history-empty-state p {
            font-size: 14px;
            color: var(--text-secondary);
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: slideIn 0.3s ease-out forwards;
        }

        /* Responsive - Mobile First */
        @media (min-width: 600px) {
            .summary-stats {
                flex-wrap: nowrap;
            }
        }

        @media (min-width: 768px) {
            .history-hero {
                padding: 24px 32px;
            }

            .hero-left {
                flex-direction: row;
                align-items: center;
                gap: 24px;
            }

            .hero-content-right {
                width: auto;
                justify-content: flex-end;
                gap: 24px;
            }

            .hero-stats {
                gap: 24px;
            }

            .hero-date-section .date-large {
                font-size: 28px;
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

            .day-card {
                flex: 0 0 60px;
                padding: 10px 4px;
            }

            .summary-stats {
                gap: 20px;
            }

            .history-item {
                padding: 12px 16px;
            }

            .history-item-icon {
                width: 40px;
                height: 40px;
            }

            .history-item-actions {
                opacity: 0;
            }

            .history-item:hover .history-item-actions {
                opacity: 1;
            }

            .category-header {
                padding: 16px 20px;
            }

            .compact-edit-row {
                flex-wrap: nowrap;
            }
        }

        @media (min-width: 1024px) {
            .history-hero {
                flex-wrap: nowrap;
            }

            .hero-content-right {
                align-items: center;
            }
        }

        /* Mobile responsive for hero */
        @media (max-width: 767px) {
            .history-hero {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
                padding: 20px;
            }

            .hero-left {
                width: 100%;
                justify-content: space-between;
            }

            .hero-content-right {
                width: 100%;
                align-items: flex-start;
                gap: 12px;
            }

            .hero-stats {
                width: 100%;
                justify-content: space-between;
                gap: 16px;
            }

            .hero-date-controls {
                width: 100%;
                justify-content: space-between;
            }

            .hero-date-controls input {
                flex: 1;
                max-width: none;
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
                <a href="dashboard.php">Dashboard</a>
                <a href="history.php" class="active">Geçmiş</a>
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
        <!-- Weekly Strip -->
        <div id="weeklyStrip" class="weekly-strip">
            <div class="loading-spinner" style="width:20px;height:20px;margin:10px auto;"></div>
        </div>

        <!-- Hero Section -->
        <div class="history-hero">
            <div class="hero-left">
                <div class="hero-date-section">
                    <div class="date-large" id="displayDate">-- -- ----</div>
                    <div class="date-small" id="displayDayName">--------</div>
                </div>

                <div class="hero-progress-section">
                    <div class="hero-progress-circle">
                        <svg width="80" height="80" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="36" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="6"/>
                            <circle id="heroProgressRing" cx="40" cy="40" r="36" fill="none" stroke="#ffffff" stroke-width="6" stroke-linecap="round" stroke-dasharray="226.2" stroke-dashoffset="226.2"/>
                        </svg>
                        <div class="hero-progress-text">
                            <span id="heroProgressPercent">0</span>%
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-content-right">
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-value" id="heroCompleted">0</div>
                        <div class="hero-stat-label">Tamamlanan</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value" id="heroCalories">--</div>
                        <div class="hero-stat-label">Kalori Dengesi</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value" id="heroExercise">--</div>
                        <div class="hero-stat-label">Egzersiz</div>
                    </div>
                </div>

                <div class="hero-date-controls">
                    <button id="prevDay">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"/></svg>
                    </button>
                    <input type="date" id="historyDate" max="">
                    <button id="nextDay">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="history-summary" id="summarySection" style="display:none;">
            <div class="summary-header">
                <span class="summary-title">Günlük Özet</span>
            </div>
            <div class="summary-stats" id="summaryStats">
                <!-- Filled by JS -->
            </div>
        </div>

        <!-- Daily Note Section -->
        <div class="history-note-section" style="background: var(--bg-secondary); border-radius: 16px; margin-bottom: 16px; border: 1px solid var(--border); overflow: hidden;">
            <div class="note-header" style="display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--border);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span style="font-weight: 600; font-size: 15px; color: var(--text-primary);">Günlük Not</span>
            </div>
            <div class="note-content" style="padding: 16px 20px;">
                <div id="dailyNoteText" style="color: var(--text-primary); font-size: 14px; line-height: 1.6; white-space: pre-wrap;"></div>
                <div id="noNoteMessage" style="color: var(--text-secondary); font-size: 14px; font-style: italic;">Bu tarih için not bulunmuyor.</div>
            </div>
        </div>

        <!-- Categories Container -->
        <div id="categoriesContainer">
            <!-- Filled by JS -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="history-empty-state" style="display:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z"/>
            </svg>
            <h3>Bu tarihte kayıt bulunmuyor</h3>
            <p>Başka bir tarih seçin veya dashboard'a dönün.</p>
            <button class="btn btn-primary" onclick="window.location.href='dashboard.php'" style="margin-top:16px;">Dashboard'a Dön</button>
        </div>
    </main>

    <div id="toast"></div>

    <!-- Mobile Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="bottom-nav-item" data-page="dashboard">
            <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span class="bottom-nav-label">Ana Sayfa</span>
        </a>
        <a href="history.php" class="bottom-nav-item active" data-page="history">
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
    <script>
        // API_BASE is already defined in app.js

        // Category definitions
        const CATEGORIES = {
            habits: {
                name: 'Alışkanlıklar',
                icon: 'check-circle',
                color: '#6366f1',
                types: ['checkbox']
            },
            goals: {
                name: 'Hedefler',
                icon: 'target',
                color: '#8b5cf6',
                types: ['numeric', 'adim']
            },
            health: {
                name: 'Sağlık',
                icon: 'heart',
                color: '#ec4899',
                types: ['uyku', 'kalori']
            },
            exercise: {
                name: 'Egzersiz',
                icon: 'activity',
                color: '#10b981',
                types: ['kosu_bandi', 'bisiklet', 'agirlik', 'dis_kosu', 'snav', 'mekik', 'barfiks', 'muscle_up', 'plank']
            }
        };

        const registrationDate = new Date('<?= $kullanici['olusturulma_zamani'] ?>').toISOString().split('T')[0];
        const todayDate = new Date().toISOString().split('T')[0];
        let currentDate = todayDate;
        let historyData = null;

        document.addEventListener('DOMContentLoaded', () => {
            const dateInput = document.getElementById('historyDate');
            dateInput.value = currentDate;
            dateInput.max = todayDate; // Prevent selecting future dates
            initWeeklyStrip();
            loadHistoryPage(currentDate);
        });

        function initWeeklyStrip() {
            const strip = document.getElementById('weeklyStrip');
            const today = new Date(todayDate);
            const regDate = new Date(registrationDate);
            const selectedDate = new Date(currentDate);
            const days = [];

            // Show current week centered on selected date, but don't go past today or before registration
            for (let i = -3; i <= 3; i++) {
                const d = new Date(currentDate);
                d.setDate(d.getDate() + i);

                // Only show dates that are on or before today and on or after registration date
                if (d <= today && d >= regDate) {
                    days.push(d);
                }
            }

            strip.innerHTML = days.map(d => {
                const dateStr = d.toISOString().split('T')[0];
                const isActive = dateStr === currentDate;
                const dayName = d.toLocaleDateString('tr-TR', { weekday: 'short' });
                const dayNum = d.getDate();

                return `
                    <div class="day-card ${isActive ? 'active' : ''}" onclick="selectDate('${dateStr}')">
                        <div class="day-name">${dayName}</div>
                        <div class="day-number">${dayNum}</div>
                    </div>
                `;
            }).join('');
        }

        function selectDate(date) {
            // Prevent future dates
            if (date > todayDate) {
                toast('Gelecek tarihler için geçmiş görüntülenemez', 'error');
                return;
            }

            currentDate = date;
            document.getElementById('historyDate').value = date;
            document.querySelectorAll('.day-card').forEach(c => c.classList.remove('active'));
            document.querySelector(`.day-card[onclick="selectDate('${date}')"]`)?.classList.add('active');
            loadHistoryPage(date);
        }

        async function loadHistoryPage(date) {
            try {
                const data = await api(`${API_BASE}/records.php?tarih=${date}`);
                historyData = data;
                currentDate = date;

                updateDisplayDate(date);
                updateHeroStats(data);
                renderCategories(data.kayitlar);
                updateSummary(data);
                updateDailyNote(data.not || '');
            } catch (err) {
                console.error('Geçmiş yükleme hatası:', err);
            }
        }

        function updateDailyNote(note) {
            const noteText = document.getElementById('dailyNoteText');
            const noNoteMessage = document.getElementById('noNoteMessage');

            if (note && note.trim()) {
                noteText.textContent = note;
                noteText.style.display = 'block';
                noNoteMessage.style.display = 'none';
            } else {
                noteText.style.display = 'none';
                noNoteMessage.style.display = 'block';
            }
        }

        function updateDisplayDate(dateStr) {
            const date = new Date(dateStr);
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            document.getElementById('displayDate').textContent = date.toLocaleDateString('tr-TR', options);
            document.getElementById('displayDayName').textContent = date.toLocaleDateString('tr-TR', { weekday: 'long' });
        }

        function updateHeroStats(data) {
            // Update progress ring
            const percent = data.tamamlanma_yuzdesi || 0;
            const offset = 226.2 - (226.2 * percent / 100);
            document.getElementById('heroProgressRing').style.strokeDashoffset = offset;
            document.getElementById('heroProgressPercent').textContent = Math.round(percent);

            // Update completed count
            const completed = data.kayitlar?.filter(k => k.tamamlandi).length || 0;
            document.getElementById('heroCompleted').textContent = completed;

            // Update calories
            const balance = data.kalori_dengesi;
            if (balance) {
                const fark = balance.fark || 0;
                document.getElementById('heroCalories').textContent = (fark >= 0 ? '+' : '') + fark;
                document.getElementById('heroExercise').textContent = balance.yakilan || 0;
            } else {
                document.getElementById('heroCalories').textContent = '--';
                document.getElementById('heroExercise').textContent = '--';
            }
        }

        function updateSummary(data) {
            const summaryEl = document.getElementById('summarySection');
            const statsEl = document.getElementById('summaryStats');

            if (!data.kayitlar || data.kayitlar.length === 0) {
                summaryEl.style.display = 'none';
                return;
            }

            summaryEl.style.display = 'block';

            const completed = data.kayitlar.filter(k => k.tamamlandi).length;
            const total = data.kayitlar.length;
            const totalCalories = data.kayitlar.reduce((sum, k) => sum + (k.yakilan_kalori || 0), 0);
            const sleepRecord = data.kayitlar.find(k => k.tip === 'uyku');
            const sleepHours = sleepRecord ? Math.round((sleepRecord.deger || 0) / 60 * 10) / 10 : 0;

            statsEl.innerHTML = `
                <div class="summary-stat">
                    <div class="summary-stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>
                        </svg>
                    </div>
                    <div class="summary-stat-content">
                        <span class="summary-stat-value">${completed}/${total}</span>
                        <span class="summary-stat-label">Tamamlandı</span>
                    </div>
                </div>
                <div class="summary-stat">
                    <div class="summary-stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v20M2 12h20"/>
                        </svg>
                    </div>
                    <div class="summary-stat-content">
                        <span class="summary-stat-value">${totalCalories}</span>
                        <span class="summary-stat-label">Yakılan Kalori</span>
                    </div>
                </div>
                ${sleepHours > 0 ? `
                <div class="summary-stat">
                    <div class="summary-stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </div>
                    <div class="summary-stat-content">
                        <span class="summary-stat-value">${sleepHours}s</span>
                        <span class="summary-stat-label">Uyku Süresi</span>
                    </div>
                </div>` : ''}
            `;
        }

        function renderCategories(records) {
            const container = document.getElementById('categoriesContainer');
            const emptyState = document.getElementById('emptyState');

            if (!records || records.length === 0) {
                container.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            // Group records by category
            const grouped = {};
            for (const [key, cat] of Object.entries(CATEGORIES)) {
                grouped[key] = records.filter(r => cat.types.includes(r.tip));
            }

            // Remove empty categories
            const nonEmpty = Object.entries(grouped).filter(([_, items]) => items.length > 0);

            container.innerHTML = nonEmpty.map(([catKey, items], idx) => {
                const cat = CATEGORIES[catKey];
                const iconSvg = getCategoryIcon(cat.icon, cat.color);

                return `
                    <div class="history-category fade-in" style="animation-delay: ${idx * 0.05}s">
                        <div class="category-header" onclick="toggleCategory(this)">
                            <div class="category-title">
                                <div class="category-icon" style="background: ${cat.color}15; color: ${cat.color};">
                                    ${iconSvg}
                                </div>
                                <div>
                                    <span class="category-name">${cat.name}</span>
                                    <span class="category-count">${items.length} öğe</span>
                                </div>
                            </div>
                            <div class="category-toggle">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="category-content expanded">
                            <div class="history-items">
                                ${items.map(item => renderHistoryItem(item)).join('')}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderHistoryItem(item) {
            const isCompleted = item.tamamlandi;
            const details = getItemDetails(item);

            let mealsHtml = '';
            if (item.tip === 'kalori' && item.yemekler && item.yemekler.length > 0) {
                mealsHtml = `
                    <div class="meal-log-list" style="margin-top: 8px; border-top: 1px solid var(--border); padding-top: 8px; width: 100%;">
                        ${item.yemekler.map(meal => {
                            const imgHtml = meal.foto_yolu 
                                ? `<img class="meal-log-img" src="${API_BASE.replace('/api', '')}/${meal.foto_yolu}" alt="${meal.yemek_adi}" style="width: 28px; height: 28px; border-radius: 6px;">`
                                : `<div class="meal-log-emoji" style="width: 28px; height: 28px; font-size: 14px; border-radius: 6px;">${meal.emoji || '🍽️'}</div>`;
                            
                            const macrosText = (parseInt(meal.protein) > 0 || parseFloat(meal.karbonhidrat) > 0 || parseInt(meal.yag) > 0)
                                ? `P: ${meal.protein || 0}g | K: ${meal.karbonhidrat || 0}g | Y: ${meal.yag || 0}g`
                                : '';
                            
                            return `
                                <div class="meal-log-item" style="padding: 6px 10px; border-radius: 8px; font-size: 12px; margin-bottom: 4px; border: 1px solid var(--border);">
                                    <div class="meal-log-left" style="gap: 8px;">
                                        ${imgHtml}
                                        <div class="meal-log-info">
                                            <div class="meal-log-name" style="font-size: 12px;">${meal.yemek_adi}</div>
                                            ${macrosText ? `<div class="meal-log-macros" style="font-size: 9px; margin-top:0;">${macrosText}</div>` : ''}
                                        </div>
                                    </div>
                                    <div class="meal-log-right" style="gap: 6px;">
                                        <div class="meal-log-calories" style="font-size: 12px;">${meal.kalori} kcal</div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                `;
            }

            return `
                <div class="history-item ${isCompleted ? 'completed' : ''}" data-id="${item.aliskanlik_id}" style="flex-wrap: wrap;">
                    <div style="display: flex; width: 100%; align-items: center;">
                        <div class="history-item-icon" style="background: ${item.renk}15; color: ${item.renk};">
                            ${getItemTypeIcon(item.tip)}
                        </div>
                        <div class="history-item-content">
                            <div class="history-item-name">${item.ad}</div>
                            <div class="history-item-details">
                                ${details.valueHtml}
                                ${details.targetHtml}
                            </div>
                        </div>
                        <div class="history-item-status">
                            <div class="status-dot ${isCompleted ? 'completed' : 'pending'}"></div>
                        </div>
                        <div class="history-item-actions">
                            <button class="item-action-btn" onclick="toggleQuickComplete(${item.aliskanlik_id}, ${isCompleted})" title="${isCompleted ? 'Geri Al' : 'Tamamla'}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    ${isCompleted ? '<path d="M3 3h18v18H3zM9 9l6 6M15 9l-6 6"/>' : '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>'}
                                </svg>
                            </button>
                            ${item.tip !== 'checkbox' ? `
                            <button class="item-action-btn" onclick="openHistoryEditModal(${item.aliskanlik_id})" title="Düzenle">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>` : ''}
                        </div>
                    </div>
                    ${mealsHtml}
                </div>
            `;
        }

        function getItemDetails(item) {
            let valueHtml = '';
            let targetHtml = '';

            switch (item.tip) {
                case 'checkbox':
                    valueHtml = `<span class="history-item-value">${item.tamamlandi ? 'Tamamlandı' : 'Yapılmadı'}</span>`;
                    break;
                case 'numeric':
                    valueHtml = `<span class="history-item-value">${item.deger || 0} ${item.birim || ''}</span>`;
                    targetHtml = `<span>Hedef: ${item.hedef_deger || '-'} ${item.birim || ''}</span>`;
                    break;
                case 'adim':
                    valueHtml = `<span class="history-item-value">${(item.deger || 0).toLocaleString('tr-TR')} adım</span>`;
                    targetHtml = `<span>Hedef: ${(item.hedef_deger || 10000).toLocaleString('tr-TR')}</span>`;
                    break;
                case 'uyku':
                    const hours = Math.floor((item.deger || 0) / 60);
                    const mins = (item.deger || 0) % 60;
                    valueHtml = `<span class="history-item-value">${hours}s ${mins}dk</span>`;
                    const targetHours = Math.floor((item.hedef_deger || 480) / 60);
                    const targetMins = (item.hedef_deger || 480) % 60;
                    targetHtml = `<span>Hedef: ${targetHours}s ${targetMins}dk</span>`;
                    break;
                case 'kalori':
                    valueHtml = `<span class="history-item-value">${item.deger || 0} kcal</span>`;
                    break;
                case 'kosu_bandi':
                    valueHtml = `<span class="history-item-value">${item.deger2 || 0} dk @ ${item.deger3 || 5} km/s</span>`;
                    if (item.deger4 > 0) targetHtml = `<span>Eğim: %${item.deger4}</span>`;
                    break;
                case 'bisiklet':
                case 'agirlik':
                    valueHtml = `<span class="history-item-value">${item.deger2 || 0} dakika</span>`;
                    break;
                case 'dis_kosu':
                    valueHtml = `<span class="history-item-value">${item.deger2 || 0} dk @ ${item.deger || 6} dk/km tempoda</span>`;
                    break;
                case 'snav':
                case 'mekik':
                case 'barfiks':
                case 'muscle_up':
                    const egzersizAd = item.tip === 'snav' ? 'Şınav' : item.tip === 'mekik' ? 'Mekik' : item.tip === 'barfiks' ? 'Barfiks' : 'Muscle Up';
                    valueHtml = `<span class="history-item-value">${item.deger || 0} adet</span>`;
                    targetHtml = `<span>Hedef: ${item.hedef_deger || 10} adet</span>`;
                    break;
                case 'plank':
                    const pMins = Math.floor((item.deger || 0) / 60);
                    const pSecs = (item.deger || 0) % 60;
                    valueHtml = `<span class="history-item-value">${pMins > 0 ? pMins + 'dk ' : ''}${pSecs}sn</span>`;
                    targetHtml = `<span>Hedef: ${item.hedef_deger || 60}sn</span>`;
                    break;
            }

            if (item.yakilan_kalori && item.yakilan_kalori > 0) {
                valueHtml += ` <span style="color:var(--danger);font-weight:600;">🔥 ${item.yakilan_kalori} kcal</span>`;
            }

            return { valueHtml, targetHtml };
        }

        function getItemTypeIcon(type) {
            const icons = {
                checkbox: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>',
                numeric: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/></svg>',
                adim: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2l1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/></svg>',
                uyku: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
                kalori: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/></svg>',
                kosu_bandi: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
                bisiklet: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6l-3 10H7l-2-4 4-4 2 2 4-2z"/></svg>',
                agirlik: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 5v14M10 5v14M14 5v14M18 5v14"/></svg>',
                dis_kosu: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
                snav: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 8h16M4 16h16M8 4v16M12 4v16M16 4v16"/></svg>',
                mekik: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>',
                barfiks: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16M8 8v8M16 8v8M12 4v16"/></svg>',
                muscle_up: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 12l3 3 7-7 7 7 3-3L12 2Z"/></svg>',
                plank: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="8" width="16" height="8" rx="2"/></svg>'
            };
            return icons[type] || icons.checkbox;
        }

        function getCategoryIcon(iconName, color) {
            const icons = {
                'check-circle': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>',
                'target': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
                'heart': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
                'activity': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>'
            };
            return icons[iconName] || icons['check-circle'];
        }

        function renderEditPanel(item) {
            let inputHtml = '';

            switch (item.tip) {
                case 'numeric':
                case 'adim':
                    inputHtml = `
                        <input type="number" class="compact-edit-input" value="${item.deger || 0}"
                            onchange="updateHistoryItem(${item.aliskanlik_id}, 'deger', this.value)">
                        <span style="font-size:12px;color:var(--text-secondary);">${item.birim || 'adım'}</span>
                    `;
                    break;
                case 'uyku':
                    const hours = Math.floor((item.deger || 0) / 60);
                    const mins = (item.deger || 0) % 60;
                    inputHtml = `
                        <input type="number" class="compact-edit-input" value="${hours}" min="0" max="24" placeholder="Saat"
                            onchange="updateSleepItem(${item.aliskanlik_id}, 'saat', this.value)">
                        <input type="number" class="compact-edit-input" value="${mins}" min="0" max="59" placeholder="Dakika"
                            onchange="updateSleepItem(${item.aliskanlik_id}, 'dakika', this.value)">
                    `;
                    break;
                case 'kalori':
                    inputHtml = `
                        <input type="number" class="compact-edit-input" value="${item.deger || 0}" min="0"
                            onchange="updateHistoryItem(${item.aliskanlik_id}, 'deger', this.value)">
                        <span style="font-size:12px;color:var(--text-secondary);">kcal</span>
                    `;
                    break;
                case 'kosu_bandi':
                    inputHtml = `
                        <input type="number" class="compact-edit-input" value="${item.deger2 || 0}" min="0" placeholder="Süre (dk)"
                            onchange="updateExerciseItem(${item.aliskanlik_id}, 'sure', this.value)">
                        <input type="number" class="compact-edit-input" value="${item.deger3 || 5}" step="0.1" min="1" placeholder="Hız"
                            onchange="updateExerciseItem(${item.aliskanlik_id}, 'hiz', this.value)">
                        <input type="number" class="compact-edit-input" value="${item.deger4 || 0}" min="0" placeholder="Eğim"
                            onchange="updateExerciseItem(${item.aliskanlik_id}, 'egim', this.value)">
                    `;
                    break;
                case 'bisiklet':
                case 'agirlik':
                    inputHtml = `
                        <input type="number" class="compact-edit-input" value="${item.deger2 || 0}" min="0"
                            onchange="updateExerciseItem(${item.aliskanlik_id}, 'sure', this.value)">
                        <span style="font-size:12px;color:var(--text-secondary);">dakika</span>
                    `;
                    break;
                case 'dis_kosu':
                    inputHtml = `
                        <input type="number" class="compact-edit-input" value="${item.deger2 || 0}" min="0" placeholder="Süre (dk)"
                            onchange="updateDisKosuItem(${item.aliskanlik_id}, 'sure', this.value)">
                        <input type="number" class="compact-edit-input" value="${item.deger || 6}" step="0.1" min="3" placeholder="Tempo"
                            onchange="updateDisKosuItem(${item.aliskanlik_id}, 'tempo', this.value)">
                        <span style="font-size:12px;color:var(--text-secondary);">dk/km</span>
                    `;
                    break;
                case 'snav':
                case 'mekik':
                case 'barfiks':
                case 'muscle_up':
                    inputHtml = `
                        <input type="number" class="compact-edit-input" value="${item.deger || 0}" min="0"
                            onchange="updateHistoryItem(${item.aliskanlik_id}, 'deger', this.value)">
                        <span style="font-size:12px;color:var(--text-secondary);">adet</span>
                    `;
                    break;
                case 'plank':
                    const pMins = Math.floor((item.deger || 0) / 60);
                    const pSecs = (item.deger || 0) % 60;
                    inputHtml = `
                        <input type="number" class="compact-edit-input" value="${pMins}" min="0" placeholder="Dk"
                            onchange="updatePlankItem(${item.aliskanlik_id}, 'dk', this.value)">
                        <input type="number" class="compact-edit-input" value="${pSecs}" min="0" max="59" placeholder="Sn"
                            onchange="updatePlankItem(${item.aliskanlik_id}, 'sn', this.value)">
                        <span style="font-size:12px;color:var(--text-secondary);">süre</span>
                    `;
                    break;
            }

            return `
                <div class="compact-edit-panel" id="editPanel-${item.aliskanlik_id}">
                    <div class="compact-edit-row">
                        ${inputHtml}
                    </div>
                </div>
            `;
        }

        let currentEditId = null;

        function openHistoryEditModal(id) {
            const item = historyData.kayitlar.find(r => r.aliskanlik_id === id);
            if (!item) return;

            currentEditId = id;
            const modal = document.getElementById('historyEditModal');
            const body = document.getElementById('editModalBody');
            document.getElementById('editModalTitle').textContent = item.ad;

            let html = '';
            switch (item.tip) {
                case 'numeric':
                case 'adim':
                case 'kalori':
                    html = `
                        <div class="form-group">
                            <label class="form-label">Değer (${item.birim || (item.tip === 'kalori' ? 'kcal' : 'adım')})</label>
                            <input type="number" id="editValue" class="form-input" value="${item.deger || 0}">
                        </div>
                    `;
                    break;
                case 'uyku':
                    const h = Math.floor((item.deger || 0) / 60);
                    const m = (item.deger || 0) % 60;
                    html = `
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Saat</label>
                                <input type="number" id="editHours" class="form-input" value="${h}" min="0" max="24">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Dakika</label>
                                <input type="number" id="editMins" class="form-input" value="${m}" min="0" max="59">
                            </div>
                        </div>
                    `;
                    break;
                case 'kosu_bandi':
                    html = `
                        <div class="form-group">
                            <label class="form-label">Süre (Dakika)</label>
                            <input type="number" id="editSure" class="form-input" value="${item.deger2 || 0}" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hız (km/s)</label>
                            <input type="number" id="editHiz" class="form-input" value="${item.deger3 || 5}" step="0.1" min="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Eğim (%)</label>
                            <input type="number" id="editEgim" class="form-input" value="${item.deger4 || 0}" min="0">
                        </div>
                    `;
                    break;
                case 'bisiklet':
                case 'agirlik':
                    html = `
                        <div class="form-group">
                            <label class="form-label">Süre (Dakika)</label>
                            <input type="number" id="editSure" class="form-input" value="${item.deger2 || 0}" min="0">
                        </div>
                    `;
                    break;
                case 'dis_kosu':
                    html = `
                        <div class="form-group">
                            <label class="form-label">Süre (Dakika)</label>
                            <input type="number" id="editSure" class="form-input" value="${item.deger2 || 0}" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tempo (dk/km)</label>
                            <input type="number" id="editTempo" class="form-input" value="${item.deger || 6}" step="0.1" min="3">
                        </div>
                    `;
                    break;
                case 'snav':
                case 'mekik':
                case 'barfiks':
                case 'muscle_up':
                    html = `
                        <div class="form-group">
                            <label class="form-label">Adet Sayısı</label>
                            <input type="number" id="editValue" class="form-input" value="${item.deger || 0}" min="0">
                        </div>
                    `;
                    break;
                case 'plank':
                    const pMins = Math.floor((item.deger || 0) / 60);
                    const pSecs = (item.deger || 0) % 60;
                    html = `
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Dakika</label>
                                <input type="number" id="editPlankMins" class="form-input" value="${pMins}" min="0" max="60">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Saniye</label>
                                <input type="number" id="editPlankSecs" class="form-input" value="${pSecs}" min="0" max="59">
                            </div>
                        </div>
                    `;
                    break;
            }

            body.innerHTML = html;
            modal.classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('historyEditModal').classList.remove('active');
            currentEditId = null;
        }

        async function saveHistoryEdit() {
            if (!currentEditId) return;
            const item = historyData.kayitlar.find(r => r.aliskanlik_id === currentEditId);
            const updates = { aliskanlik_id: currentEditId, tarih: currentDate };

            switch (item.tip) {
                case 'numeric':
                case 'adim':
                case 'kalori':
                    updates.deger = parseInt(document.getElementById('editValue').value) || 0;
                    break;
                case 'uyku':
                    const h = parseInt(document.getElementById('editHours').value) || 0;
                    const m = parseInt(document.getElementById('editMins').value) || 0;
                    updates.deger = (h * 60) + m;
                    break;
                case 'kosu_bandi':
                    updates.deger2 = parseInt(document.getElementById('editSure').value) || 0;
                    updates.deger3 = parseFloat(document.getElementById('editHiz').value) || 5;
                    updates.deger4 = parseInt(document.getElementById('editEgim').value) || 0;
                    break;
                case 'bisiklet':
                case 'agirlik':
                    updates.deger2 = parseInt(document.getElementById('editSure').value) || 0;
                    break;
                case 'dis_kosu':
                    updates.deger = parseFloat(document.getElementById('editTempo').value) || 6;
                    updates.deger2 = parseInt(document.getElementById('editSure').value) || 0;
                    break;
                case 'snav':
                case 'mekik':
                case 'barfiks':
                case 'muscle_up':
                    updates.deger = parseInt(document.getElementById('editValue').value) || 0;
                    break;
                case 'plank':
                    const pMins = parseInt(document.getElementById('editPlankMins').value) || 0;
                    const pSecs = parseInt(document.getElementById('editPlankSecs').value) || 0;
                    updates.deger = (pMins * 60) + pSecs;
                    break;
            }

            try {
                await api(`${API_BASE}/records.php`, {
                    method: 'POST',
                    body: JSON.stringify(updates)
                });
                toast('Güncellendi');
                closeEditModal();
                loadHistoryPage(currentDate);
            } catch (err) {
                toast(err.message, 'error');
            }
        }

        function toggleCategory(header) {
            header.classList.toggle('expanded');
            const content = header.nextElementSibling;
            content.classList.toggle('expanded');
        }

        async function toggleQuickComplete(id, currentStatus) {
            try {
                await api(`${API_BASE}/records.php`, {
                    method: 'POST',
                    body: JSON.stringify({
                        aliskanlik_id: id,
                        tarih: currentDate,
                        tamamlandi: !currentStatus
                    })
                });
                toast(currentStatus ? 'Geri alındı' : 'Tamamlandı');
                loadHistoryPage(currentDate);
            } catch (err) {
                toast(err.message, 'error');
            }
        }

        // Date navigation
        document.getElementById('prevDay').addEventListener('click', () => {
            const date = new Date(currentDate);
            date.setDate(date.getDate() - 1);
            selectDate(date.toISOString().split('T')[0]);
        });

        document.getElementById('nextDay').addEventListener('click', () => {
            const date = new Date(currentDate);
            const today = new Date(todayDate);
            date.setDate(date.getDate() + 1);
            if (date <= today) {
                selectDate(date.toISOString().split('T')[0]);
            }
        });

        document.getElementById('historyDate').addEventListener('change', (e) => {
            const selectedDate = new Date(e.target.value);
            const today = new Date(todayDate);

            // Don't allow future dates
            if (selectedDate > today) {
                toast('Gelecek tarihler için geçmiş görüntülenemez', 'error');
                e.target.value = currentDate;
                return;
            }

            selectDate(e.target.value);
        });

        // Additional update functions for new exercise types
        async function updateDisKosuItem(id, field, value) {
            const item = historyData.kayitlar.find(r => r.aliskanlik_id === id);
            const updates = { aliskanlik_id: id, tarih: currentDate };
            updates.deger = parseFloat(item.deger) || 6;
            updates.deger2 = parseInt(item.deger2) || 0;
            updates[field === 'tempo' ? 'deger' : 'deger2'] = field === 'tempo' ? parseFloat(value) : parseInt(value);

            try {
                await api(`${API_BASE}/records.php`, { method: 'POST', body: JSON.stringify(updates) });
                toast('Güncellendi');
                loadHistoryPage(currentDate);
            } catch (err) {
                toast(err.message, 'error');
            }
        }

        async function updatePlankItem(id, field, value) {
            const item = historyData.kayitlar.find(r => r.aliskanlik_id === id);
            const currentMins = Math.floor((item.deger || 0) / 60);
            const currentSecs = (item.deger || 0) % 60;
            let newMins = currentMins;
            let newSecs = currentSecs;

            if (field === 'dk') newMins = parseInt(value) || 0;
            if (field === 'sn') newSecs = parseInt(value) || 0;

            try {
                await api(`${API_BASE}/records.php`, {
                    method: 'POST',
                    body: JSON.stringify({ aliskanlik_id: id, tarih: currentDate, deger: (newMins * 60) + newSecs })
                });
                toast('Güncellendi');
                loadHistoryPage(currentDate);
            } catch (err) {
                toast(err.message, 'error');
            }
        }

        async function updateHistoryItem(id, field, value) {
            try {
                await api(`${API_BASE}/records.php`, {
                    method: 'POST',
                    body: JSON.stringify({ aliskanlik_id: id, tarih: currentDate, [field]: parseInt(value) || 0 })
                });
                toast('Güncellendi');
                loadHistoryPage(currentDate);
            } catch (err) {
                toast(err.message, 'error');
            }
        }

        async function updateExerciseItem(id, field, value) {
            const item = historyData.kayitlar.find(r => r.aliskanlik_id === id);
            const updates = { aliskanlik_id: id, tarih: currentDate };
            updates.deger2 = parseInt(item.deger2) || 0;
            updates.deger3 = parseFloat(item.deger3) || 5;
            updates.deger4 = parseInt(item.deger4) || 0;

            if (field === 'sure') updates.deger2 = parseInt(value) || 0;
            if (field === 'hiz') updates.deger3 = parseFloat(value) || 5;
            if (field === 'egim') updates.deger4 = parseInt(value) || 0;

            try {
                await api(`${API_BASE}/records.php`, { method: 'POST', body: JSON.stringify(updates) });
                toast('Güncellendi');
                loadHistoryPage(currentDate);
            } catch (err) {
                toast(err.message, 'error');
            }
        }

        async function updateSleepItem(id, field, value) {
            const item = historyData.kayitlar.find(r => r.aliskanlik_id === id);
            const currentMins = item.deger || 0;
            const currentH = Math.floor(currentMins / 60);
            const currentM = currentMins % 60;
            let newH = currentH;
            let newM = currentM;

            if (field === 'saat') newH = parseInt(value) || 0;
            if (field === 'dakika') newM = parseInt(value) || 0;

            try {
                await api(`${API_BASE}/records.php`, {
                    method: 'POST',
                    body: JSON.stringify({ aliskanlik_id: id, tarih: currentDate, deger: (newH * 60) + newM })
                });
                toast('Güncellendi');
                loadHistoryPage(currentDate);
            } catch (err) {
                toast(err.message, 'error');
            }
        }

    </script>

    <!-- History Edit Modal -->
    <div id="historyEditModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="editModalTitle">Kaydı Düzenle</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <div id="editModalBody" style="padding: 20px 0;">
                <!-- Inputs will be injected here -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="saveHistoryEdit()" style="width: 100%;">Kaydet</button>
            </div>
        </div>
    </div>
</body>
</html>
