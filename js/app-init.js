// Init - DOM hazır olduğunda çalıştır
function initApp() {
    console.log('DOM hazır, init çağrılıyor...');
    console.log('habitsContainer:', habitsContainer);
    console.log('emptyState:', emptyState);

    if (habitsContainer) {
        console.log('loadHabits çağrılıyor...');
        loadHabits();
    } else {
        console.error('habitsContainer bulunamadı!');
    }

    if (historyContainer && historyDate) {
        loadHistory(historyDate.value);
    }
}

console.log('Script yüklendi');

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
