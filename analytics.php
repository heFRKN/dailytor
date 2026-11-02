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
    <title>Dailytor - Analiz</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .weight-prediction {
            background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
            border-radius: var(--radius);
            padding: var(--space-lg);
            color: white;
            margin-bottom: var(--space-xl);
        }
        .weight-prediction h3 {
            font-size: 14px;
            font-weight: 600;
            opacity: 0.9;
            margin-bottom: var(--space-md);
        }
        .weight-predictions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-md);
        }
        .weight-predict-item {
            text-align: center;
            padding: var(--space-md);
            background: rgba(255,255,255,0.1);
            border-radius: var(--radius-sm);
        }
        .weight-predict-label {
            font-size: 11px;
            opacity: 0.8;
            margin-bottom: var(--space-xs);
        }
        .weight-predict-value {
            font-size: 24px;
            font-weight: 700;
        }
        .weight-predict-value small {
            font-size: 12px;
        }
        .weight-predict-diff {
            font-size: 11px;
            opacity: 0.8;
            margin-top: var(--space-xs);
        }
        .daily-stats {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-md);
            margin-top: var(--space-md);
            padding-top: var(--space-md);
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        .daily-stat {
            text-align: center;
        }
        .daily-stat-label {
            font-size: 11px;
            opacity: 0.8;
        }
        .daily-stat-value {
            font-size: 16px;
            font-weight: 600;
        }
        .target-goal-section {
            margin-top: var(--space-xl);
            padding-top: var(--space-xl);
            border-top: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: flex-start;
            gap: var(--space-md);
            flex-wrap: wrap;
        }
        
        /* Mobile responsive overrides */
        @media (min-width: 600px) {
            .weight-predictions {
                grid-template-columns: repeat(4, 1fr);
            }
            .daily-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (min-width: 768px) {
            .weight-prediction {
                padding: 24px;
            }
            .weight-prediction h3 {
                font-size: 16px;
                margin-bottom: 16px;
            }
            .weight-predictions {
                gap: 16px;
            }
            .weight-predict-item {
                padding: 16px;
            }
            .weight-predict-value {
                font-size: 28px;
            }
            .weight-predict-value small {
                font-size: 14px;
            }
            .daily-stats {
                gap: 16px;
                margin-top: 16px;
                padding-top: 16px;
            }
            .daily-stat-value {
                font-size: 18px;
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
                <a href="history.php">Geçmiş</a>
                <a href="analytics.php" class="active">Analiz</a>
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

    <main class="main-content analytics">
        <div class="page-header">
            <h1>Analiz</h1>
            <select id="periodSelect" class="period-select" onchange="loadAnalytics()">
                <option value="30" selected>Son 30 Gün</option>
                <option value="60">Son 60 Gün</option>
            </select>
        </div>

        <div id="weightPrediction" class="weight-prediction">
            <h3>Kilo Tahmini (Bu Trende Devam Edersen)</h3>
            <div class="weight-predictions">
                <div class="weight-predict-item">
                    <div class="weight-predict-label">1 Hafta Sonra</div>
                    <div class="weight-predict-value" id="weight1w">--<small style="font-size:14px;">kg</small></div>
                    <div class="weight-predict-diff" id="weightDiff1w">--</div>
                </div>
                <div class="weight-predict-item">
                    <div class="weight-predict-label">2 Hafta Sonra</div>
                    <div class="weight-predict-value" id="weight2w">--<small style="font-size:14px;">kg</small></div>
                    <div class="weight-predict-diff" id="weightDiff2w">--</div>
                </div>
                <div class="weight-predict-item">
                    <div class="weight-predict-label">3 Hafta Sonra</div>
                    <div class="weight-predict-value" id="weight3w">--<small style="font-size:14px;">kg</small></div>
                    <div class="weight-predict-diff" id="weightDiff3w">--</div>
                </div>
                <div class="weight-predict-item">
                    <div class="weight-predict-label">1 Ay Sonra</div>
                    <div class="weight-predict-value" id="weight4w">--<small style="font-size:14px;">kg</small></div>
                    <div class="weight-predict-diff" id="weightDiff4w">--</div>
                </div>
            </div>
            <div class="daily-stats">
                <div class="daily-stat">
                    <div class="daily-stat-label">Günlük Ort. Alınan</div>
                    <div class="daily-stat-value" id="avgCaloriesIn">-- kcal</div>
                </div>
                <div class="daily-stat">
                    <div class="daily-stat-label">Günlük Ort. Yakılan</div>
                    <div class="daily-stat-value" id="avgCaloriesOut">-- kcal</div>
                </div>
                <div class="daily-stat">
                    <div class="daily-stat-label">Günlük Ort. Fark</div>
                    <div class="daily-stat-value" id="avgCaloriesDiff">-- kcal</div>
                </div>
            </div>

            <div class="target-goal-section" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 180px;">
                    <label style="display: block; font-size: 13px; opacity: 0.9; margin-bottom: 8px; font-weight: 500;">🔔 Hedef Kilonuz</label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="number" id="targetWeightInput" step="0.5" placeholder="Hedef" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; padding: 12px 16px; color: white; width: 110px; font-size: 20px; font-weight: 700; outline: none; transition: all 0.2s;">
                        <span style="font-weight: 600; opacity: 0.9; font-size: 18px;">kg</span>
                    </div>
                </div>
                <div id="targetGoalResult" style="flex: 2; min-width: 280px; background: rgba(0,0,0,0.2); padding: 20px; border-radius: 16px; display: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0);">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 4px; font-weight: 500;" id="targetGoalText">Hedefe ulaşmak için:</div>
                    <div style="font-size: 28px; font-weight: 800; color: #fbbf24;" id="targetGoalDays">-- gün kaldı</div>
                    <div style="font-size: 12px; opacity: 0.7; margin-top: 4px;" id="targetGoalSubtext">Mevcut kalori dengeniz korunursa tahmindir.</div>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <!-- Kilo Takibi spans full width -->
            <div class="stat-card-full">
                <div class="stat-header">
                    <span class="stat-label">Kilo Takibi</span>
                </div>
                <div class="chart-container">
                    <canvas id="weightHistoryChart"></canvas>
                </div>
            </div>

            <!-- Side by side charts -->
            <div class="stat-card-large">
                <div class="stat-header">
                    <span class="stat-label">Kalori Dengesi</span>
                </div>
                <div class="chart-container">
                    <canvas id="calorieBalanceChart"></canvas>
                </div>
            </div>

            <div class="stat-card-large">
                <div class="stat-header">
                    <span class="stat-label">Haftalık Karşılaştırma</span>
                </div>
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>

            <!-- Other full width charts -->
            <div class="stat-card-full">
                <div class="stat-header">
                    <span class="stat-label">Günlük Tamamlanma</span>
                </div>
                <div class="chart-container">
                    <canvas id="dailyCompletionChart"></canvas>
                </div>
            </div>

            <div class="stat-card-full">
                <div class="stat-header">
                    <span class="stat-label">Alışkanlık Bazlı İlerleme</span>
                </div>
                <div class="chart-container">
                    <canvas id="habitProgressChart"></canvas>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card-full">
                <div class="stat-header">
                    <span class="stat-label">Aylık Özet</span>
                </div>
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
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
        <a href="history.php" class="bottom-nav-item" data-page="history">
            <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span class="bottom-nav-label">Geçmiş</span>
        </a>
        <a href="analytics.php" class="bottom-nav-item active" data-page="analytics">
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
    <script src="js/charts.js?v=4"></script>
</body>
</html>
