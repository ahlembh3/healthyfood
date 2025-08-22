import './bootstrap.js';
import './styles/app.css';
import './styles/theme.css';

(() => {
    const doc = document.documentElement;
    const LS  = window.localStorage;

    // Thème initial
    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
    const savedTheme  = LS.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) doc.classList.add('dark');

    // Taille de police initiale
    const savedScale = parseFloat(LS.getItem('fontScale') || '1');
    if (savedScale !== 1) doc.style.fontSize = (savedScale * 100) + '%';

    // Réduction de mouvements
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
        doc.classList.add('reduce-motion');
    }

    // Raccourcis
    const $ = (sel) => document.querySelector(sel);
    const byId = (id) => document.getElementById(id);

    // ---- FAB : ouvrir/fermer le panneau ----
    const fabBtn   = byId('a11yFab');
    const fabPanel = byId('a11yPanel');

    function toggleFabPanel(force){
        const show = (typeof force === 'boolean') ? force : fabPanel.hasAttribute('hidden');
        if (show) {
            fabPanel.removeAttribute('hidden');
            fabBtn.setAttribute('aria-expanded', 'true');
        } else {
            fabPanel.setAttribute('hidden', '');
            fabBtn.setAttribute('aria-expanded', 'false');
        }
    }
    fabBtn?.addEventListener('click', () => toggleFabPanel());

    // fermer si on clique ailleurs / Esc
    document.addEventListener('click', (e) => {
        if (!fabPanel || fabPanel.hasAttribute('hidden')) return;
        if (e.target.closest('[data-a11y]')) return;
        toggleFabPanel(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') toggleFabPanel(false);
    });

    // ---- A+/A / sombre ----
    const fontUp     = byId('fontUp');
    const fontReset  = byId('fontReset');
    const toggleDark = byId('toggleDark');

    fontUp?.addEventListener('click', () => {
        const current = parseFloat(LS.getItem('fontScale') || '1');
        const next = Math.min(1.6, current + 0.1);
        LS.setItem('fontScale', String(next));
        doc.style.fontSize = (next * 100) + '%';
    });
    fontReset?.addEventListener('click', () => {
        LS.setItem('fontScale', '1');
        doc.style.fontSize = '';
    });
    toggleDark?.addEventListener('click', () => {
        doc.classList.toggle('dark');
        LS.setItem('theme', doc.classList.contains('dark') ? 'dark' : 'light');
    });

    // ---- Lecture vocale (toggle start/stop) ----
    const readBtn = byId('readBtn');
    let isReading = false;
    let utterance = null;

    function setReadingUI(active){
        isReading = active;
        if (!readBtn) return;
        readBtn.setAttribute('aria-pressed', active ? 'true' : 'false');
        readBtn.textContent = active ? '⏹️ Arrêter la lecture' : '🔊 Lecture';
        readBtn.classList.toggle('btn-danger', active);
        readBtn.classList.toggle('btn-outline-secondary', !active);
    }

    function startReading(){
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();

        const container = document.querySelector('main') || document.body;
        const nodes = container.querySelectorAll('h1,h2,h3,article,section,p,li');
        const texts = Array.from(nodes).map(n => n.innerText.trim()).filter(Boolean).slice(0, 250);
        if (!texts.length) return;

        utterance = new SpeechSynthesisUtterance(texts.join('. '));
        utterance.lang = 'fr-FR';
        utterance.onend = utterance.onerror = () => setReadingUI(false);

        window.speechSynthesis.speak(utterance);
        setReadingUI(true);
    }

    function stopReading(){
        try { window.speechSynthesis.cancel(); } catch {}
        setReadingUI(false);
    }

    readBtn?.addEventListener('click', () => {
        if (!('speechSynthesis' in window)) {
            readBtn.disabled = true;
            readBtn.title = 'Lecture non disponible sur ce navigateur';
            return;
        }
        if (isReading) stopReading(); else startReading();
    });

    // Arrêter la synthèse quand on change de page / onglet masqué
    window.addEventListener('beforeunload', stopReading);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stopReading();
    });

    // ---- Fermer le menu hamburger (mobile) après clic ----
    document.addEventListener('click', (e) => {
        const link = e.target.closest('.navbar .nav-link, .navbar .dropdown-item');
        if (!link) return;
        const toggler  = document.querySelector('.navbar-toggler');
        const collapse = document.getElementById('mainNav');
        if (toggler && collapse && collapse.classList.contains('show')) toggler.click();
    });

    // Focus automatique si on arrive sur #main via clavier
    if (window.location.hash === '#main') {
        const main = document.getElementById('main');
        main?.focus({ preventScroll: true });
    }
})();

console.log('UI globale (thème, a11y) chargée depuis assets/app.js');
