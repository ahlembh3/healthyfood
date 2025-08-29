// assets/app.js
import './bootstrap.js';

/**
 * Bind les interactions QUI DÉPENDENT DU DOM DE LA PAGE COURANTE.
 * Appelé à chaque "turbo:load" (et au 1er DOMContentLoaded).
 */
function bindPageUI() {
    const doc = document.documentElement;
    const LS  = window.localStorage;

    // --------- Accessibilité (panel) ----------
    const fabBtn   = document.getElementById('a11yFab');
    const fabPanel = document.getElementById('a11yPanel');

    function toggleFabPanel(force) {
        if (!fabPanel) return;
        const show = (typeof force === 'boolean') ? force : fabPanel.hasAttribute('hidden');
        if (show) {
            fabPanel.removeAttribute('hidden');
            fabBtn?.setAttribute('aria-expanded', 'true');
        } else {
            fabPanel.setAttribute('hidden', '');
            fabBtn?.setAttribute('aria-expanded', 'false');
        }
    }

    if (fabBtn && !fabBtn.dataset.bound) {
        fabBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleFabPanel(); });
        fabBtn.dataset.bound = '1';
    }

    // Fermer si clic hors du bloc data-a11y
    if (!window.__a11yDocBound) {
        document.addEventListener('click', (e) => {
            const panel = document.getElementById('a11yPanel');
            if (!panel || panel.hasAttribute('hidden')) return;
            if (e.target.closest('[data-a11y]')) return;
            panel.setAttribute('hidden', '');
            document.getElementById('a11yFab')?.setAttribute('aria-expanded', 'false');
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') toggleFabPanel(false);
        });
        window.__a11yDocBound = true;
    }

    // Boutons A+/A/dark
    const fontUp    = document.getElementById('fontUp');
    const fontReset = document.getElementById('fontReset');
    const toggleDark= document.getElementById('toggleDark');

    if (fontUp && !fontUp.dataset.bound) {
        fontUp.addEventListener('click', () => {
            const current = parseFloat(LS.getItem('fontScale') || '1');
            const next = Math.min(1.6, current + 0.1);
            LS.setItem('fontScale', String(next));
            doc.style.fontSize = (next * 100) + '%';
        });
        fontUp.dataset.bound = '1';
    }
    if (fontReset && !fontReset.dataset.bound) {
        fontReset.addEventListener('click', () => {
            LS.setItem('fontScale', '1');
            doc.style.fontSize = '';
        });
        fontReset.dataset.bound = '1';
    }
    if (toggleDark && !toggleDark.dataset.bound) {
        toggleDark.addEventListener('click', () => {
            doc.classList.toggle('dark');
            LS.setItem('theme', doc.classList.contains('dark') ? 'dark' : 'light');
        });
        toggleDark.dataset.bound = '1';
    }

    // Lecture vocale
    const readBtn = document.getElementById('readBtn');
    if (readBtn && !readBtn.dataset.bound) {
        let isReading = false;
        let utterance = null;
        function setReadingUI(active){
            isReading = active;
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
        function stopReading(){ try { window.speechSynthesis.cancel(); } catch{} setReadingUI(false); }

        readBtn.addEventListener('click', () => {
            if (!('speechSynthesis' in window)) { readBtn.disabled = true; readBtn.title = 'Lecture non disponible'; return; }
            if (isReading) stopReading(); else startReading();
        });
        window.addEventListener('beforeunload', stopReading);
        document.addEventListener('visibilitychange', () => { if (document.hidden) stopReading(); });

        readBtn.dataset.bound = '1';
    }

    // --------- Menu mobile (hamburger) ----------
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu   = document.getElementById('mainNav') || document.querySelector('.nav-main');

    function closeMenu(){
        navMenu?.classList.remove('active');
        navToggle?.setAttribute('aria-expanded', 'false');
    }

    if (navToggle && !navToggle.dataset.bound) {
        navToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!navMenu) return;
            navMenu.classList.toggle('active');
            navToggle.setAttribute('aria-expanded', navMenu.classList.contains('active') ? 'true' : 'false');
        });
        navToggle.dataset.bound = '1';
    }

    if (navMenu && !navMenu.dataset.boundClicks) {
        navMenu.addEventListener('click', (e) => {
            const link = e.target.closest('a.nav-link');
            if (link) closeMenu();
        });
        navMenu.dataset.boundClicks = '1';
    }

    if (!window.__navDocBound) {
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('mainNav') || document.querySelector('.nav-main');
            const toggle = document.querySelector('.nav-toggle');
            if (!menu || !menu.classList.contains('active')) return;
            if (e.target.closest('.site-header')) return;
            menu.classList.remove('active');
            toggle?.setAttribute('aria-expanded', 'false');
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
        window.__navDocBound = true;
    }
}

/**
 * Init “une seule fois” (préférences utilisateurs, thème initial, motion)
 */
function initOnce() {
    if (window.__uiOnce) return;
    window.__uiOnce = true;

    const doc = document.documentElement;
    const LS  = window.localStorage;

    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
    const savedTheme  = LS.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) doc.classList.add('dark');

    const savedScale = parseFloat(LS.getItem('fontScale') || '1');
    if (savedScale !== 1) doc.style.fontSize = (savedScale * 100) + '%';

    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
        doc.classList.add('reduce-motion');
    }
}

// Avec Turbo : rebinder à chaque navigation
document.addEventListener('turbo:load', () => { initOnce(); bindPageUI(); });
// Cas non Turbo (1er chargement plein)
document.addEventListener('DOMContentLoaded', () => { initOnce(); bindPageUI(); });
