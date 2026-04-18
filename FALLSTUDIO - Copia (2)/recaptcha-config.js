/**
 * reCAPTCHA v3 Configuration and Helper Functions
 */

// La site key verrà caricata dinamicamente dal server
let RECAPTCHA_SITE_KEY = null;
let recaptchaReadyPromise = null;
let recaptchaReadyResolve = null;

// Inizializza la Promise di attesa
recaptchaReadyPromise = new Promise((resolve) => {
    recaptchaReadyResolve = resolve;
});

/**
 * Carica la site key dall'API
 */
async function loadRecaptchaSiteKey() {
    try {
        console.log('reCAPTCHA: Caricamento site key...');
        const response = await fetch('/api/recaptcha-sitekey.php');
        const data = await response.json();

        if (data.siteKey) {
            RECAPTCHA_SITE_KEY = data.siteKey;
            console.log('reCAPTCHA: Site key caricata');
            loadRecaptchaScript();
        } else {
            console.error('reCAPTCHA: Site key non trovata nella risposta API');
        }
    } catch (error) {
        console.error('reCAPTCHA: Errore nel caricamento della site key:', error);
    }
}

/**
 * Carica lo script di Google reCAPTCHA v3
 */
function loadRecaptchaScript() {
    if (!RECAPTCHA_SITE_KEY) return;

    if (document.querySelector('script[src*="recaptcha/api.js"]')) return;

    const script = document.createElement('script');
    script.src = `https://www.google.com/recaptcha/api.js?render=${RECAPTCHA_SITE_KEY}`;
    script.async = true;
    script.defer = true;

    script.onload = () => {
        console.log('reCAPTCHA: Script Google caricato');
        grecaptcha.ready(() => {
            console.log('reCAPTCHA: Pronto per l\'esecuzione');
            if (recaptchaReadyResolve) recaptchaReadyResolve();
        });
    };

    script.onerror = () => {
        console.error('reCAPTCHA: Errore nel caricamento dello script Google');
    };

    document.head.appendChild(script);
}

/**
 * Esegue la verifica reCAPTCHA
 */
async function executeRecaptcha(action) {
    // Aspetta che sia pronto
    await recaptchaReadyPromise;

    if (typeof grecaptcha === 'undefined') {
        throw new Error('reCAPTCHA non caricato correttamente');
    }

    return new Promise((resolve, reject) => {
        grecaptcha.ready(async () => {
            try {
                const token = await grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: action });
                resolve(token);
            } catch (error) {
                reject(error);
            }
        });
    });
}

function isRecaptchaReady() {
    return typeof grecaptcha !== 'undefined' && RECAPTCHA_SITE_KEY !== null;
}

// Esporta helper globale
window.recaptchaHelper = {
    execute: executeRecaptcha,
    isReady: isRecaptchaReady,
    whenReady: () => recaptchaReadyPromise
};

// Avvia caricamento
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadRecaptchaSiteKey);
} else {
    loadRecaptchaSiteKey();
}
