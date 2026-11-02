<?php
require_once 'includes/functions.php';

if (oturumKontrol()) {
    $basePath = dirname($_SERVER['SCRIPT_NAME']) === '/dailytor' ? '/dailytor/' : '/';
    header('Location: ' . $basePath . 'dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dailytor - Kayıt Ol</title>
    <link rel="icon" type="image/x-icon" href="img/dailytor.ico">
    <link rel="stylesheet" href="css/style.css?v=8">
    <style>
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .form-row .form-group {
            margin-bottom: 0;
        }
        select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 15px;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 16px 0 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }
        .optional {
            font-size: 12px;
            color: var(--text-tertiary);
            font-weight: normal;
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="logo">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="3" stroke-dasharray="8 4"/>
                <circle cx="24" cy="24" r="12" fill="currentColor"/>
            </svg>
            <h1>Dailytor</h1>
        </div>

        <div class="auth-box" style="max-width: 500px;">
            <h2>Kayıt Ol</h2>

            <form id="registerForm" class="auth-form active">
                <div class="section-title">Hesap Bilgileri</div>

                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="kullanici_adi" minlength="3" maxlength="50" required autocomplete="username">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Şifre</label>
                        <input type="password" name="sifre" minlength="6" required autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label>Şifre Tekrar</label>
                        <input type="password" name="sifre_tekrar" minlength="6" required autocomplete="new-password">
                    </div>
                </div>

                <div class="section-title">Vücut Bilgileri <span class="optional">(İsteğe bağlı)</span></div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Boy (cm)</label>
                        <input type="number" name="boy" min="100" max="250" placeholder="175">
                    </div>
                    <div class="form-group">
                        <label>Kilo (kg)</label>
                        <input type="number" name="kilo" min="30" max="300" step="0.1" placeholder="70">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Doğum Tarihi</label>
                        <input type="date" name="dogum_tarihi" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Cinsiyet</label>
                        <select name="cinsiyet">
                            <option value="erkek">Erkek</option>
                            <option value="kadin">Kadın</option>
                        </select>
                    </div>
                </div>



                <button type="submit" class="btn btn-primary">Kayıt Ol</button>
            </form>

            <div class="auth-footer">
                Zaten hesabın var mı? <a href="index.php">Giriş Yap</a>
            </div>

            <div id="authMessage" class="message"></div>
        </div>

        <button id="themeToggle" class="theme-toggle">
            <svg class="sun-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
            </svg>
            <svg class="moon-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
            </svg>
        </button>
    </div>

    <script src="js/app.js?v=8"></script>
    <script>
        const DASHBOARD_PATH = window.location.pathname.startsWith('/dailytor') ? '/dailytor/dashboard.php' : '/dashboard.php';

        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            if (data.sifre !== data.sifre_tekrar) {
                const msg = document.getElementById('authMessage');
                msg.textContent = 'Şifreler eşleşmiyor';
                msg.className = 'message error';
                msg.style.display = 'block';
                return;
            }

            delete data.sifre_tekrar;

            try {
                const res = await fetch(`${API_BASE}/auth.php?aksiyon=kayit`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });

                const result = await res.json();

                if (!res.ok) {
                    throw new Error(result.hata || 'Kayıt başarısız');
                }

                window.location.href = DASHBOARD_PATH;
            } catch (err) {
                const msg = document.getElementById('authMessage');
                msg.textContent = err.message;
                msg.className = 'message error';
                msg.style.display = 'block';
            }
        });

        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        function setTheme(t) {
            html.setAttribute('data-theme', t);
            localStorage.setItem('theme', t);
        }

        setTheme(localStorage.getItem('theme') || 'light');

        themeToggle.addEventListener('click', () => {
            const current = localStorage.getItem('theme') || 'light';
            setTheme(current === 'light' ? 'dark' : 'light');
        });
    </script>
</body>
</html>
