<?php
require_once 'includes/functions.php';
oturumZorla();
$kullanici = kullaniciGetir();
$kullaniciId = $_SESSION['kullanici_id'];
$profil = kullaniciProfilGetir($kullaniciId);
?>
<!DOCTYPE html>
<html lang="tr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dailytor - Profilim</title>
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
            --profile-gradient: var(--primary);
        }

        .profile-wrapper {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 32px;
        }

        .profile-sidebar {
            background: var(--bg-secondary);
            border-radius: 20px;
            padding: 32px 24px;
            border: 1px solid var(--border);
            height: fit-content;
            text-align: center;
        }

        .profile-avatar-large {
            width: 100px;
            height: 100px;
            background: var(--profile-gradient);
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 42px;
            font-weight: 800;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
            border: 4px solid var(--bg-secondary);
        }

        .profile-sidebar h2 {
            margin-bottom: 4px;
            font-size: 22px;
        }

        .profile-sidebar p {
            color: var(--text-tertiary);
            font-size: 13px;
            margin-bottom: 24px;
        }

        .profile-nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .profile-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            font-size: 14px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .profile-nav-item:hover:not(.active) {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .profile-nav-item.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .profile-main {
            background: var(--bg-secondary);
            border-radius: 20px;
            border: 1px solid var(--border);
            overflow: hidden;
            min-height: 500px;
        }

        .profile-content-header {
            padding: 28px 32px;
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border);
        }

        .profile-content-header h3 {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .profile-content-header p {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .profile-tab-content {
            display: none;
            padding: 32px;
        }

        .profile-tab-content.active {
            display: block;
        }

        .form-section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-secondary);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper svg {
            position: absolute;
            left: 14px;
            color: var(--text-tertiary);
            pointer-events: none;
            width: 18px;
            height: 18px;
        }

        .input-wrapper input, .input-wrapper select {
            width: 100%;
            padding: 12px 14px 12px 44px;
            background: var(--bg-primary);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 15px;
            transition: all 0.2s;
            outline: none;
        }

        .input-wrapper input:focus, .input-wrapper select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-info-card {
            background: rgba(99, 102, 241, 0.05);
            border: 1px dashed var(--primary);
            border-radius: 12px;
            padding: 14px 16px;
            margin-top: 12px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .form-info-card svg {
            color: var(--primary);
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            margin-top: 1px;
        }

        .form-info-card p {
            font-size: 13px;
            line-height: 1.5;
            color: var(--text-secondary);
            margin: 0;
        }

        .btn-profile-submit {
            background: var(--profile-gradient);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-profile-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-profile-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Theme Settings */
        .theme-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .theme-option {
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .theme-option:hover {
            border-color: var(--primary);
        }

        .theme-option.active {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .theme-preview {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .theme-option.light .theme-preview {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #6366f1;
        }

        .theme-option.dark .theme-preview {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #a855f7;
        }

        .theme-option.auto .theme-preview {
            background: linear-gradient(135deg, #f8fafc 50%, #1e293b 50%);
            color: #6366f1;
        }

        .theme-option h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .theme-option p {
            font-size: 12px;
            color: var(--text-secondary);
            margin: 0;
        }

        /* Security Settings */
        .security-section {
            margin-bottom: 32px;
        }

        .security-section:last-child {
            margin-bottom: 0;
        }

        .security-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .security-description {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .security-input {
            width: 100%;
            max-width: 400px;
            padding: 12px 16px;
            background: var(--bg-primary);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 15px;
            color: var(--text-primary);
            outline: none;
            margin-bottom: 12px;
        }

        .security-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .security-input::placeholder {
            color: var(--text-tertiary);
        }

        .password-strength {
            margin-top: 10px;
            display: flex;
            gap: 4px;
        }

        .password-strength-bar {
            flex: 1;
            height: 4px;
            background: var(--bg-tertiary);
            border-radius: 2px;
            transition: all 0.3s;
        }

        .password-strength-bar.active.weak { background: var(--danger); }
        .password-strength-bar.active.medium { background: var(--warning); }
        .password-strength-bar.active.strong { background: var(--success); }

        .btn-security {
            padding: 10px 20px;
            background: var(--bg-tertiary);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            color: var(--text-primary);
        }

        .btn-security:hover {
            background: var(--border);
        }

        .btn-security.primary {
            background: var(--primary);
            color: white;
        }

        .btn-security.primary:hover {
            background: var(--primary-hover);
        }

        .session-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            background: var(--bg-primary);
            border-radius: 12px;
            margin-bottom: 8px;
        }

        .session-info h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .session-info p {
            font-size: 12px;
            color: var(--text-secondary);
            margin: 0;
        }

        .session-current {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
        }

        /* Responsive - Mobile First */
        @media (min-width: 600px) {
            .theme-options {
                grid-template-columns: repeat(3, 1fr);
            }

            .form-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (min-width: 768px) {
            .profile-wrapper {
                grid-template-columns: 300px 1fr;
            }

            .profile-nav {
                flex-direction: column;
            }

            .profile-nav-item {
                width: 100%;
            }

            .profile-main {
                min-height: 500px;
            }

            .profile-content-header {
                padding: 28px 32px;
            }

            .profile-tab-content {
                padding: 32px;
            }
        }

        @media (min-width: 1024px) {
            .form-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Mobile Fix */
        @media (max-width: 767px) {
            .profile-wrapper {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .profile-sidebar {
                padding: 24px 20px;
            }

            .profile-main {
                min-height: auto;
            }

            .profile-content-header {
                padding: 20px;
            }

            .profile-tab-content {
                padding: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .theme-options {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .theme-option {
                padding: 16px;
            }

            .input-group {
                margin-bottom: 16px;
            }

            .btn-profile-submit {
                padding: 12px 24px;
            }

            .profile-avatar-large {
                width: 80px;
                height: 80px;
                font-size: 36px;
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
                <a href="analytics.php">Analiz</a>
                <a href="profil.php" class="active">Profil</a>
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
        <div class="profile-wrapper fade-in">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-avatar-large">
                    <?= strtoupper(substr($kullanici['kullanici_adi'], 0, 1)) ?>
                </div>
                <h2 style="font-size: 20px; font-weight: 700; color: var(--text-primary);"><?= htmlspecialchars($kullanici['kullanici_adi']) ?></h2>
                <p style="font-size: 13px; color: var(--text-tertiary); margin-bottom: 24px;">Üyelik: <?= date('d M Y', strtotime($kullanici['olusturulma_zamani'])) ?></p>

                <nav class="profile-nav">
                    <button class="profile-nav-item active" data-tab="personal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Kişisel Bilgiler
                    </button>
                    <button class="profile-nav-item" data-tab="theme">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        Tema Ayarları
                    </button>
                    <button class="profile-nav-item" data-tab="security">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Güvenlik
                    </button>
                </nav>

                <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border);">
                    <a href="logout.php" class="btn btn-logout" style="width: 100%; justify-content: center; background: var(--bg-primary); color: var(--danger); border: 1px solid var(--border); font-size: 14px; min-height: 44px; border-radius: 12px; gap: 10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Oturumu Kapat
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="profile-main">
                <!-- Personal Info Tab -->
                <div id="tab-personal" class="profile-tab-content active">
                    <div class="profile-content-header">
                        <h3>Kişisel Bilgiler</h3>
                        <p>Fiziksel ölçülerinizi ve aktivite durumunuzu güncelleyin.</p>
                    </div>

                    <form id="profileForm" style="padding: 32px 0;" onsubmit="updateProfile(event)">
                        <div class="form-section-title">Vücut Ölçüleriniz</div>

                        <div class="form-row">
                            <div class="input-group">
                                <label>Boy (cm)</label>
                                <div class="input-wrapper">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 2h2M11 22h2M12 2v20M7 5l5-3 5 3M7 19l5 3 5-3"/></svg>
                                    <input type="number" name="boy" value="<?= $profil['boy'] ?>" placeholder="175" required min="100" max="250">
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Mevcut Kilo (kg)</label>
                                <div class="input-wrapper">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                    <input type="number" name="kilo" step="0.1" value="<?= $profil['kilo'] ?>" data-original="<?= $profil['kilo'] ?>" placeholder="75.5" required min="30" max="300">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <label>Doğum Tarihi</label>
                                <div class="input-wrapper">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <input type="date" name="dogum_tarihi" value="<?= $profil['dogum_tarihi'] ?>" required max="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Cinsiyet</label>
                                <div class="input-wrapper">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/><path d="M12 15v7"/><path d="M9 19h6"/><path d="M15 5l-6 6"/><path d="M15 11l-6 -6"/></svg>
                                    <select name="cinsiyet" required>
                                        <option value="erkek" <?= $profil['cinsiyet'] === 'erkek' ? 'selected' : '' ?>>Erkek</option>
                                        <option value="kadin" <?= $profil['cinsiyet'] === 'kadin' ? 'selected' : '' ?>>Kadın</option>
                                    </select>
                                </div>
                            </div>
                        </div>



                        <button type="submit" class="btn-profile-submit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Profili Güncelle
                        </button>
                    </form>
                </div>

                <!-- Theme Tab -->
                <div id="tab-theme" class="profile-tab-content">
                    <div class="profile-content-header">
                        <h3>Tema Ayarları</h3>
                        <p>Görünüm tercihlerinizi özelleştirin.</p>
                    </div>

                    <div style="padding: 32px 0;">
                        <div class="form-section-title">Tema Seçimi</div>

                        <div class="theme-options">
                            <div class="theme-option light" data-theme="light" onclick="selectTheme('light')">
                                <div class="theme-preview">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                                </div>
                                <h4>Aydınlık</h4>
                                <p>Gündüz kullanım için ideal</p>
                            </div>
                            <div class="theme-option dark" data-theme="dark" onclick="selectTheme('dark')">
                                <div class="theme-preview">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                                </div>
                                <h4>Karanlık</h4>
                                <p>Gece kullanım için ideal</p>
                            </div>
                            <div class="theme-option auto" data-theme="auto" onclick="selectTheme('auto')">
                                <div class="theme-preview">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                                </div>
                                <h4>Otomatik</h4>
                                <p>Sistem ayarını takip eder</p>
                            </div>
                        </div>

                        <div class="form-info-card" style="margin-top: 24px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <p>Tema tercihiniz tarayıcınızda saklanır ve bir sonraki girişinizde otomatik olarak uygulanır.</p>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div id="tab-security" class="profile-tab-content">
                    <div class="profile-content-header">
                        <h3>Güvenlik</h3>
                        <p>Hesap güvenlik ayarlarınızı yönetin.</p>
                    </div>

                    <div style="padding: 32px 0;">
                        <!-- Change Password -->
                        <div class="security-section">
                            <div class="security-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Şifre Değiştir
                            </div>
                            <p class="security-description">Şifrenizi düzenli olarak değiştirmeniz önerilir.</p>

                            <form id="passwordForm" onsubmit="changePassword(event)">
                                <input type="password" id="currentPassword" class="security-input" placeholder="Mevcut Şifre" required>
                                 <div class="password-strength">
                                    <div class="password-strength-bar" id="strength1"></div>
                                    <div class="password-strength-bar" id="strength2"></div>
                                    <div class="password-strength-bar" id="strength3"></div>
                                </div>
                                <input type="password" id="newPassword" class="security-input" placeholder="Yeni Şifre" required minlength="6" oninput="checkPasswordStrength(this.value)">
                                <input type="password" id="confirmPassword" class="security-input" placeholder="Yeni Şifre (Tekrar)" required>
                                           
                                <button type="submit" class="btn-security primary">Şifreyi Değiştir</button>
                            </form>
                        </div>

                        <!-- Active Sessions -->
                        <div class="security-section">
                            <div class="security-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                Aktif Oturumlar
                            </div>
                            <p class="security-description">Şu anda kullanmakta olduğunuz oturum dahil tüm aktif oturumlarınız:</p>

                            <div class="session-item session-current">
                                <div class="session-info">
                                    <h4>Mevcut Oturum</h4>
                                    <p><?= $_SERVER['HTTP_USER_AGENT'] ?? 'Bilinmiyor' ?> • <?= date('d M Y, H:i') ?></p>
                                </div>
                                <span style="font-size: 12px; padding: 4px 10px; background: var(--success); color: white; border-radius: 6px; font-weight: 600;">Aktif</span>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        <div class="security-section">
                            <div class="security-title" style="color: var(--danger);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Tehlike Bölgesi
                            </div>
                            <p class="security-description">Bu işlemler geri alınamaz. Dikkatli olun.</p>

                            <button class="btn-security" style="color: var(--danger); border: 1px solid var(--danger);" onclick="deleteAllData()">
                                Tüm Verilerimi Sil
                            </button>
                        </div>
                    </div>
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
        <a href="analytics.php" class="bottom-nav-item" data-page="analytics">
            <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            <span class="bottom-nav-label">Analiz</span>
        </a>
        <a href="profil.php" class="bottom-nav-item active" data-page="profile">
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

        // Get current theme from app.js state
        function getCurrentAppTheme() {
            return html.getAttribute('data-theme') || 'light';
        }

        // Tab Navigation
        document.querySelectorAll('.profile-nav-item').forEach(item => {
            item.addEventListener('click', () => {
                const tabId = item.dataset.tab;

                // Update nav
                document.querySelectorAll('.profile-nav-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');

                // Update content
                document.querySelectorAll('.profile-tab-content').forEach(c => c.classList.remove('active'));
                document.getElementById(`tab-${tabId}`).classList.add('active');
            });
        });

        // Profile Update
        async function updateProfile(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const weightInput = form.querySelector('input[name="kilo"]');
            const currentWeight = parseFloat(weightInput.dataset.original) || 0;
            const newWeight = parseFloat(data.kilo) || 0;
            
            let weightUpdateType = null;
            if (currentWeight > 0 && newWeight !== currentWeight) {
                const result = await Dialog.show({
                    title: 'Kilo Güncelleme',
                    message: `Kilo değişikliği algılandı (${currentWeight} -> ${newWeight} kg). Bu işlem bir yanlışlığı düzeltmek için miydi yoksa yeni bir kilo kaydı mı?`,
                    buttons: [
                        { text: 'Yeni Kilom / İlerleme', class: 'dialog-btn-primary' },
                        { text: 'Sadece Bir Yanlışı Düzelttim', class: 'dialog-btn-secondary' }
                    ]
                });
                
                if (result.index === -1) return; // Cancelled
                weightUpdateType = result.index === 0 ? 'progress' : 'correction';
            }

            const btn = form.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Güncelleniyor...';
            btn.disabled = true;

            try {
                await api(`${API_BASE}/profile.php`, {
                    method: 'POST',
                    body: JSON.stringify({ ...data, weight_update_type: weightUpdateType })
                });
                toast('Profil başarıyla güncellendi!', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (err) {
                toast(err.message, 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // Theme Selection
        function selectTheme(theme) {
            // Save the preference
            localStorage.setItem('preferredTheme', theme);

            // Apply the theme
            const themeToApply = theme === 'auto' ? getAutoTheme() : theme;
            setTheme(themeToApply);

            // Update UI
            updateThemeSelectionUI(theme);

            toast(`Tema ${getThemeName(theme)} olarak ayarlandı`, 'success');
        }

        function updateThemeSelectionUI(theme) {
            document.querySelectorAll('.theme-option').forEach(o => o.classList.remove('active'));
            document.querySelector(`.theme-option[data-theme="${theme}"]`)?.classList.add('active');
        }

        function getAutoTheme() {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        function getThemeName(theme) {
            const names = { light: 'Aydınlık', dark: 'Karanlık', auto: 'Otomatik' };
            return names[theme] || theme;
        }

        // Get current theme preference (or detect from current state)
        function getCurrentThemePreference() {
            const saved = localStorage.getItem('preferredTheme');
            if (saved) return saved;

            // If no saved preference, use current theme from app
            return getCurrentAppTheme();
        }

        // Initialize theme selection based on saved preference or current state
        const savedTheme = getCurrentThemePreference();
        updateThemeSelectionUI(savedTheme);

        // Listen for system theme changes when in auto mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('preferredTheme') === 'auto') {
                setTheme(e.matches ? 'dark' : 'light');
            }
        });

        // Listen for theme changes from navbar toggle and update profile UI
        window.addEventListener('themeChanged', (e) => {
            const newTheme = e.detail.theme;
            updateThemeSelectionUI(newTheme);
        });

        // Password Strength
        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[A-Z]/.test(password) && /[0-9]/.test(password)) strength++;

            document.getElementById('strength1').classList.toggle('active', strength >= 1);
            document.getElementById('strength1').className = 'password-strength-bar active ' + (strength === 1 ? 'weak' : strength === 2 ? 'medium' : 'strong');
            document.getElementById('strength2').classList.toggle('active', strength >= 2);
            document.getElementById('strength2').className = 'password-strength-bar active ' + (strength === 2 ? 'medium' : 'strong');
            document.getElementById('strength3').classList.toggle('active', strength >= 3);
            document.getElementById('strength3').className = 'password-strength-bar active strong';
        }

        // Change Password
        async function changePassword(e) {
            e.preventDefault();

            const currentPass = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;

            if (newPass !== confirmPass) {
                toast('Şifreler eşleşmiyor', 'error');
                return;
            }

            if (newPass.length < 6) {
                toast('Şifre en az 6 karakter olmalı', 'error');
                return;
            }

            try {
                await api('/api/profile.php', {
                    method: 'PUT',
                    body: JSON.stringify({
                        current_password: currentPass,
                        new_password: newPass
                    })
                });

                toast('Şifre başarıyla değiştirildi!', 'success');
                document.getElementById('passwordForm').reset();
                document.querySelectorAll('.password-strength-bar').forEach(b => b.classList.remove('active'));
            } catch (err) {
                toast(err.message, 'error');
            }
        }

        // Delete All Data
        async function deleteAllData() {
            if (!confirm('Tüm verileriniz silinecek ve bu işlem geri alınamaz. Devam etmek istediğinize emin misiniz?')) return;
            if (!confirm('Bu işlem tüm alışkanlık kayıtlarınızı, geçmişinizi ve profil bilgilerinizi silecektir. Son şansınız: İptal etmek için "İptal" butonuna basın.')) return;

            try {
                await api('/api/profile.php', {
                    method: 'DELETE'
                });

                toast('Tüm verileriniz silindi', 'success');
                setTimeout(() => window.location.href = '/logout.php', 1500);
            } catch (err) {
                toast(err.message, 'error');
            }
        }
    </script>
</body>
</html>
