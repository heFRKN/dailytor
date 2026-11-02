// API Base URL - Dynamic for both localhost and hosting
// Method 1: Check if /dailytor is in the path
let API_BASE = '/api';

// Dialog System
const Dialog = {
    show: function(options) {
        const { title, message, input, buttons } = options;
        const overlay = document.createElement('div');
        overlay.className = 'dialog-overlay';
        let inputHtml = '';
        if (input) {
            inputHtml = `<input type="${input.type || 'text'}" class="dialog-input" placeholder="${input.placeholder || ''}" value="${input.value || ''}" id="dialogInput">`;
        }
        const buttonsHtml = (buttons || []).map((btn, i) => `
            <button class="dialog-btn ${btn.class || 'dialog-btn-secondary'}" data-index="${i}">
                ${btn.icon || ''}
                ${btn.text}
            </button>
        `).join('');
        overlay.innerHTML = `
            <div class="dialog-container">
                <div class="dialog-title">${title}</div>
                <div class="dialog-message">${message}</div>
                ${inputHtml}
                <div class="dialog-buttons">${buttonsHtml}</div>
            </div>
        `;
        document.body.appendChild(overlay);
        overlay.offsetWidth;
        overlay.classList.add('active');
        document.body.classList.add('modal-open');
        if (input) {
            setTimeout(() => (document.getElementById('dialogInput') || {}).focus && document.getElementById('dialogInput').focus(), 100);
        } else {
            const primaryBtn = overlay.querySelector('.dialog-btn-primary') || overlay.querySelector('.dialog-btn');
            if (primaryBtn) setTimeout(() => primaryBtn.focus(), 100);
        }
        return new Promise((resolve) => {
            overlay.querySelectorAll('.dialog-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const index = parseInt(btn.dataset.index);
                    const value = input ? document.getElementById('dialogInput').value : null;
                    overlay.classList.remove('active');
                    document.body.classList.remove('modal-open');
                    setTimeout(() => overlay.remove(), 200);
                    resolve({ index, value });
                });
            });
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    overlay.classList.remove('active');
                    document.body.classList.remove('modal-open');
                    setTimeout(() => overlay.remove(), 200);
                    resolve({ index: -1 });
                }
            });
        });
    },
    confirm: function(title, message, primaryText = 'Tamam', secondaryText = 'İptal') {
        return this.show({
            title,
            message,
            buttons: [
                { text: primaryText, class: 'dialog-btn-primary' },
                { text: secondaryText, class: 'dialog-btn-secondary' }
            ]
        }).then(res => res.index === 0);
    }
};
window.Dialog = Dialog;
const pathCheck = window.location.pathname;

// Method 2: Check script src to detect base path
const scripts = document.getElementsByTagName('script');
for (let i = 0; i < scripts.length; i++) {
    const src = scripts[i].src;
    if (src && src.includes('/js/app.js')) {
        if (src.includes('/dailytor/js/app.js')) {
            API_BASE = '/dailytor/api';
        }
        break;
    }
}

console.log('Path check:', pathCheck, 'API_BASE:', API_BASE);

// Theme Management
const themeToggle = document.getElementById('themeToggle');
const html = document.documentElement;

function setTheme(theme) {
    html.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);

    // Dispatch event for other components to listen
    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
}

function getTheme() {
    const savedTheme = localStorage.getItem('theme');

    // If saved theme exists, use it
    if (savedTheme) return savedTheme;

    // Check if user has a preferred theme setting
    const preferred = localStorage.getItem('preferredTheme');
    if (preferred === 'auto') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    return preferred || 'light';
}

// Initialize theme
setTheme(getTheme());

// Update preferredTheme when using toggle (breaks out of auto mode)
themeToggle && themeToggle.addEventListener('click', () => {
    const current = getTheme();
    const newTheme = current === 'light' ? 'dark' : 'light';
    setTheme(newTheme);

    // If user manually toggles, save as explicit preference (not auto)
    localStorage.setItem('preferredTheme', newTheme);
});

// API Helper
async function api(url, options = {}) {
    const headers = {
        'Content-Type': 'application/json',
        ...options.headers
    };

    const response = await fetch(url, { ...options, headers });

    if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        throw new Error(data.hata || 'İstek başarısız');
    }

    return response.json();
}

// Notes Helper
async function saveDailyNote() {
    const noteEl = document.getElementById('dailyNoteInput');
    const tarih = (document.getElementById('historyDate') || {}).value || new Date().toISOString().split('T')[0];
    const text = noteEl.value;

    try {
        await api(`${API_BASE}/notes.php`, {
            method: 'POST',
            body: JSON.stringify({ tarih, not: text })
        });
        toast('Not kaydedildi', 'success');
    } catch (err) {
        toast('Not kaydedilemedi', 'error');
    }
}

async function loadDailyNote(tarih) {
    const noteEl = document.getElementById('dailyNoteInput');
    if (!noteEl) return;

    try {
        const data = await api(`${API_BASE}/notes.php?tarih=${tarih}`);
        noteEl.value = data.not || '';
    } catch (err) {
        noteEl.value = '';
    }
}

// Toast Notification
function toast(message, type = 'success') {
    const toastEl = document.getElementById('toast');
    if (!toastEl) return;

    toastEl.textContent = message;
    toastEl.className = `toast ${type}`;
    toastEl.classList.add('show');

    setTimeout(() => {
        toastEl.classList.remove('show');
    }, 3000);
}

// Auth Forms
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const authMessage = document.getElementById('authMessage');
const tabBtns = document.querySelectorAll('.tab-btn');

tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;

        tabBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));

        if (tab === 'login') {
            loginForm && loginForm.classList.add('active');
        } else {
            registerForm && registerForm.classList.add('active');
        }

        authMessage.style.display = 'none';
    });
});

loginForm && loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(loginForm);
    const data = Object.fromEntries(formData);

    try {
        authMessage.style.display = 'none';

        await api(`${API_BASE}/auth.php?aksiyon=giris`, {
            method: 'POST',
            body: JSON.stringify(data)
        });

        window.location.href = window.location.pathname.includes('/dailytor') ? '/dailytor/dashboard.php' : '/dashboard.php';
    } catch (err) {
        authMessage.textContent = err.message;
        authMessage.className = 'message error';
        authMessage.style.display = 'block';
    }
});

// Dashboard
const habitsContainer = document.getElementById('habitsContainer');
const emptyState = document.getElementById('emptyState');
const progressRing = document.getElementById('progressRing');
const progressPercent = document.getElementById('progressPercent');
const habitModal = document.getElementById('habitModal');
const habitForm = document.getElementById('habitForm');
const addHabitBtn = document.getElementById('addHabitBtn');
const addFirstHabitBtn = document.getElementById('addFirstHabitBtn');
const modalClose = document.querySelector('.modal-close');

// Habit type selection
const habitTypeCards = document.querySelectorAll('.habit-type-card-mini');
const numericFields = document.getElementById('numericFields');
const uykuFields = document.getElementById('uykuFields');
const adimFields = document.getElementById('adimFields');
const exerciseFields = document.getElementById('exerciseFields');
const disKosuFields = document.getElementById('disKosuFields');
const adetFields = document.getElementById('adetFields');
const plankFields = document.getElementById('plankFields');
const infoFields = document.getElementById('infoFields');
const infoText = document.getElementById('infoText');
const tipInput = document.querySelector('input[name="tip"]');

// Info text for exercise types
const infoTexts = {
    'bisiklet': '🚴 Bisiklet süresi girin, tamamlandı sayılır.',
    'agirlik': '💪 Ağırlık antrenmanı süresi girin, tamamlandı sayılır.'
};

habitTypeCards.forEach(card => {
    card.addEventListener('click', () => {
        habitTypeCards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        tipInput.value = card.dataset.type;

        // Hide all type fields
        numericFields.style.display = 'none';
        uykuFields.style.display = 'none';
        adimFields.style.display = 'none';
        exerciseFields.style.display = 'none';
        disKosuFields.style.display = 'none';
        adetFields.style.display = 'none';
        plankFields.style.display = 'none';
        infoFields.style.display = 'none';

        const type = card.dataset.type;
        if (type === 'numeric') {
            numericFields.style.display = 'block';
        } else if (type === 'uyku') {
            uykuFields.style.display = 'block';
        } else if (type === 'adim') {
            adimFields.style.display = 'block';
        } else if (type === 'kosu_bandi') {
            exerciseFields.style.display = 'block';
        } else if (type === 'dis_kosu') {
            disKosuFields.style.display = 'block';
        } else if (['snav', 'mekik', 'barfiks', 'muscle_up'].includes(type)) {
            adetFields.style.display = 'block';
        } else if (type === 'plank') {
            plankFields.style.display = 'block';
        } else if (['bisiklet', 'agirlik'].includes(type)) {
            infoFields.style.display = 'block';
            if (infoText) infoText.textContent = infoTexts[type] || 'Girdiğiniz süre kadar yapmanız yeterli.';
        }
    });
});

window.fixedFoodHabitId = null;

async function loadHabits() {
    const today = new Date().toISOString().split('T')[0];

    try {
        const data = await api(`${API_BASE}/records.php?tarih=${today}`);
        console.log('Yüklenen alışkanlıklar:', data);
        window.allHabits = data.kayitlar;
        if (data.yemek_kaydi) {
            window.fixedFoodHabitId = data.yemek_kaydi.aliskanlik_id;
            renderFixedFoodSection(data.yemek_kaydi);
        } else {
            const fixedSect = document.getElementById('fixedFoodSection');
            if (fixedSect) fixedSect.innerHTML = '';
        }
        renderHabits(data.kayitlar);
        updateProgress(data.tamamlanma_yuzdesi);
        updateCalorieBalance(data.kalori_dengesi);
        loadDailyNote(today);
    } catch (err) {
        console.error('Alışkanlık yükleme hatası:', err);
        habitsContainer.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--danger);">Hata: ${err.message}</div>`;
    }
}

async function loadHistory(date) {
    try {
        const data = await api(`${API_BASE}/records.php?tarih=${date}`);
        if (data.yemek_kaydi) {
            window.fixedFoodHabitId = data.yemek_kaydi.aliskanlik_id;
            renderFixedFoodSection(data.yemek_kaydi);
        } else {
            const fixedSect = document.getElementById('fixedFoodSection');
            if (fixedSect) fixedSect.innerHTML = '';
        }
        renderHabits(data.kayitlar);
        updateProgress(data.tamamlanma_yuzdesi);
        updateCalorieBalance(data.kalori_dengesi);
        loadDailyNote(date);
    } catch (err) {
        console.error('Geçmiş yükleme hatası:', err);
    }
}

function renderFixedFoodSection(yemekKaydi) {
    const container = document.getElementById('fixedFoodSection');
    if (!container) return;

    let mealsHtml = '';
    if (yemekKaydi.yemekler && yemekKaydi.yemekler.length > 0) {
        mealsHtml = `
            <div class="meal-log-list" style="margin-top: 16px;">
                ${yemekKaydi.yemekler.map(meal => {
                    const imgHtml = meal.foto_yolu 
                        ? `<img class="meal-log-img" src="${API_BASE.replace('/api', '')}/${meal.foto_yolu}" alt="${meal.yemek_adi}">`
                        : `<div class="meal-log-emoji">${meal.emoji || '🍽️'}</div>`;
                    
                    const macrosText = (parseInt(meal.protein) > 0 || parseFloat(meal.karbonhidrat) > 0 || parseInt(meal.yag) > 0)
                        ? `P: ${meal.protein || 0}g | K: ${meal.karbonhidrat || 0}g | Y: ${meal.yag || 0}g`
                        : '';
                    
                    return `
                        <div class="meal-log-item">
                            <div class="meal-log-left">
                                ${imgHtml}
                                <div class="meal-log-info">
                                    <div class="meal-log-name">${meal.yemek_adi}</div>
                                    ${macrosText ? `<div class="meal-log-macros">${macrosText}</div>` : ''}
                                </div>
                            </div>
                            <div class="meal-log-right">
                                <div class="meal-log-calories">${meal.kalori} kcal</div>
                                <button class="meal-log-delete-btn" onclick="deleteMeal(${meal.id}, ${yemekKaydi.aliskanlik_id})" title="Yemeği Sil">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    } else {
        mealsHtml = `
            <div style="text-align: center; color: var(--text-secondary); padding: 20px 0; font-size: 14px;">
                Henüz bugüne ait yemek kaydı yok. "Manuel Ekle" diyerek veya "Fotoğraf Yükle" diyerek yapay zekaya analiz ettirebilirsiniz!
            </div>
        `;
    }

    container.innerHTML = `
        <div class="fixed-food-card">
            <div class="fixed-food-header">
                <div class="fixed-food-title-area">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path>
                        <path d="M7 2v20"></path>
                        <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path>
                    </svg>
                    <div>
                        <h2>Günlük Yemek</h2>
                        <div class="fixed-food-subtitle">Toplam ${yemekKaydi.deger || 0} kcal alındı</div>
                    </div>
                </div>
                
                <div class="fixed-food-actions">
                    <button class="btn btn-primary food-camera-btn" onclick="triggerFoodCamera(${yemekKaydi.aliskanlik_id})">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                        <span>Yapay Zekayla Analiz Et</span>
                    </button>
                    <input type="file" accept="image/*" capture="environment" id="foodCameraInput-${yemekKaydi.aliskanlik_id}" style="display:none;" onchange="handleFoodCameraUpload(event, ${yemekKaydi.aliskanlik_id})">
                    
                    <button class="meal-add-manual-btn-fixed" onclick="openAddMealModal(${yemekKaydi.aliskanlik_id})" title="Manuel Yemek Ekle">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Manuel Ekle</span>
                    </button>
                </div>
            </div>
            ${mealsHtml}
        </div>
    `;
}

function renderHabits(habits) {
    console.log('renderHabits çağrıldı, kayıt sayısı:', (habits ? habits.length : 0));
    console.log('habitsContainer elementi:', habitsContainer);
    console.log('emptyState elementi:', emptyState);

    if (!habits || habits.length === 0) {
        console.log('Hiç alışkanlık yok, empty state gösteriliyor');
        habitsContainer.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    console.log('Alışkanlıklar render ediliyor...');
    habitsContainer.style.display = 'grid';
    emptyState.style.display = 'none';

    const html = habits.map(h => createHabitCard(h)).join('');
    console.log('Oluşturulan HTML uzunluğu:', html.length);
    habitsContainer.innerHTML = html;
    attachHabitListeners();
    console.log('Alışkanlıklar render edildi');
}

function createHabitCard(habit) {
    const isCompleted = habit.tamamlandi;
    let content = '';
    let progressBar = '';

    switch (habit.tip) {
        case 'checkbox':
            content = `
                <div class="checkbox-wrapper" onclick="toggleHabit(${habit.aliskanlik_id}, ${habit.tamamlandi})">
                    <div class="custom-checkbox">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                    </div>
                    <span class="habit-status">${isCompleted ? 'Tamamlandı' : 'Bekliyor'}</span>
                </div>
            `;
            break;

        case 'numeric':
            const progressWidth = habit.hedef_deger ? Math.min((habit.deger / habit.hedef_deger) * 100, 100) : 0;
            progressBar = `
                <div class="habit-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${progressWidth}%; background-color: ${habit.renk}"></div>
                    </div>
                </div>
            `;
            content = `
                <div class="habit-value-input">
                    <input type="number" value="${habit.deger || 0}" min="0" id="habitInput-${habit.aliskanlik_id}"
                        onchange="updateNumericHabit(${habit.aliskanlik_id}, this.value)"
                        data-hedef="${habit.hedef_deger}">
                    <button class="add-value-btn" onclick="addValue(${habit.aliskanlik_id})" title="Üzerine Ekle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                    <span>${habit.birim || ''} / ${habit.hedef_deger || '-'} ${habit.birim || ''}</span>
                </div>
            `;
            break;



        case 'uyku':
            const hours = Math.floor((habit.deger || 0) / 60);
            const mins = (habit.deger || 0) % 60;
            const targetHours = Math.floor((habit.hedef_deger || 480) / 60);
            const targetMins = (habit.hedef_deger || 480) % 60;
            const targetText = `${targetHours}s${targetMins > 0 ? ' ' + targetMins + 'd' : ''}`;
            content = `
                <div class="exercise-inputs" data-sleep-id="${habit.aliskanlik_id}">
                    <div class="habit-value-input">
                        <input type="number" value="${hours}" min="0" max="24" class="sleep-hours"
                            onchange="handleSleepChange(${habit.aliskanlik_id})">
                        <span>saat</span>
                    </div>
                    <div class="habit-value-input">
                        <input type="number" value="${mins}" min="0" max="59" class="sleep-mins"
                            onchange="handleSleepChange(${habit.aliskanlik_id})">
                        <span>dk (Hedef: ${targetText})</span>
                    </div>
                </div>
            `;
            break;

        case 'adim':
            content = `
                <div class="habit-value-input">
                    <input type="number" value="${habit.deger || 0}" min="0" id="habitInput-${habit.aliskanlik_id}"
                        onchange="updateNumericHabit(${habit.aliskanlik_id}, this.value)">
                    <button class="add-value-btn" onclick="addValue(${habit.aliskanlik_id})" title="Adım Ekle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                    <span>adım / ${habit.hedef_deger || 10000}</span>
                </div>
            `;
            break;

        case 'kosu_bandi':
            content = `
                <div class="exercise-inputs">
                    <div class="habit-value-input">
                        <input type="number" value="${habit.deger3 || 5}" step="0.1" min="1" max="20"
                            onchange="updateExerciseHabit(${habit.aliskanlik_id}, 'hiz', this.value)">
                        <span>km/s</span>
                    </div>
                    <div class="habit-value-input">
                        <input type="number" value="${habit.deger4 || 0}" min="0" max="15"
                            onchange="updateExerciseHabit(${habit.aliskanlik_id}, 'egim', this.value)">
                        <span>% eğim</span>
                    </div>
                    <div class="habit-value-input">
                        <input type="number" value="${habit.deger2 || 0}" min="0" max="180"
                            onchange="updateExerciseHabit(${habit.aliskanlik_id}, 'sure', this.value)">
                        <span>dakika</span>
                    </div>
                    ${habit.yakilan_kalori ? `<div style="font-size:12px;color:var(--text-secondary);margin-top:8px;">≈ ${habit.yakilan_kalori} kcal yakıldı</div>` : ''}
                </div>
            `;
            break;

        case 'bisiklet':
            content = `
                <div class="habit-value-input">
                    <input type="number" value="${habit.deger2 || 0}" min="0" max="180"
                        onchange="updateExerciseHabit(${habit.aliskanlik_id}, 'sure', this.value)">
                    <span>dakika</span>
                </div>
                ${habit.yakilan_kalori ? `<div style="font-size:12px;color:var(--text-secondary);margin-top:8px;">≈ ${habit.yakilan_kalori} kcal yakıldı</div>` : ''}
            `;
            break;

        case 'agirlik':
            content = `
                <div class="habit-value-input">
                    <input type="number" value="${habit.deger2 || 0}" min="0" max="180"
                        onchange="updateExerciseHabit(${habit.aliskanlik_id}, 'sure', this.value)">
                    <span>dakika</span>
                </div>
                ${habit.yakilan_kalori ? `<div style="font-size:12px;color:var(--text-secondary);margin-top:8px;">💪 ≈ ${habit.yakilan_kalori} kcal yakıldı</div>` : ''}
            `;
            break;

        case 'dis_kosu':
            content = `
                <div class="exercise-inputs">
                    <div class="habit-value-input">
                        <input type="number" value="${habit.deger || habit.dis_kosu_hedef_tempo || 6}" step="0.1" min="3" max="15"
                            onchange="updateDisKosuHabit(${habit.aliskanlik_id}, 'tempo', this.value)">
                        <span>dk/km</span>
                    </div>
                    <div class="habit-value-input">
                        <input type="number" value="${habit.deger2 || 0}" min="0" max="180"
                            onchange="updateDisKosuHabit(${habit.aliskanlik_id}, 'sure', this.value)">
                        <span>dakika</span>
                    </div>
                </div>
                ${habit.yakilan_kalori ? `<div style="font-size:12px;color:var(--text-secondary);margin-top:8px;">🏃 ≈ ${habit.yakilan_kalori} kcal yakıldı</div>` : ''}
            `;
            break;

        case 'snav':
        case 'mekik':
        case 'barfiks':
        case 'muscle_up':
            const egzersizAd = habit.tip === 'snav' ? 'Şınav' : habit.tip === 'mekik' ? 'Mekik' : habit.tip === 'barfiks' ? 'Barfiks' : 'Muscle Up';
            content = `
                <div class="habit-value-input">
                    <input type="number" value="${habit.deger || 0}" min="0" id="habitInput-${habit.aliskanlik_id}"
                        onchange="updateNumericHabit(${habit.aliskanlik_id}, this.value)">
                    <button class="add-value-btn" onclick="addValue(${habit.aliskanlik_id})" title="Üzerine Ekle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                    <span>adet / ${habit.hedef_deger || '-'}</span>
                </div>
                ${habit.yakilan_kalori ? `<div style="font-size:12px;color:var(--text-secondary);margin-top:8px;">💪 ≈ ${habit.yakilan_kalori} kcal yakıldı</div>` : ''}
            `;
            break;

        case 'plank':
            const plankSeconds = habit.deger || 0;
            const plankMins = Math.floor(plankSeconds / 60);
            const plankSecs = plankSeconds % 60;
            content = `
                <div class="exercise-inputs">
                    <div class="habit-value-input">
                        <input type="number" value="${plankMins}" min="0" max="60" id="plankMins-${habit.aliskanlik_id}"
                            onchange="updatePlankHabit(${habit.aliskanlik_id})">
                        <span>dk</span>
                    </div>
                    <div class="habit-value-input">
                        <input type="number" value="${plankSecs}" min="0" max="59" id="plankSecs-${habit.aliskanlik_id}"
                            onchange="updatePlankHabit(${habit.aliskanlik_id})">
                        <span>sn / ${habit.hedef_deger || 60}sn</span>
                    </div>
                </div>
                ${habit.yakilan_kalori ? `<div style="font-size:12px;color:var(--text-secondary);margin-top:8px;">🧱 ≈ ${habit.yakilan_kalori} kcal yakıldı</div>` : ''}
            `;
            break;

        default:
            content = `
                <div class="habit-value-input">
                    <input type="number" value="${habit.deger || 0}" min="0"
                        onchange="updateNumericHabit(${habit.aliskanlik_id}, this.value)">
                    <span>${habit.birim || ''}</span>
                </div>
            `;
            break;
    }

    return `
        <div class="habit-card ${isCompleted ? 'completed' : ''}" data-id="${habit.aliskanlik_id}" draggable="true" ondragstart="handleDragStart(event, ${habit.aliskanlik_id})" ondragover="handleDragOver(event)" ondrop="handleDrop(event, ${habit.aliskanlik_id})">
            <div class="habit-header" style="display: flex; align-items: center; gap: 8px;">
                <div class="drag-handle" style="cursor: grab; color: var(--text-secondary); opacity: 0.5;" title="Sürükleyip Sırasını Değiştir">⋮⋮</div>
                <span class="habit-name" style="color: ${habit.renk}">${habit.ad}</span>
                <div class="habit-actions" style="margin-left: auto; display: flex; gap: 4px;">
                    <button class="habit-btn" onclick="openEditHabit(${habit.aliskanlik_id})" title="Düzenle">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="habit-btn" onclick="deleteHabit(${habit.aliskanlik_id})" title="Sil">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>
            ${progressBar}
            ${content}
        </div>
    `;
}

window.addValue = async (habitId) => {
    const input = document.getElementById(`habitInput-${habitId}`);
    const current = parseFloat(input.value) || 0;
    
    const result = await Dialog.show({
        title: 'Değer Ekle',
        message: 'Mevcut değerin üzerine eklemek istediğiniz miktarı girin.',
        input: { type: 'number', placeholder: 'Örn: 250' },
        buttons: [
            { text: 'Değer Ekle', class: 'dialog-btn-primary' },
            { text: 'İptal', class: 'dialog-btn-secondary' }
        ]
    });

    if (result.index === 0 && result.value) {
        const add = parseFloat(result.value);
        if (add > 0) {
            const newValue = current + add;
            input.value = newValue;
            window.updateNumericHabit(habitId, newValue);
        }
    }
};

function attachHabitListeners() {}

function updateProgress(percent) {
    progressPercent.textContent = Math.round(percent);
    const offset = 226.2 - (226.2 * percent / 100);
    progressRing.style.strokeDashoffset = offset;
}

function updateCalorieBalance(balance) {
    const balanceEl = document.getElementById('calorieBalance');
    const valueEl = document.getElementById('calorieValue');
    const exerciseEl = document.getElementById('caloriesExercise');
    const completedEl = document.getElementById('completedCount');

    if (!balance) {
        if (valueEl) valueEl.textContent = '--';
        if (exerciseEl) exerciseEl.textContent = '--';
        return;
    }

    const fark = balance.fark || 0;

    if (valueEl) {
        valueEl.textContent = (fark >= 0 ? '+' : '') + fark;
    }

    if (exerciseEl) {
        exerciseEl.textContent = balance.yakilan || 0;
    }

    // Update completed count from habits
    if (completedEl && window.allHabits) {
        const completed = window.allHabits.filter(h => h.tamamlandi).length;
        completedEl.textContent = completed;
    }

    if (balanceEl) {
        if (fark > 300) {
            balanceEl.style.background = 'linear-gradient(135deg, #ef4444 0%, #f97316 100%)';
            balanceEl.style.boxShadow = '0 10px 30px -10px rgba(239, 68, 68, 0.3)';
        } else if (fark < -300) {
            balanceEl.style.background = 'linear-gradient(135deg, #10b981 0%, #22c55e 100%)';
            balanceEl.style.boxShadow = '0 10px 30px -10px rgba(16, 185, 129, 0.3)';
        } else {
            balanceEl.style.background = 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)';
            balanceEl.style.boxShadow = '0 10px 30px -10px rgba(79, 70, 229, 0.3)';
        }
    }
}

window.toggleHabit = async (habitId, currentStatus) => {
    console.log('toggleHabit called:', habitId, currentStatus);
    const today = new Date().toISOString().split('T')[0];

    try {
        const data = await api(`${API_BASE}/records.php`, {
            method: 'POST',
            body: JSON.stringify({
                aliskanlik_id: habitId,
                tarih: today,
                tamamlandi: !currentStatus
            })
        });

        console.log('Toggle response:', data);
        updateProgress(data.tamamlanma_yuzdesi);
        updateCalorieBalance(data.kalori_dengesi);
        await loadHabits();
    } catch (err) {
        console.error('Toggle error:', err);
        toast(err.message, 'error');
    }
};

window.updateNumericHabit = async (habitId, value) => {
    const today = new Date().toISOString().split('T')[0];

    try {
        const data = await api(`${API_BASE}/records.php`, {
            method: 'POST',
            body: JSON.stringify({
                aliskanlik_id: habitId,
                tarih: today,
                deger: parseInt(value) || 0
            })
        });

        updateProgress(data.tamamlanma_yuzdesi);
        updateCalorieBalance(data.kalori_dengesi);
        await loadHabits();
        toast('Kayıt güncellendi');
    } catch (err) {
        toast(err.message, 'error');
    }
};

window.handleSleepChange = (habitId) => {
    const container = document.querySelector(`[data-sleep-id="${habitId}"]`);
    const hours = parseInt(container.querySelector('.sleep-hours').value) || 0;
    const mins = parseInt(container.querySelector('.sleep-mins').value) || 0;
    const totalMins = (hours * 60) + mins;
    window.updateNumericHabit(habitId, totalMins);
};

window.updateExerciseHabit = async (habitId, field, value) => {
    const today = new Date().toISOString().split('T')[0];
    const card = document.querySelector(`[data-id="${habitId}"]`);
    const inputs = card.querySelectorAll('input');

    const updates = { aliskanlik_id: habitId, tarih: today };
    inputs.forEach(input => {
        if (input.onchange.toString().includes('hiz')) updates.deger3 = parseFloat(input.value);
        if (input.onchange.toString().includes('egim')) updates.deger4 = parseInt(input.value);
        if (input.onchange.toString().includes('sure')) updates.deger2 = parseInt(input.value);
    });

    updates[field === 'hiz' ? 'deger3' : field === 'egim' ? 'deger4' : 'deger2'] =
        field === 'hiz' ? parseFloat(value) : parseInt(value);

    try {
        const data = await api(`${API_BASE}/records.php`, {
            method: 'POST',
            body: JSON.stringify(updates)
        });

        updateProgress(data.tamamlanma_yuzdesi);
        updateCalorieBalance(data.kalori_dengesi);
        await loadHabits();
        toast('Kayıt güncellendi');
    } catch (err) {
        toast(err.message, 'error');
    }
};

window.updateDisKosuHabit = async (habitId, field, value) => {
    const today = new Date().toISOString().split('T')[0];
    const card = document.querySelector(`[data-id="${habitId}"]`);
    const inputs = card.querySelectorAll('input');

    const updates = { aliskanlik_id: habitId, tarih: today };
    inputs.forEach(input => {
        if (input.onchange.toString().includes('tempo')) updates.deger = parseFloat(input.value);
        if (input.onchange.toString().includes('sure')) updates.deger2 = parseInt(input.value);
    });

    updates[field === 'tempo' ? 'deger' : 'deger2'] =
        field === 'tempo' ? parseFloat(value) : parseInt(value);

    try {
        const data = await api(`${API_BASE}/records.php`, {
            method: 'POST',
            body: JSON.stringify(updates)
        });

        updateProgress(data.tamamlanma_yuzdesi);
        updateCalorieBalance(data.kalori_dengesi);
        await loadHabits();
        toast('Kayıt güncellendi');
    } catch (err) {
        toast(err.message, 'error');
    }
};

window.updatePlankHabit = async (habitId) => {
    const today = new Date().toISOString().split('T')[0];
    const mins = parseInt(document.getElementById(`plankMins-${habitId}`).value) || 0;
    const secs = parseInt(document.getElementById(`plankSecs-${habitId}`).value) || 0;
    const totalSeconds = (mins * 60) + secs;

    try {
        const data = await api(`${API_BASE}/records.php`, {
            method: 'POST',
            body: JSON.stringify({
                aliskanlik_id: habitId,
                tarih: today,
                deger: totalSeconds
            })
        });

        updateProgress(data.tamamlanma_yuzdesi);
        updateCalorieBalance(data.kalori_dengesi);
        await loadHabits();
        toast('Kayıt güncellendi');
    } catch (err) {
        toast(err.message, 'error');
    }
};

window.deleteHabit = async (habitId) => {
    const result = await Dialog.confirm('Alışkanlığı Sil', 'Bu alışkanlığı silmek istediğinize emin misiniz?');
    if (!result) return;
    try {
        await api(`${API_BASE}/habits.php?id=${habitId}`, { method: 'DELETE' });
        toast('Alışkanlık silindi');
        await loadHabits();
    } catch (err) {
        toast(err.message, 'error');
    }
};

// Global Modal Backdrop listener
document.addEventListener('mousedown', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
        if (typeof closeEditModal === 'function') closeEditModal();
        if (typeof closeHabitModal === 'function') closeHabitModal();
    }
});

// Habit Modal
// Yeni modal sistemi kullanılıyor
addHabitBtn && addHabitBtn.addEventListener('click', () => {
    if (window.openNewHabitModal) window.openNewHabitModal();
});
addFirstHabitBtn && addFirstHabitBtn.addEventListener('click', () => {
    if (window.openNewHabitModal) window.openNewHabitModal();
});
// Eski modal listener'ları - yeni modal sistemi kendi içinde yönetiyor
// Silinmedi çünkü edit modal'ı hala eski sistemi kullanıyor
modalClose && modalClose.addEventListener('click', () => {
    // Yeni modal'ı kapat
    if (window.closeNewHabitModal) window.closeNewHabitModal();
});

habitModal && habitModal.addEventListener('click', (e) => {
    if (e.target === habitModal) {
        if (window.closeNewHabitModal) window.closeNewHabitModal();
    }
});

habitForm && habitForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(habitForm);
    const data = Object.fromEntries(formData);
    
    if (!data.ad || data.ad.trim() === '') {
        toast('Lütfen alışkanlık adını girin', 'error');
        return;
    }
    
    const type = data.tip;

    // Yeni modal için input ID'leri farklı
    const isNewModal = document.getElementById('step2') !== null;

    if (type === 'checkbox') {
        delete data.hedef_deger;
        delete data.birim;
    } else if (type === 'kalori') {
        delete data.hedef_deger;
        delete data.birim;
    } else if (type === 'numeric') {
        const hedef = isNewModal ?
            (document.getElementById('settingHedef') || {}).value :
            (document.querySelector('input[name="hedef_deger"]') || {}).value;
        if (!hedef) {
            toast('Hedef değer gerekli', 'error');
            return;
        }
        data.hedef_deger = parseInt(hedef);
        const birim = isNewModal ?
            (document.getElementById('settingBirim') || {}).value :
            (document.querySelector('input[name="birim"]') || {}).value;
        data.birim = birim || '';
    } else if (type === 'uyku') {
        const saatInput = isNewModal ? 'settingSaat' : 'uykuSaat';
        const dakikaInput = isNewModal ? 'settingDakika' : 'uykuDakika';
        const saat = parseInt((document.getElementById(saatInput) || {}).value) || 0;
        const dakika = parseInt((document.getElementById(dakikaInput) || {}).value) || 0;
        data.hedef_deger = (saat * 60) + dakika;
        data.birim = "dakika";
        if (data.hedef_deger <= 0) {
            toast('Geçerli bir uyku süresi girin', 'error');
            return;
        }
    } else if (type === 'adim') {
        const adimInput = isNewModal ? 'settingAdimHedef' : 'adimHedefDeger';
        data.hedef_deger = parseInt((document.getElementById(adimInput) || {}).value) || 10000;
        data.birim = "adım";
    } else if (type === 'kosu_bandi') {
        if (isNewModal) {
            data.hiz = parseFloat((document.getElementById('settingKosuHiz') || {}).value) || 6;
            data.egim = parseInt((document.getElementById('settingKosuEgim') || {}).value) || 0;
            data.sure = parseInt((document.getElementById('settingKosuSure') || {}).value) || 30;
        } else {
            data.hiz = parseFloat((document.querySelector('input[name="hiz"]') || {}).value) || parseFloat(data.hiz) || 6;
            data.egim = parseInt((document.querySelector('input[name="egim"]') || {}).value) || parseInt(data.egim) || 0;
        }

        data.sure = 0;
        delete data.hedef_deger;
        delete data.birim;
    } else if (type === 'bisiklet') {
        data.sure = 0;
        delete data.hedef_deger;
        delete data.birim;
    } else if (type === 'agirlik') {
        data.sure = 0;
        delete data.hedef_deger;
        delete data.birim;
    } else if (type === 'dis_kosu') {
        // Yeni modal'da tempo seçimi var
        const tempoInput = isNewModal ? 'settingDiscoTempo' : null;
        const tempo = tempoInput ? (document.getElementById(tempoInput) || {}).value : 'orta';

        // Tempo değerini hesapla (dk/km cinsinden)
        if (typeof TEMPO_DEGERLERI !== 'undefined') {
            data.tempo = TEMPO_DEGERLERI[tempo] || 6.5;
        } else {
            data.tempo = parseFloat(data.tempo) || 6.5;
        }
        delete data.hedef_deger;
        delete data.birim;
    } else if (['snav', 'mekik', 'barfiks', 'muscle_up'].includes(type)) {
        const adetInput = isNewModal ? 'settingAdet' : 'adetHedefDeger';
        data.hedef_adet = parseInt((document.getElementById(adetInput) || {}).value) || 10;
        data.hedef_deger = data.hedef_adet;
        data.birim = 'adet';
    } else if (type === 'plank') {
        let plankSure;
        if (isNewModal) {
            // Yeni modal: dakika + saniye
            const dk = parseInt((document.getElementById('settingPlankDakika') || {}).value) || 0;
            const sn = parseInt((document.getElementById('settingPlankSaniye') || {}).value) || 0;
            plankSure = (dk * 60) + sn;
        } else {
            plankSure = parseInt((document.getElementById('plankSure') || {}).value) || 60;
        }
        data.plank_sure = plankSure;
        data.hedef_deger = plankSure;
        data.birim = 'saniye';
    }
    delete data.bisiklet_sure;
    delete data.agirlik_sure;

    const isEdit = !!data.edit_id;
    if (isEdit) {
        data.id = data.edit_id;
    }
    delete data.edit_id;

    try {
        await api(`${API_BASE}/habits.php`, {
            method: isEdit ? 'PUT' : 'POST',
            body: JSON.stringify(data)
        });

        // Modalı kapat
        if (isNewModal && window.closeNewHabitModal) {
            window.closeNewHabitModal();
        } else {
            habitModal.classList.remove('active');
            document.body.classList.remove('modal-open');
        }

        const modalTitle = document.querySelector('.modal-header h2');
        if (modalTitle) modalTitle.textContent = 'Yeni Alışkanlık';

        toast(isEdit ? 'Alışkanlık güncellendi' : 'Alışkanlık oluşturuldu');
        if (window.location.pathname.includes('history.php')) {
            const dateVal = document.getElementById('historyDate').value;
            await loadHistory(dateVal);
        } else {
            await loadHabits();
        }
    } catch (err) {
        toast(err.message, 'error');
    }
});

// Editing Handlers
window.openEditHabit = (habitId) => {
    const habit = window.allHabits && allHabits.find(h => h.aliskanlik_id == habitId);
    if (!habit) return;

    habitForm.reset();
    document.getElementById('editHabitId').value = habitId;
    document.querySelector('input[name="ad"]').value = habit.ad;
    if(habit.renk) document.querySelector('input[name="renk"]').value = habit.renk;
    
    habitTypeCards.forEach(c => c.classList.remove('selected'));
    const typeCard = document.querySelector(`[data-type="${habit.tip}"]`);
    if(typeCard) {
        typeCard.classList.add('selected');
        tipInput.value = habit.tip;

        numericFields.style.display = 'none';
        uykuFields.style.display = 'none';
        adimFields.style.display = 'none';
        exerciseFields.style.display = 'none';
        disKosuFields.style.display = 'none';
        adetFields.style.display = 'none';
        plankFields.style.display = 'none';
        infoFields.style.display = 'none';

        if (habit.tip === 'numeric') {
            numericFields.style.display = 'block';
            document.querySelector('input[name="hedef_deger"]').value = habit.hedef_deger || '';
            document.querySelector('input[name="birim"]').value = habit.birim || '';
        } else if (habit.tip === 'uyku') {
            uykuFields.style.display = 'block';
            document.getElementById('uykuSaat').value = Math.floor((habit.hedef_deger || 480) / 60);
            document.getElementById('uykuDakika').value = (habit.hedef_deger || 480) % 60;
        } else if (habit.tip === 'adim') {
            adimFields.style.display = 'block';
            document.getElementById('adimHedefDeger').value = habit.hedef_deger || 10000;
        } else if (habit.tip === 'kosu_bandi') {
            exerciseFields.style.display = 'block';
            document.querySelector('input[name="hiz"]').value = habit.kosu_bandi_hedef_hiz || 6;
            document.querySelector('input[name="egim"]').value = habit.kosu_bandi_hedef_egim || 0;
        } else if (habit.tip === 'dis_kosu') {
            disKosuFields.style.display = 'block';
            document.querySelector('input[name="tempo"]').value = habit.dis_kosu_hedef_tempo || 6;
        } else if (['snav', 'mekik', 'barfiks', 'muscle_up'].includes(habit.tip)) {
            adetFields.style.display = 'block';
            document.getElementById('adetHedefDeger').value = habit.hedef_deger || 10;
        } else if (habit.tip === 'plank') {
            plankFields.style.display = 'block';
            document.getElementById('plankSure').value = habit.plank_hedef_sure || 60;
        } else if (['bisiklet', 'agirlik'].includes(habit.tip)) {
            infoFields.style.display = 'block';
            if (infoText) infoText.textContent = infoTexts[habit.tip] || 'Girdiğiniz süre kadar yapmanız yeterli.';
        }
    }

    const modalTitle = document.querySelector('.modal-header h2');
    if (modalTitle) modalTitle.textContent = 'Alışkanlığı Düzenle';

    habitModal.classList.add('active');
    document.body.classList.add('modal-open');
};

// Drag and Drop Handlers
let draggedHabitId = null;
let draggedHabitElement = null;

window.handleDragStart = (e, id) => {
    draggedHabitId = id;
    draggedHabitElement = e.currentTarget;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', id);
    e.currentTarget.style.opacity = '0.5';
};

window.handleDragOver = (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const targetCard = e.target.closest('.habit-card');
    if (targetCard && targetCard !== draggedHabitElement) {
        const bounding = targetCard.getBoundingClientRect();
        const offset = bounding.y + (bounding.height / 2);
        if (e.clientY - offset > 0) {
            targetCard.style.borderBottom = '2px solid var(--primary)';
            targetCard.style.borderTop = '';
        } else {
            targetCard.style.borderTop = '2px solid var(--primary)';
            targetCard.style.borderBottom = '';
        }
    }
};

window.handleDrop = async (e, id) => {
    e.preventDefault();
    document.querySelectorAll('.habit-card').forEach(c => {
        c.style.borderTop = '';
        c.style.borderBottom = '';
        c.style.opacity = '1';
    });
    
    if (!draggedHabitId) return;

    const targetCard = e.target.closest('.habit-card');
    if (targetCard && targetCard !== draggedHabitElement) {
        const bounding = targetCard.getBoundingClientRect();
        const offset = bounding.y + (bounding.height / 2);
        if (e.clientY - offset > 0) {
            targetCard.after(draggedHabitElement);
        } else {
            targetCard.before(draggedHabitElement);
        }
        
        const newOrder = Array.from(document.querySelectorAll('.habit-card')).map((c, index) => ({
            id: c.dataset.id,
            siralama: index
        }));

        try {
            await Promise.all(newOrder.map(item => api(`${API_BASE}/habits.php`, {
                method: 'PUT',
                body: JSON.stringify({ id: item.id, siralama: item.siralama })
            })));
        } catch (err) {
            console.error('Sıralama hatası:', err);
        }
    }
    draggedHabitId = null;
    draggedHabitElement = null;
};

// Current Date Display
const currentDateEl = document.getElementById('currentDate');
const currentDayNameEl = document.getElementById('currentDayName');
if (currentDateEl) {
    const today = new Date();
    currentDateEl.textContent = today.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' });
}
if (currentDayNameEl) {
    const today = new Date();
    currentDayNameEl.textContent = today.toLocaleDateString('tr-TR', { weekday: 'long' });
}

// History Page
const historyDate = document.getElementById('historyDate');
const historyContainer = document.getElementById('historyContainer');
const historyEmptyState = document.getElementById('historyEmptyState');
const historyPercent = document.getElementById('historyPercent');
const prevDayBtn = document.getElementById('prevDay');
const nextDayBtn = document.getElementById('nextDay');
const todayBtn = document.getElementById('todayBtn');

if (historyDate) {
    historyDate.value = new Date().toISOString().split('T')[0];
}

async function loadHistory(date) {
    // Check if we're on the new history.php page (with categoriesContainer)
    const categoriesContainer = document.getElementById('categoriesContainer');
    if (categoriesContainer) {
        // New history.php uses its own JS (loadHistoryPage function)
        return;
    }

    // Old history page functionality (if still exists)
    if (!historyContainer || !historyEmptyState) {
        return;
    }

    try {
        const data = await api(`${API_BASE}/records.php?tarih=${date}`);
        window.allHabits = data.kayitlar || [];

        if (data.kayitlar.length === 0) {
            historyContainer.style.display = 'none';
            historyEmptyState.style.display = 'block';
            updateProgress(0);
            updateCalorieBalance(data.kalori_dengesi || null);
            return;
        }

        historyContainer.style.display = 'grid';
        historyEmptyState.style.display = 'none';
        updateProgress(data.tamamlanma_yuzdesi);
        updateCalorieBalance(data.kalori_dengesi);

        renderHistory(data.kayitlar);
    } catch (err) {
        console.error(err);
    }
}

function renderHistory(records) {
    historyContainer.innerHTML = records.map((r, index) => {
        // Calculate progress percentage for types with targets
        let progressPercent = 0;
        let progressBar = '';
        if (r.tip === 'checkbox') {
            progressPercent = r.tamamlandi ? 100 : 0;
        } else if (r.tip === 'uyku') {
            progressPercent = r.hedef_deger ? Math.min(100, ((r.deger || 0) / r.hedef_deger) * 100) : 0;
        } else if (r.tip === 'kosu_bandi') {
            const hedefSure = r.kosu_bandi_hedef_sure || 30;
            progressPercent = Math.min(100, ((r.deger2 || 0) / hedefSure) * 100);
        } else if (r.tip === 'bisiklet') {
            const hedefSure = r.bisiklet_hedef_sure || 30;
            progressPercent = Math.min(100, ((r.deger2 || 0) / hedefSure) * 100);
        } else if (r.hedef_deger) {
            progressPercent = Math.min(100, ((r.deger || 0) / r.hedef_deger) * 100);
        }

        if (r.tip !== 'checkbox') {
            progressBar = `
                <div class="habit-progress" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px; opacity: 0.7;">
                        <span>İlerleme</span>
                        <span>%${Math.round(progressPercent)}</span>
                    </div>
                    <div class="progress-bar" style="height: 10px; background: var(--bg-tertiary); border-radius: 5px; overflow: hidden;">
                        <div class="progress-fill" style="width: ${progressPercent}%; background: ${r.renk || 'var(--primary)'}; height: 100%; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                    </div>
                </div>`;
        }

        // Status pill
        const statusPill = r.tamamlandi
            ? `<span class="status-pill status-completed">✓ Tamamlandı</span>`
            : `<span class="status-pill status-pending">Devam Ediyor</span>`;

        // Calorie burn badge
        const calorieBadge = r.yakilan_kalori && r.yakilan_kalori > 0
            ? `<span style="font-size: 12px; padding: 4px 10px; background: rgba(239,68,68,0.1); color: #ef4444; border-radius: 20px; font-weight: 600; margin-left: 8px;">🔥 ${r.yakilan_kalori} kcal</span>`
            : '';

        // Content based on type with premium styling
        let content = '';
        const inputStyle = `
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.2s;
            outline: none;
        `;

        switch (r.tip) {
            case 'checkbox':
                content = `
                    <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-tertiary); padding: 16px; border-radius: 16px; cursor: pointer;" onclick="updateHistoryRecord(${r.aliskanlik_id}, null, '${r.tip}', ${!r.tamamlandi})">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="custom-checkbox" style="width: 32px; height: 32px; border-radius: 10px; border: 2px solid ${r.tamamlandi ? 'var(--success)' : 'var(--border)'}; background: ${r.tamamlandi ? 'var(--success)' : 'transparent'}; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="white" style="display: ${r.tamamlandi ? 'block' : 'none'}">
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                </svg>
                            </div>
                            <span style="font-weight: 600; font-size: 16px; color: ${r.tamamlandi ? 'var(--text-primary)' : 'var(--text-secondary)'}">${r.tamamlandi ? 'Başarıyla Tamamlandı' : 'Tamamlanmadı'}</span>
                        </div>
                        <span style="font-size: 12px; font-weight: 700; color: var(--primary);">DEĞİŞTİR</span>
                    </div>`;
                break;

            case 'kosu_bandi':
                content = `
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                        <div>
                            <label style="font-size:11px; font-weight:700; color: var(--text-secondary); margin-bottom:6px; display:block; text-transform:uppercase;">Hız (km/s)</label>
                            <input type="number" value="${r.deger3 || 5}" step="0.1" min="1" max="20"
                                onchange="updateHistoryExercise(${r.aliskanlik_id}, document.querySelector('[data-kosu-sure-${r.aliskanlik_id}]').value, this.value, document.querySelector('[data-kosu-egim-${r.aliskanlik_id}]').value)"
                                data-kosu-hiz-${r.aliskanlik_id}
                                style="${inputStyle}" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:700; color: var(--text-secondary); margin-bottom:6px; display:block; text-transform:uppercase;">Eğim (%)</label>
                            <input type="number" value="${r.deger4 || 0}" min="0" max="15"
                                onchange="updateHistoryExercise(${r.aliskanlik_id}, document.querySelector('[data-kosu-sure-${r.aliskanlik_id}]').value, document.querySelector('[data-kosu-hiz-${r.aliskanlik_id}]').value, this.value)"
                                data-kosu-egim-${r.aliskanlik_id}
                                style="${inputStyle}" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:700; color: var(--text-secondary); margin-bottom:6px; display:block; text-transform:uppercase;">Süre (dk)</label>
                            <input type="number" value="${r.deger2 || 30}" min="1" max="180"
                                onchange="updateHistoryExercise(${r.aliskanlik_id}, this.value, document.querySelector('[data-kosu-hiz-${r.aliskanlik_id}]').value, document.querySelector('[data-kosu-egim-${r.aliskanlik_id}]').value)"
                                data-kosu-sure-${r.aliskanlik_id}
                                style="${inputStyle}" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                        </div>
                    </div>`;
                break;

            case 'uyku':
                const saatVal = Math.floor((r.deger || 0) / 60);
                const dkVal = (r.deger || 0) % 60;
                content = `
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size:11px; font-weight:700; color: var(--text-secondary); margin-bottom:6px; display:block; text-transform:uppercase;">Saat</label>
                            <input type="number" value="${saatVal}" min="0" max="24" id="hSaat_${r.aliskanlik_id}"
                                onchange="updateHistoryRecord(${r.aliskanlik_id}, parseInt(this.value)*60 + parseInt(document.getElementById('hDk_${r.aliskanlik_id}').value), '${r.tip}')"
                                style="${inputStyle}">
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:700; color: var(--text-secondary); margin-bottom:6px; display:block; text-transform:uppercase;">Dakika</label>
                            <input type="number" value="${dkVal}" min="0" max="59" id="hDk_${r.aliskanlik_id}"
                                onchange="updateHistoryRecord(${r.aliskanlik_id}, parseInt(document.getElementById('hSaat_${r.aliskanlik_id}').value)*60 + parseInt(this.value), '${r.tip}')"
                                style="${inputStyle}">
                        </div>
                    </div>`;
                break;

            case 'adim':
                content = `
                    <div>
                        <label style="font-size:11px; font-weight:700; color: var(--text-secondary); margin-bottom:6px; display:block; text-transform:uppercase;">Atılan Adım Sayısı</label>
                        <div style="position: relative;">
                            <input type="number" value="${r.deger || 0}" min="0" step="100"
                                onchange="updateHistoryRecord(${r.aliskanlik_id}, this.value, '${r.tip}')"
                                style="${inputStyle} padding-right: 80px;">
                            <span style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-tertiary); font-size: 12px;">ADIM</span>
                        </div>
                    </div>`;
                break;

            case 'bisiklet':
            case 'agirlik':
                content = `
                    <div>
                        <label style="font-size:11px; font-weight:700; color: var(--text-secondary); margin-bottom:6px; display:block; text-transform:uppercase;">Egzersiz Süresi</label>
                        <div style="position: relative;">
                            <input type="number" value="${r.deger2 || 0}" min="0"
                                onchange="updateHistoryExercise(${r.aliskanlik_id}, this.value, 0, 0)"
                                style="${inputStyle} padding-right: 80px;">
                            <span style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-tertiary); font-size: 12px;">DAKİKA</span>
                        </div>
                    </div>`;
                break;

            default:
                content = `
                    <div>
                        <label style="font-size:11px; font-weight:700; color: var(--text-secondary); margin-bottom:6px; display:block; text-transform:uppercase;">${r.birim || 'Değer'}</label>
                        <input type="number" value="${r.deger || 0}" min="0"
                            onchange="updateHistoryRecord(${r.aliskanlik_id}, this.value, '${r.tip}')"
                            style="${inputStyle}">
                    </div>`;
        }

        return `
            <div class="habit-card fade-in ${r.tamamlandi ? 'completed' : ''}" data-id="${r.aliskanlik_id}" style="animation-delay: ${index * 0.05}s">
                <div class="habit-header" style="margin-bottom: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span class="habit-name" style="color: ${r.renk || 'var(--primary)'}; font-size: 20px; font-weight: 700;">${r.ad}</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            ${statusPill}
                            ${calorieBadge}
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button class="habit-btn" onclick="openEditHabit(${r.aliskanlik_id})" title="Düzenle" style="background: var(--bg-tertiary); transform: scale(1.1);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                ${progressBar}
                <div style="background: var(--bg-secondary); border: 1px solid var(--border); padding: 20px; border-radius: 16px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                    ${content}
                </div>
            </div>`;
    }).join('');
}

window.updateHistoryRecord = async (habitId, value, type, completed) => {
    const date = historyDate.value;

    try {
        await api(`${API_BASE}/records.php`, {
            method: 'POST',
            body: JSON.stringify({
                aliskanlik_id: habitId,
                tarih: date,
                deger: value !== null ? parseInt(value) : null,
                tamamlandi: completed !== undefined ? completed : undefined
            })
        });

        toast('Kayıt güncellendi');
        await loadHistory(date);
    } catch (err) {
        toast(err.message, 'error');
    }
};

window.updateHistoryExercise = async (habitId, sure, hiz, egim) => {
    const date = historyDate.value;

    try {
        await api(`${API_BASE}/records.php`, {
            method: 'POST',
            body: JSON.stringify({
                aliskanlik_id: habitId,
                tarih: date,
                deger2: parseInt(sure),
                deger3: parseFloat(hiz),
                deger4: parseInt(egim)
            })
        });

        toast('Kayıt güncellendi');
        await loadHistory(date);
    } catch (err) {
        toast(err.message, 'error');
    }
};

historyDate && historyDate.addEventListener('change', () => loadHistory(historyDate.value));

prevDayBtn && prevDayBtn.addEventListener('click', () => {
    const date = new Date(historyDate.value);
    date.setDate(date.getDate() - 1);
    historyDate.value = date.toISOString().split('T')[0];
    loadHistory(historyDate.value);
});

nextDayBtn && nextDayBtn.addEventListener('click', () => {
    const date = new Date(historyDate.value);
    const today = new Date();
    date.setDate(date.getDate() + 1);
    if (date <= today) {
        historyDate.value = date.toISOString().split('T')[0];
        loadHistory(historyDate.value);
    }
});

todayBtn && todayBtn.addEventListener('click', () => {
    historyDate.value = new Date().toISOString().split('T')[0];
    loadHistory(historyDate.value);
});

// Init - DOM hazır olduğunda çalıştır
function initCustomSelects() {
    document.querySelectorAll('select:not(.custom-select-hidden):not(.period-select)').forEach(select => {
        // Exclude some selects if needed, though we will apply to all native selects
        select.classList.add('custom-select-hidden');

        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-container';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        const trigger = document.createElement('div');
        trigger.className = 'custom-select-trigger';
        
        const selectedOption = select.options[select.selectedIndex];
        trigger.innerHTML = `<span>${selectedOption ? selectedOption.text : 'Seçiniz'}</span> 
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary)"><path d="M6 9l6 6 6-6"/></svg>`;
        
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'custom-select-options';

        Array.from(select.options).forEach(option => {
            const optDiv = document.createElement('div');
            optDiv.className = `custom-option ${option.selected ? 'selected' : ''}`;
            optDiv.dataset.value = option.value;
            optDiv.textContent = option.text;
            
            optDiv.addEventListener('click', () => {
                select.value = option.value;
                trigger.querySelector('span').textContent = option.text;
                optionsDiv.querySelectorAll('.custom-option').forEach(o => o.classList.remove('selected'));
                optDiv.classList.add('selected');
                wrapper.classList.remove('open');
                select.dispatchEvent(new Event('change'));
            });
            optionsDiv.appendChild(optDiv);
        });

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.custom-select-container.open').forEach(w => {
                if (w !== wrapper) w.classList.remove('open');
            });
            wrapper.classList.toggle('open');
        });

        wrapper.appendChild(trigger);
        wrapper.appendChild(optionsDiv);
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-select-container.open').forEach(w => w.classList.remove('open'));
    });
}

function initApp() {
    console.log('DOM hazır, init çağrılıyor...');
    
    initCustomSelects();

    // Set active state for bottom navigation
    setBottomNavActiveState();

    if (habitsContainer) {
        loadHabits();
        loadDailyNote(new Date().toISOString().split('T')[0]);
    }

    // Only load history if we have the old historyContainer element
    // New history.php uses its own JS (loadHistoryPage function)
    if (historyContainer && historyDate && !document.getElementById('categoriesContainer')) {
        loadHistory(historyDate.value);
    }
}

// Set active state for bottom navigation
function setBottomNavActiveState() {
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
    const bottomNavItems = document.querySelectorAll('.bottom-nav-item');

    bottomNavItems.forEach(item => {
        const page = item.getAttribute('data-page');
        let isActive = false;

        // Page matching logic
        if (page === 'dashboard' && (currentPage === 'dashboard.php' || currentPage === 'index.php' || currentPage === '')) {
            isActive = true;
        } else if (page === 'history' && currentPage === 'history.php') {
            isActive = true;
        } else if (page === 'analytics' && currentPage === 'analytics.php') {
            isActive = true;
        } else if (page === 'profile' && currentPage === 'profil.php') {
            isActive = true;
        }

        if (isActive) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
}

// =====================================================
// YENI STEP MODAL SISTEMI
// =====================================================

(function() {
    const habitModal = document.getElementById('habitModal');
    const habitForm = document.getElementById('habitForm');
    const habitNameInput = document.getElementById('habitNameInput');
    const backToStep1Btn = document.getElementById('backToStep1');
    const changeTypeBtn = document.getElementById('changeTypeBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const modalFooter = document.getElementById('modalFooter');
    const habitSearchInput = document.getElementById('habitSearchInput');
    const habitTypeList = document.getElementById('habitTypeList');
    const categoryFilters = document.getElementById('categoryFilters');
    const dynamicSettings = document.getElementById('dynamicSettings');
    const selectedTypeInfo = document.getElementById('selectedTypeInfo');
    const submitHabitBtn = document.getElementById('submitHabitBtn');

    let currentStep = 1;
    let selectedHabitType = null;
    let currentCategory = 'all';

    // Modalı aç
    function openHabitModal() {
        habitModal.classList.add('active');
        document.body.classList.add('modal-open');
        resetModal();
    }

    // Modalı kapat
    function closeHabitModal() {
        habitModal.classList.remove('active');
        document.body.classList.remove('modal-open');
        setTimeout(() => resetModal(), 300);
    }

    // Modalı sıfırla
    function resetModal() {
        habitForm.reset();
        document.getElementById('editHabitId').value = '';
        document.getElementById('selectedType').value = '';
        document.getElementById('selectedColor').value = '#6366f1';
        document.getElementById('modalTitle').textContent = 'Yeni Alışkanlık';
        submitHabitBtn.textContent = 'Oluştur ✓';

        // Renk seçimini sıfırla
        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const defaultColorBtn = document.querySelector('.color-btn[data-color="#6366f1"]');
        if (defaultColorBtn) {
            defaultColorBtn.classList.add('active');
        }

        // Step 1'e git
        goToStep(1);
        selectedHabitType = null;
        currentCategory = 'all';

        // Filtreleri sıfırla
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = 'var(--bg-primary)';
            btn.style.color = 'var(--text-secondary)';
            btn.style.borderColor = 'var(--border)';
        });
        const allBtn = document.querySelector('.filter-btn[data-category="all"]');
        if (allBtn) {
            allBtn.classList.add('active');
            allBtn.style.background = 'var(--primary)';
            allBtn.style.color = 'white';
            allBtn.style.borderColor = 'var(--primary)';
        }

        // Aramayı sıfırla
        habitSearchInput.value = '';
        renderHabitTypes();

        // Dinamik ayarları temizle
        dynamicSettings.innerHTML = '';
    }

    // Step değiştir
    function goToStep(step) {
        currentStep = step;

        // Tüm step'leri gizle
        document.querySelectorAll('.modal-step').forEach(s => s.style.display = 'none');

        // İlgili step'i göster
        document.getElementById(`step${step}`).style.display = 'block';

        // Progress dots güncelle (2 step var)
        document.querySelectorAll('.progress-dot').forEach((dot, index) => {
            if (index < 2) { // Sadece ilk 2 dot'u kullan
                dot.style.background = index + 1 <= step ? 'var(--primary)' : 'var(--border)';
            }
        });

        // Footer'ı sadece step 2'de göster
        modalFooter.style.display = step === 2 ? 'block' : 'none';

        if (step === 1) {
            renderHabitTypes();
        } else if (step === 2 && habitNameInput) {
            // İsim input'a focus
            setTimeout(() => habitNameInput.focus(), 100);
        }
    }

    // Habit type listesi render et
    function renderHabitTypes() {
        const searchTerm = habitSearchInput ? habitSearchInput.value.toLowerCase() : '';
        const filteredTypes = typeof HABIT_TYPES !== 'undefined' ? HABIT_TYPES.filter(ht => {
            const matchCategory = currentCategory === 'all' || ht.category === currentCategory;
            const matchSearch = ht.name.toLowerCase().includes(searchTerm) || ht.desc.toLowerCase().includes(searchTerm);
            return matchCategory && matchSearch;
        }) : [];

        if (!habitTypeList) return;

        if (filteredTypes.length === 0) {
            habitTypeList.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 30px; color: var(--text-secondary);">
                    <div style="font-size: 40px; margin-bottom: 10px;">🔍</div>
                    <div>Sonuç bulunamadı</div>
                    <div style="font-size: 12px;">Başka bir arama deneyin</div>
                </div>
            `;
            return;
        }

        habitTypeList.innerHTML = filteredTypes.map(ht => `
            <button type="button" class="habit-type-btn"
                data-type="${ht.type}"
                style="display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 16px 12px;
                border: 2px solid var(--border);
                border-radius: 12px; background: var(--bg-primary);
                cursor: pointer; transition: all 0.2s;">
                <span style="font-size: 32px;">${ht.icon}</span>
                <span style="font-weight: 600; font-size: 13px; color: var(--text-primary);">${ht.name}</span>
                <span style="font-size: 11px; color: var(--text-secondary); text-align: center;">${ht.desc}</span>
            </button>
        `).join('');

        // Click handlers ekle
        habitTypeList.querySelectorAll('.habit-type-btn').forEach(btn => {
            btn.addEventListener('click', () => selectHabitType(btn.dataset.type));
        });
    }

    // Habit tipi seç
    function selectHabitType(type) {
        selectedHabitType = type;
        document.getElementById('selectedType').value = type;

        // Seçili type bilgilerini göster
        const habitData = typeof HABIT_TYPES !== 'undefined' ? HABIT_TYPES.find(h => h.type === type) : null;
        if (habitData && selectedTypeInfo) {
            document.getElementById('selectedTypeIcon').textContent = habitData.icon;
            document.getElementById('selectedTypeName').textContent = habitData.name;
            document.getElementById('selectedTypeDesc').textContent = habitData.desc;
        }

        // Dinamik ayarları yükle
        loadTypeSettings(type);

        // Step 2'ye git
        goToStep(2);
    }

    // Type ayarlarını yükle
    function loadTypeSettings(type) {
        const settings = typeof TYPE_SETTINGS !== 'undefined' ? TYPE_SETTINGS[type] : { html: '' };
        if (dynamicSettings) {
            dynamicSettings.innerHTML = settings.html || '';
        }

        // Tempo butonları için event listener
        if (type === 'kosu_bandi') {
            setupTempoButtons('tempo-btn', 'settingTempo');
        } else if (type === 'dis_kosu') {
            setupTempoButtons('disco-tempo-btn', 'settingDiscoTempo');
        }
    }

    // Tempo butonlarını ayarla
    function setupTempoButtons(btnClass, inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const container = input.parentElement;
        const buttons = container.querySelectorAll(`.${btnClass}`);

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const value = btn.dataset.value;
                input.value = value;

                // Tüm butonlardan active class'ı kaldır
                buttons.forEach(b => {
                    b.classList.remove('active');
                });

                // Seçili butona active class'ı ekle
                btn.classList.add('active');
            });
        });
    }

    // Event Listeners
    backToStep1Btn && backToStep1Btn.addEventListener('click', () => goToStep(1));

    changeTypeBtn && changeTypeBtn.addEventListener('click', () => goToStep(1));

    closeModalBtn && closeModalBtn.addEventListener('click', closeHabitModal);

    habitModal && habitModal.addEventListener('click', (e) => {
        if (e.target === habitModal) closeHabitModal();
    });

    if (habitSearchInput) {
        habitSearchInput.addEventListener('input', () => renderHabitTypes());
    }

    if (categoryFilters) {
        categoryFilters.addEventListener('click', (e) => {
            if (e.target.classList.contains('filter-btn')) {
                currentCategory = e.target.dataset.category;

                // Tüm butonları güncelle
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.background = 'var(--bg-primary)';
                    btn.style.color = 'var(--text-secondary)';
                    btn.style.borderColor = 'var(--border)';
                });

                // Seçili butonu işaretle
                e.target.classList.add('active');
                e.target.style.background = 'var(--primary)';
                e.target.style.color = 'white';
                e.target.style.borderColor = 'var(--primary)';

                renderHabitTypes();
            }
        });
    }

    // Renk seçimi
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('color-btn')) {
            const color = e.target.dataset.color;
            const colorInput = document.getElementById('selectedColor');
            if (colorInput) colorInput.value = color;

            // Tüm butonlardan active class'ı kaldır
            document.querySelectorAll('.color-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Seçili butona active class'ı ekle
            e.target.classList.add('active');
        }
    });

    // Global functions
    window.openNewHabitModal = openHabitModal;
    window.closeNewHabitModal = closeHabitModal;
})();

console.log('Script yüklendi');

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // Find correct sw path based on current path
        const swPath = window.location.pathname.includes('/dailytor') ? '/dailytor/sw.js' : '/sw.js';
        
        navigator.serviceWorker.register(swPath)
            .then(registration => {
                console.log('[Service Worker] Registered successfully with scope:', registration.scope);
            })
            .catch(error => {
                console.error('[Service Worker] Registration failed:', error);
            });
    });
}

// =====================================================
// YAPAY ZEKA YEMEK ANALIZ FONKSIYONLARI
// =====================================================

window.triggerFoodCamera = function(habitId) {
    const fileInput = document.getElementById(`foodCameraInput-${habitId}`);
    if (fileInput) {
        fileInput.click();
    }
};

window.showAnalysisLoader = function() {
    let loader = document.getElementById('analysisLoaderOverlay');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'analysisLoaderOverlay';
        loader.className = 'fullscreen-loader-overlay';
        loader.innerHTML = `
            <div class="fullscreen-loader-content">
                <div class="loader-spinner-ring"></div>
                <div class="loader-status-text">Yemek Analiz Ediliyor...</div>
                <div class="loader-sub-text">Yapay zeka besin değerlerini ve makroları hesaplıyor, lütfen bekleyin.</div>
            </div>
        `;
        document.body.appendChild(loader);
    }
    // Force reflow
    loader.offsetHeight;
    loader.classList.add('active');
    document.body.style.overflow = 'hidden';
};

window.hideAnalysisLoader = function() {
    const loader = document.getElementById('analysisLoaderOverlay');
    if (loader) {
        loader.classList.remove('active');
        document.body.style.overflow = '';
        setTimeout(() => {
            if (loader.parentNode) loader.remove();
        }, 300);
    }
};

window.handleFoodCameraUpload = async function(event, habitId) {
    const fileInput = event.target;
    const file = fileInput.files[0];
    if (!file) return;

    // Yükleme ve blur ekranını göster
    window.showAnalysisLoader();

    const cleanUp = () => {
        window.hideAnalysisLoader();
        fileInput.value = '';
    };

    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = async () => {
        try {
            const base64Data = reader.result.split(',')[1];
            const mimeType = file.type;

            const response = await fetch(`${API_BASE}/detect-food.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    image: base64Data,
                    mimeType: mimeType
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.hata || 'Yemek analizi başarısız oldu.');
            }

            // Loader'ı kapat ve modalı aç
            window.hideAnalysisLoader();
            window.showFoodAnalysisResultModal(data, base64Data, mimeType, habitId, cleanUp);

        } catch (err) {
            console.error('Yemek analizi hatası:', err);
            toast(err.message || 'Analiz sırasında bir hata oluştu.', 'error');
            cleanUp();
        }
    };
    reader.onerror = (err) => {
        toast('Fotoğraf okunamadı.', 'error');
        cleanUp();
    };
};

window.showFoodAnalysisResultModal = function(result, base64Data, mimeType, habitId, onCloseCallback) {
    const overlay = document.createElement('div');
    overlay.className = 'food-modal-overlay';
    
    // Mikro besinleri hazırla
    const sugar = result.seker || 0;
    const sodium = result.sodyum || 0;
    const fiber = result.lif || 0;

    overlay.innerHTML = `
        <div class="food-modal-container">
            <div class="food-modal-body-scrollable">
                <img class="food-modal-image-preview" src="data:${mimeType};base64,${base64Data}" alt="Yemek">
                
                <div class="food-modal-header">
                    <div class="food-modal-title">${result.emoji || '🍔'} ${result.yemek_adi}</div>
                    <div class="food-modal-meta-row">
                        <div class="food-modal-portion">⏱️ Porsiyon: ${result.porsiyon || '1 porsiyon'}</div>
                        <div class="food-modal-calories-badge" style="margin: 0; font-size: 16px; padding: 6px 16px;">${result.kalori} kcal</div>
                    </div>
                </div>
                
                <div style="font-size:11px; font-weight:800; color:var(--text-secondary); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: -12px; margin-top: 4px;">MAKRO DEĞERLERİ</div>
                <div class="food-modal-macros">
                    <div class="food-modal-macro-card" style="--accent-color: #6366f1;">
                        <div class="food-modal-macro-value">${result.protein || 0}g</div>
                        <div class="food-modal-macro-label">Protein</div>
                    </div>
                    <div class="food-modal-macro-card" style="--accent-color: #10b981;">
                        <div class="food-modal-macro-value">${result.karbonhidrat || 0}g</div>
                        <div class="food-modal-macro-label">Karb</div>
                    </div>
                    <div class="food-modal-macro-card" style="--accent-color: #f59e0b;">
                        <div class="food-modal-macro-value">${result.yag || 0}g</div>
                        <div class="food-modal-macro-label">Yağ</div>
                    </div>
                </div>

                <div style="font-size:11px; font-weight:800; color:var(--text-secondary); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: -12px; margin-top: 4px;">MİKRO DEĞERLERİ</div>
                <div class="food-modal-macros" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="food-modal-macro-card" style="--accent-color: #ec4899;">
                        <div class="food-modal-macro-value">${sugar}g</div>
                        <div class="food-modal-macro-label">Şeker</div>
                    </div>
                    <div class="food-modal-macro-card" style="--accent-color: #ef4444;">
                        <div class="food-modal-macro-value">${sodium}mg</div>
                        <div class="food-modal-macro-label">Sodyum</div>
                    </div>
                    <div class="food-modal-macro-card" style="--accent-color: #06b6d4;">
                        <div class="food-modal-macro-value">${fiber}g</div>
                        <div class="food-modal-macro-label">Lif</div>
                    </div>
                </div>

                <div class="food-modal-description" style="max-height: none !important; min-height: 80px !important; overflow: visible !important;">
                    <div class="food-modal-description-title">
                        <span>💡</span> Diyetisyen Notu & Analiz
                    </div>
                    <div>${result.aciklama || 'Besin değerleri yapay zeka tarafından analiz edildi.'}</div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px; font-size: 13px; color: var(--text-primary); background: var(--bg-tertiary); padding: 10px 14px; border-radius: 12px; border: 1px solid var(--border); flex-shrink: 0;">
                    <input type="checkbox" id="saveMacrosCheckbox" checked style="width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer;">
                    <label for="saveMacrosCheckbox" style="cursor: pointer; font-weight:600; font-size: 12px; user-select: none;">Makro ve mikro besin değerlerini de kaydet</label>
                </div>
            </div>

            <div class="food-modal-footer">
                <button class="food-modal-btn-primary" id="foodModalAddBtn">Günlük Kayda Ekle (+)</button>
                <button class="food-modal-btn-cancel" id="foodModalCancelBtn">İptal</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);
    
    // Animasyonu başlatmak için
    overlay.offsetHeight;
    overlay.classList.add('active');

    const closeModal = () => {
        overlay.classList.remove('active');
        setTimeout(() => {
            overlay.remove();
            if (onCloseCallback) onCloseCallback();
        }, 300);
    };

    const addBtn = overlay.querySelector('#foodModalAddBtn');
    const cancelBtn = overlay.querySelector('#foodModalCancelBtn');
    const saveMacrosChk = overlay.querySelector('#saveMacrosCheckbox');

    addBtn.addEventListener('click', async () => {
        const selectedDate = (document.getElementById('historyDate') || {}).value || new Date().toISOString().split('T')[0];
        const saveMacros = saveMacrosChk.checked;

        addBtn.disabled = true;
        addBtn.textContent = 'Kaydediliyor...';

        try {
            const bodyData = {
                aliskanlik_id: habitId,
                tarih: selectedDate,
                yemek_adi: result.yemek_adi,
                kalori: result.kalori,
                emoji: result.emoji || '🍽️'
            };

            if (saveMacros) {
                bodyData.protein = parseInt(result.protein) || 0;
                bodyData.karbonhidrat = parseFloat(result.karbonhidrat) || 0.0;
                bodyData.yag = parseInt(result.yag) || 0;
                bodyData.seker = parseInt(result.seker) || 0;
                bodyData.sodyum = parseInt(result.sodyum) || 0;
                bodyData.lif = parseInt(result.lif) || 0;
            }

            const response = await fetch(`${API_BASE}/meals.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(bodyData)
            });

            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.hata || 'Yemek kaydedilemedi.');
            }

            toast('Yemek başarıyla eklendi');
            
            // UI Güncellemeleri
            updateProgress(responseData.tamamlanma_yuzdesi);
            updateCalorieBalance(responseData.kalori_dengesi);
            await loadHistory(selectedDate);
            closeModal();

        } catch (err) {
            toast(err.message, 'error');
            addBtn.disabled = false;
            addBtn.textContent = 'Günlük Kayda Ekle (+)';
        }
    });

    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });
};

window.openAddMealModal = function(habitId) {
    const overlay = document.createElement('div');
    overlay.className = 'food-modal-overlay';
    overlay.id = 'manualMealModal';
    
    overlay.innerHTML = `
        <div class="food-modal-container" style="max-width: 460px; max-height: 95vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin:0;">Manuel Yemek Ekle</h3>
                <div id="closeManualMealBtn" style="cursor: pointer; font-size: 24px; color: var(--text-secondary); line-height: 1;">&times;</div>
            </div>
            <form id="manualMealForm">
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 13px; font-weight:600; color: var(--text-secondary); display: block; margin-bottom: 4px;">Yemek / Yiyecek Adı</label>
                    <input type="text" id="manualMealName" style="width:100%; box-sizing:border-box; padding:10px; border-radius:10px; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-primary);" required placeholder="Örn: Haşlanmış Yumurta">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 13px; font-weight:600; color: var(--text-secondary); display: block; margin-bottom: 4px;">Kalori (kcal)</label>
                    <input type="number" id="manualMealCalories" style="width:100%; box-sizing:border-box; padding:10px; border-radius:10px; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-primary);" required min="1" placeholder="Örn: 155">
                </div>
                
                <div style="font-size: 13px; font-weight: 700; color: var(--text-primary); margin-top: 16px; margin-bottom: 6px;">Makro Besinler (İsteğe Bağlı)</div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 2px;">Protein (g)</label>
                        <input type="number" id="manualMealProtein" style="width:100%; box-sizing:border-box; padding:8px; border-radius:8px; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-primary);" min="0" placeholder="0">
                    </div>
                    <div>
                        <label style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 2px;">Karb (g)</label>
                        <input type="number" step="0.1" id="manualMealCarbs" style="width:100%; box-sizing:border-box; padding:8px; border-radius:8px; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-primary);" min="0" placeholder="0.0">
                    </div>
                    <div>
                        <label style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 2px;">Yağ (g)</label>
                        <input type="number" id="manualMealFat" style="width:100%; box-sizing:border-box; padding:8px; border-radius:8px; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-primary);" min="0" placeholder="0">
                    </div>
                </div>

                <div style="font-size: 13px; font-weight: 700; color: var(--text-primary); margin-top: 12px; margin-bottom: 6px;">Mikro Besinler (İsteğe Bağlı)</div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 2px;">Şeker (g)</label>
                        <input type="number" id="manualMealSugar" style="width:100%; box-sizing:border-box; padding:8px; border-radius:8px; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-primary);" min="0" placeholder="0">
                    </div>
                    <div>
                        <label style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 2px;">Sodyum (mg)</label>
                        <input type="number" id="manualMealSodium" style="width:100%; box-sizing:border-box; padding:8px; border-radius:8px; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-primary);" min="0" placeholder="0">
                    </div>
                    <div>
                        <label style="font-size: 11px; color: var(--text-secondary); display: block; margin-bottom: 2px;">Lif (g)</label>
                        <input type="number" id="manualMealFiber" style="width:100%; box-sizing:border-box; padding:8px; border-radius:8px; border: 1px solid var(--border); background: var(--bg-primary); color: var(--text-primary);" min="0" placeholder="0">
                    </div>
                </div>

                <div class="food-modal-footer">
                    <button type="submit" class="food-modal-btn-primary" id="manualMealSubmitBtn">Yemeği Ekle</button>
                    <button type="button" class="food-modal-btn-cancel" id="manualMealCancelBtn">İptal</button>
                </div>
            </form>
        </div>
    `;

    document.body.appendChild(overlay);
    overlay.offsetHeight;
    overlay.classList.add('active');
    document.body.classList.add('modal-open');

    const closeModal = () => {
        overlay.classList.remove('active');
        document.body.classList.remove('modal-open');
        setTimeout(() => overlay.remove(), 300);
    };

    overlay.querySelector('#closeManualMealBtn').addEventListener('click', closeModal);
    overlay.querySelector('#manualMealCancelBtn').addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    const form = overlay.querySelector('#manualMealForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = overlay.querySelector('#manualMealSubmitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Ekleniyor...';

        const selectedDate = (document.getElementById('historyDate') || {}).value || new Date().toISOString().split('T')[0];

        const name = overlay.querySelector('#manualMealName').value;
        const calories = parseInt(overlay.querySelector('#manualMealCalories').value) || 0;
        const protein = parseInt(overlay.querySelector('#manualMealProtein').value) || 0;
        const carbs = parseFloat(overlay.querySelector('#manualMealCarbs').value) || 0.0;
        const fat = parseInt(overlay.querySelector('#manualMealFat').value) || 0;
        const sugar = parseInt(overlay.querySelector('#manualMealSugar').value) || 0;
        const sodium = parseInt(overlay.querySelector('#manualMealSodium').value) || 0;
        const fiber = parseInt(overlay.querySelector('#manualMealFiber').value) || 0;

        try {
            const response = await fetch(`${API_BASE}/meals.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    aliskanlik_id: habitId,
                    tarih: selectedDate,
                    yemek_adi: name,
                    kalori: calories,
                    protein: protein,
                    karbonhidrat: carbs,
                    yag: fat,
                    seker: sugar,
                    sodyum: sodium,
                    lif: fiber
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.hata || 'Yemek kaydedilemedi.');
            }

            toast('Yemek başarıyla eklendi');
            
            updateProgress(data.tamamlanma_yuzdesi);
            updateCalorieBalance(data.kalori_dengesi);
            await loadHistory(selectedDate);
            closeModal();

        } catch (err) {
            toast(err.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Yemeği Ekle';
        }
    });
};

window.deleteMeal = async function(mealId, habitId) {
    const selectedDate = (document.getElementById('historyDate') || {}).value || new Date().toISOString().split('T')[0];
    const confirmDelete = await Dialog.confirm('Yemeği Sil', 'Bu yemeği listenizden silmek istediğinize emin misiniz?');
    if (!confirmDelete) return;

    try {
        const response = await fetch(`${API_BASE}/meals.php?id=${mealId}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.hata || 'Yemek silinemedi.');
        }

        toast('Yemek silindi');
        
        updateProgress(data.tamamlanma_yuzdesi);
        updateCalorieBalance(data.kalori_dengesi);
        await loadHistory(selectedDate);

    } catch (err) {
        toast(err.message, 'error');
    }
};

