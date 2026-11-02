// API_BASE is already defined in app.js

let dailyChart, weeklyChart, habitChart, monthlyChart, calorieChart, weightHistoryChart;

const isDark = getTheme() === 'dark';

const textColor = isDark ? '#f1f5f9' : '#0f172a';
const gridColor = isDark ? '#334155' : '#e2e8f0';

function handleEmptyState(ctxId, isEmpty) {
    const canvas = document.getElementById(ctxId);
    if (!canvas) return true;
    const container = canvas.closest('.chart-container');
    if (!container) return isEmpty;

    let overlay = container.querySelector('.chart-empty-state');
    if (isEmpty) {
        canvas.style.filter = 'blur(4px)';
        canvas.style.opacity = '0.3';
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'chart-empty-state';
            overlay.innerHTML = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:8px;opacity:0.7"><path d="M2 12h4l2-9 5 18 4-10 3 4h2"/></svg><span>Yeterli Veri Yok</span>';
            container.appendChild(overlay);
        }
        overlay.style.display = 'flex';
        return true;
    } else {
        canvas.style.filter = 'none';
        canvas.style.opacity = '1';
        if (overlay) overlay.style.display = 'none';
        return false;
    }
}

async function loadAnalytics() {
    const period = document.getElementById('periodSelect')?.value || 30;

    try {
        const [dailyData, weeklyData, habitData, monthlyData, calorieData, weightPredictionData, weightHistoryData] = await Promise.all([
            api(API_BASE + '/analytics.php?tip=gunluk-tamamlanma&gun_sayisi=' + period),
            api(API_BASE + '/analytics.php?tip=haftalik-karsilastirma'),
            api(API_BASE + '/analytics.php?tip=alinan-ilerleme&gun_sayisi=' + period),
            api(API_BASE + '/analytics.php?tip=aylik-ozet'),
            api(API_BASE + '/analytics.php?tip=kalori-dengesi&gun_sayisi=' + period),
            api(API_BASE + '/analytics.php?tip=kilo-tahmini'),
            api(API_BASE + '/profile.php?gecmis')
        ]);

        renderDailyChart(dailyData);
        renderWeeklyChart(weeklyData);
        renderHabitChart(habitData);
        renderMonthlyChart(monthlyData);
        renderCalorieChart(calorieData);
        renderWeightPrediction(weightPredictionData);
        renderWeightHistoryChart(weightHistoryData);

        const avgEl = document.getElementById('avgCompletion');
        if (avgEl && dailyData.veriler.length > 0) {
            const avg = dailyData.veriler.reduce((a, b) => a + b, 0) / dailyData.veriler.length;
            avgEl.textContent = avg.toFixed(1) + '%';
        }
    } catch (err) {
        console.error(err);
    }
}

function renderWeightHistoryChart(data) {
    const isEmpty = !data || !data.kayitlar || data.kayitlar.length === 0;
    if (handleEmptyState('weightHistoryChart', isEmpty)) return;
    
    const ctx = document.getElementById('weightHistoryChart').getContext('2d');

    if (weightHistoryChart) weightHistoryChart.destroy();

    const labels = data.kayitlar.map(k => new Date(k.tarih).toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' }));
    const values = data.kayitlar.map(k => k.kilo);

    weightHistoryChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Kilo (kg)',
                data: values,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointBackgroundColor: '#8b5cf6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                },
                y: {
                    grid: { color: gridColor },
                    ticks: { color: textColor, callback: value => value + ' kg' }
                }
            }
        }
    });
}

function renderDailyChart(data) {
    const isEmpty = !data || !data.veriler || data.veriler.length === 0 || data.veriler.every(v => v === 0);
    if (handleEmptyState('dailyCompletionChart', isEmpty)) return;
    
    const ctx = document.getElementById('dailyCompletionChart').getContext('2d');

    if (dailyChart) {
        dailyChart.destroy();
    }

    dailyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.etiketler,
            datasets: [{
                label: 'Tamamlanma %',
                data: data.veriler,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor,
                        maxTicksLimit: 10
                    }
                },
                y: {
                    min: 0,
                    max: 100,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        callback: value => value + '%'
                    }
                }
            }
        }
    });
}

function renderWeeklyChart(data) {
    const isEmpty = !data || !data.veriler || data.veriler.length === 0 || data.veriler.every(v => v === 0);
    if (handleEmptyState('weeklyChart', isEmpty)) return;
    
    const ctx = document.getElementById('weeklyChart').getContext('2d');

    if (weeklyChart) {
        weeklyChart.destroy();
    }

    const gradient = ctx.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.8)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.3)');

    weeklyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.etiketler,
            datasets: [{
                label: 'Ortalama %',
                data: data.veriler,
                backgroundColor: gradient,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor
                    }
                },
                y: {
                    min: 0,
                    max: 100,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        callback: value => value + '%'
                    }
                }
            }
        }
    });
}

function renderHabitChart(data) {
    const isEmpty = !data || !data.alinanlar || data.alinanlar.length === 0;
    if (handleEmptyState('habitProgressChart', isEmpty)) return;
    
    const ctx = document.getElementById('habitProgressChart').getContext('2d');

    if (habitChart) {
        habitChart.destroy();
    }

    habitChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.alinanlar.map(h => h.ad),
            datasets: [{
                data: data.alinanlar.map(h => h.yuzde),
                backgroundColor: data.alinanlar.map(h => h.renk),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: textColor,
                        padding: 16,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

function renderMonthlyChart(data) {
    const isEmpty = !data || !data.veriler || data.veriler.length === 0 || data.veriler.every(v => v === 0);
    if (handleEmptyState('monthlyChart', isEmpty)) return;
    
    const ctx = document.getElementById('monthlyChart').getContext('2d');

    if (monthlyChart) {
        monthlyChart.destroy();
    }

    monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.etiketler,
            datasets: [{
                label: 'Tamamlanma %',
                data: data.veriler,
                backgroundColor: '#f59e0b',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor
                    }
                },
                y: {
                    min: 0,
                    max: 100,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        callback: value => value + '%'
                    }
                }
            }
        }
    });
}

function renderCalorieChart(data) {
    const isEmpty = !data || !data.veriler || data.veriler.length === 0 || data.veriler.every(v => v === 0);
    if (handleEmptyState('calorieBalanceChart', isEmpty)) return;
    
    const ctx = document.getElementById('calorieBalanceChart').getContext('2d');

    if (calorieChart) {
        calorieChart.destroy();
    }

    const colors = data.veriler.map(v => v >= 0 ? '#10b981' : '#ef4444');

    calorieChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.etiketler,
            datasets: [{
                label: 'Kalori Farkı',
                data: data.veriler,
                backgroundColor: colors,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor,
                        maxTicksLimit: 10
                    }
                },
                y: {
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        callback: value => value + ' kcal'
                    }
                }
            }
        }
    });
}

function renderWeightPrediction(data) {
    if (!data.tahminler) return;

    const getEl = (id) => document.getElementById(id);

    const w1 = getEl('weight1w');
    if (w1) w1.innerHTML = data.tahminler[0].kilo + '<small style="font-size:14px;">kg</small>';
    const wd1 = getEl('weightDiff1w');
    if (wd1) wd1.textContent = (data.tahminler[0].fark >= 0 ? '+' : '') + data.tahminler[0].fark + ' kg';

    const w2 = getEl('weight2w');
    if (w2) w2.innerHTML = data.tahminler[1].kilo + '<small style="font-size:14px;">kg</small>';
    const wd2 = getEl('weightDiff2w');
    if (wd2) wd2.textContent = (data.tahminler[1].fark >= 0 ? '+' : '') + data.tahminler[1].fark + ' kg';

    const w3 = getEl('weight3w');
    if (w3) w3.innerHTML = data.tahminler[2].kilo + '<small style="font-size:14px;">kg</small>';
    const wd3 = getEl('weightDiff3w');
    if (wd3) wd3.textContent = (data.tahminler[2].fark >= 0 ? '+' : '') + data.tahminler[2].fark + ' kg';

    const w4 = getEl('weight4w');
    if (w4) w4.innerHTML = data.tahminler[3].kilo + '<small style="font-size:14px;">kg</small>';
    const wd4 = getEl('weightDiff4w');
    if (wd4) wd4.textContent = (data.tahminler[3].fark >= 0 ? '+' : '') + data.tahminler[3].fark + ' kg';

    const avgIn = getEl('avgCaloriesIn');
    if (avgIn) avgIn.textContent = data.gunluk_ortalama.alinan + ' kcal';
    const avgOut = getEl('avgCaloriesOut');
    if (avgOut) avgOut.textContent = data.gunluk_ortalama.yakilan + ' kcal';
    const avgDiff = getEl('avgCaloriesDiff');
    if (avgDiff) avgDiff.textContent = (data.gunluk_ortalama.fark >= 0 ? '+' : '') + data.gunluk_ortalama.fark + ' kcal';

    // Hedef Kilo Hesaplama Mantığı
    const targetInput = document.getElementById('targetWeightInput');
    const resultBox = document.getElementById('targetGoalResult');
    const daysEl = document.getElementById('targetGoalDays');
    const textEl = document.getElementById('targetGoalText');
    const subtextEl = document.getElementById('targetGoalSubtext');

    if (targetInput) {
        const calculateTarget = () => {
            const targetWeight = parseFloat(targetInput.value);
            const currentWeight = parseFloat(data.mevcut_kilo);
            const dailyDeficit = data.gunluk_ortalama.fark; // Pozitif: Fazla, Negatif: Açık

            if (isNaN(targetWeight) || targetWeight <= 0) {
                resultBox.style.display = 'none';
                return;
            }

            resultBox.style.display = 'block';
            const weightDiff = targetWeight - currentWeight;

            if (Math.abs(weightDiff) < 0.1) {
                daysEl.textContent = 'Hedefinize ulaştınız!';
                textEl.textContent = 'Tebrikler!';
                subtextEl.textContent = 'Mevcut kilonuzu korumaya odaklanın.';
                daysEl.style.color = '#10b981';
                return;
            }

            // 1kg = Yaklaşık 7700 kcal
            const kcalNeeded = Math.abs(weightDiff) * 7700;

            let days = Infinity;
            let status = 'none';

            if (weightDiff < 0) {
                // Kilo verme
                if (dailyDeficit < 0) {
                    days = Math.ceil(kcalNeeded / Math.abs(dailyDeficit));
                    status = 'on-track';
                } else {
                    status = 'wrong-way';
                }
            } else {
                // Kilo alma
                if (dailyDeficit > 0) {
                    days = Math.ceil(kcalNeeded / dailyDeficit);
                    status = 'on-track';
                } else {
                    status = 'wrong-way';
                }
            }

            if (status === 'on-track') {
                daysEl.style.color = '#fbbf24'; // Amber
                daysEl.textContent = days + ' gün kaldı';
                textEl.textContent = targetWeight + ' kg hedefine ulaşmak için:';
                subtextEl.textContent = 'Mevcut kalori dengenizi korumanız yeterli.';
            } else {
                daysEl.style.color = '#f87171'; // Red
                daysEl.textContent = 'Trend uyumsuz';
                textEl.textContent = 'Hedefinizle ters yöndesiniz!';
                subtextEl.textContent = weightDiff < 0
                    ? 'Kilo vermek istiyorsunuz ama ortalama kalori fazlanız var.'
                    : 'Kilo almak istiyorsunuz ama ortalama kalori açığınız var.';
            }
        };

        targetInput.addEventListener('input', calculateTarget);
        if (targetInput.value) calculateTarget();
    }
}

document.getElementById('periodSelect')?.addEventListener('change', () => {
    loadAnalytics();
});

// Wait for DOM to be fully loaded
if (document.getElementById('dailyCompletionChart')) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => loadAnalytics());
    } else {
        // DOM already ready
        setTimeout(() => loadAnalytics(), 100);
    }
}
