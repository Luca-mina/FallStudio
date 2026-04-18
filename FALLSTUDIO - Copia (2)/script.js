// Inizializzazione quando la pagina è caricata
function initSite() {
    try {
        // Prevent scroll during initial load
        document.body.style.overflow = 'hidden';



        // Rimuovi toolbar/iframe di AlterVista se presente
        (function removeAltervistaToolbar() {
            function tryRemove() {
                const elements = [
                    document.getElementById('av_toolbar_iframe'),
                    document.querySelector('iframe[src*="tb.altervista.org"]'),
                    document.getElementById('av_toolbar_regdiv'),
                    document.querySelector('[id^="av_toolbar_"]')
                ];
                let removed = false;
                elements.forEach(el => { if (el) { el.remove(); removed = true; } });
                return removed;
            }
            tryRemove();
            const observer = new MutationObserver(() => { if (tryRemove()) observer.disconnect(); });
            observer.observe(document.documentElement || document.body, { childList: true, subtree: true });
            setTimeout(() => observer.disconnect(), 7000);
        })();

        // Informativa Cookie (GDPR Compliance - Versione Completa)
        (function initCookieBanner() {
            const cookieStatus = localStorage.getItem('cookieConsent');
            if (cookieStatus) return; // Non mostrare se già scelto

            const banner = document.createElement('div');
            banner.id = 'cookie-consent-banner';
            banner.className = 'cookie-banner';
            banner.innerHTML = `
                <div class="cookie-banner-content">
                    <div class="cookie-banner-text">
                        <p><strong>Rispettiamo la tua Privacy</strong></p>
                        <p>Utilizziamo i cookie per migliorare la tua esperienza, analizzare il traffico e personalizzare i contenuti. <a href="cookies-policy.html" target="_blank">Scopri di più</a></p>
                    </div>
                    <div class="cookie-banner-buttons">
                        <button class="btn-cookie btn-reject" data-accept="false">Rifiuta</button>
                        <button class="btn-cookie btn-settings" data-settings="true">Personalizza</button>
                        <button class="btn-cookie btn-accept" data-accept="true">Accetta Tutto</button>
                    </div>
                </div>

                <div class="cookie-preferences hidden" id="cookie-preferences">
                    <div class="preferences-content">
                        <h3>Preferenze Cookie</h3>
                        <p>Scegli quali cookie desideri accettare:</p>
                        
                        <div class="preference-item">
                            <label>
                                <input type="checkbox" name="essential" checked disabled>
                                <span><strong>Essenziali</strong> - Necessari per il funzionamento del sito</span>
                            </label>
                        </div>
                        
                        <div class="preference-item">
                            <label>
                                <input type="checkbox" name="analytics">
                                <span><strong>Analitici</strong> - Analizzare come usi il sito</span>
                            </label>
                        </div>
                        
                        <div class="preference-item">
                            <label>
                                <input type="checkbox" name="marketing">
                                <span><strong>Marketing</strong> - Mostrarti annunci pertinenti</span>
                            </label>
                        </div>

                        <div class="preferences-buttons">
                            <button class="btn-cookie btn-prefs-cancel">Annulla</button>
                            <button class="btn-cookie btn-prefs-save">Salva Preferenze</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(banner);

            // Event Listeners
            const btnAccept = banner.querySelector('.btn-accept');
            const btnReject = banner.querySelector('.btn-reject');
            const btnSettings = banner.querySelector('.btn-settings');
            const btnPrefsCancel = banner.querySelector('.btn-prefs-cancel');
            const btnPrefsSave = banner.querySelector('.btn-prefs-save');
            const prefsContainer = banner.querySelector('#cookie-preferences');

            btnAccept.addEventListener('click', () => {
                localStorage.setItem('cookieConsent', JSON.stringify({
                    timestamp: new Date().toISOString(),
                    essential: true,
                    analytics: true,
                    marketing: true
                }));
                closeBanner();
                loadGoogleAnalytics();
                initBehaviorTracking();
            });

            btnReject.addEventListener('click', () => {
                localStorage.setItem('cookieConsent', JSON.stringify({
                    timestamp: new Date().toISOString(),
                    essential: true,
                    analytics: false,
                    marketing: false
                }));
                closeBanner();
            });

            btnSettings.addEventListener('click', () => {
                banner.querySelector('.cookie-banner-content').classList.add('hidden');
                prefsContainer.classList.remove('hidden');
            });

            btnPrefsCancel.addEventListener('click', () => {
                banner.querySelector('.cookie-banner-content').classList.remove('hidden');
                prefsContainer.classList.add('hidden');
            });

            btnPrefsSave.addEventListener('click', () => {
                const analytics = prefsContainer.querySelector('input[name="analytics"]').checked;
                const marketing = prefsContainer.querySelector('input[name="marketing"]').checked;

                localStorage.setItem('cookieConsent', JSON.stringify({
                    timestamp: new Date().toISOString(),
                    essential: true,
                    analytics: analytics,
                    marketing: marketing
                }));
                closeBanner();

                if (analytics) {
                    loadGoogleAnalytics();
                    initBehaviorTracking();
                }
            });

            function closeBanner() {
                banner.style.animation = 'slideDown 0.3s ease forwards';
                setTimeout(() => banner.remove(), 300);
            }
        })();

        // Menu Mobile (robusto, null-safe e con log di debug)
        const menuToggle = document.querySelector('.menu-toggle');
        const mainNav = document.querySelector('.main-nav');

        if (menuToggle) {
            // assicurati che il toggle sia sempre cliccabile
            try {
                menuToggle.style.zIndex = '1101';
            } catch (e) { }

            // Stato aria iniziale
            menuToggle.setAttribute('aria-expanded', 'false');
            if (mainNav) mainNav.setAttribute('aria-hidden', 'true');

            console.log('menuToggle inizializzato');

            const openMenu = () => {
                if (!mainNav) return;
                mainNav.classList.add('active');
                menuToggle.setAttribute('aria-expanded', 'true');
                mainNav.setAttribute('aria-hidden', 'false');
                const icon = menuToggle.querySelector('i');
                if (icon && icon.classList) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                }
                document.body.style.overflow = 'hidden';
                // sposta il focus sul primo link del menu
                const firstLink = mainNav.querySelector('a');
                if (firstLink) firstLink.focus();
                console.log('menu aperto');
            };

            const closeMenu = (returnFocus = true) => {
                if (!mainNav) return;
                mainNav.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                mainNav.setAttribute('aria-hidden', 'true');
                const icon = menuToggle.querySelector('i');
                if (icon && icon.classList) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
                document.body.style.overflow = '';
                if (returnFocus) menuToggle.focus();
                console.log('menu chiuso');
            };

            // Supporto click, touch e tastiera (Enter/Space)
            const onToggleActivate = (e) => {
                if (e) e.stopPropagation();
                if (!mainNav) return console.warn('mainNav non trovato');
                if (mainNav.classList.contains('active')) closeMenu(); else openMenu();
            };

            menuToggle.addEventListener('click', onToggleActivate);
            menuToggle.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    onToggleActivate();
                }
            });

            // Chiudi menu con ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && mainNav && mainNav.classList.contains('active')) {
                    closeMenu();
                }
            });

            // Chiudi cliccando fuori dal menu (solo quando è aperto)
            document.addEventListener('click', (e) => {
                if (!mainNav || !mainNav.classList.contains('active')) return;
                if (e.target === menuToggle || menuToggle.contains(e.target)) return;
                if (mainNav.contains(e.target)) return;
                closeMenu(false);
            });

            // Chiudi menu cliccando su link (anchor)
            if (mainNav) {
                const navLinks = mainNav.querySelectorAll('a');
                navLinks.forEach(link => link.addEventListener('click', () => closeMenu()));
            }
        } else {
            console.warn('menuToggle element not found');
        }

        // Chiudi menu cliccando su link (null-safe)
        if (mainNav) {
            const navLinks = mainNav.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    mainNav.classList.remove('active');
                    const icon = menuToggle ? menuToggle.querySelector('i') : null;
                    if (icon && icon.classList) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                    document.body.style.overflow = '';
                });
            });
        }

        // Ricerca
        const searchBtn = document.querySelector('.search-btn');
        const searchBar = document.querySelector('.search-bar');
        const searchClose = document.querySelector('.search-close');
        const searchInput = searchBar ? searchBar.querySelector('input') : null;
        const productsGrid = document.querySelector('.products-grid');
        const productCards = document.querySelectorAll('.product-card');

        if (searchBtn && searchBar) {
            searchBtn.addEventListener('click', () => {
                searchBar.classList.add('active');
                if (searchInput) searchInput.focus();
                if (searchInput) searchInput.value = '';
                // Mostra tutti i prodotti (null-safe)
                productCards.forEach(card => {
                    if (card && card.style) card.style.display = 'block';
                });
            });

            if (searchClose) {
                searchClose.addEventListener('click', () => {
                    searchBar.classList.remove('active');
                    if (searchInput) searchInput.value = '';
                    // Mostra tutti i prodotti (null-safe)
                    productCards.forEach(card => {
                        if (card && card.style) card.style.display = 'block';
                    });
                });
            }

            // Ricerca in tempo reale
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase().trim();

                    productCards.forEach(card => {
                        const productTitle = card.querySelector('h3') ? card.querySelector('h3').textContent.toLowerCase() : '';
                        const productDesc = card.querySelector('.product-description') ? card.querySelector('.product-description').textContent.toLowerCase() : '';

                        if (productTitle.includes(searchTerm) || productDesc.includes(searchTerm)) {
                            if (card && card.style) card.style.display = 'block';
                        } else {
                            if (card && card.style) card.style.display = 'none';
                        }
                    });
                });
            }

            // Chiudi ricerca con ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && searchBar && searchBar.classList.contains('active')) {
                    searchBar.classList.remove('active');
                    if (searchInput) searchInput.value = '';
                    // Mostra tutti i prodotti
                    productCards.forEach(card => {
                        card.style.display = 'block';
                    });
                }
            });
        }

        // Effetto hover per il cambio immagine dei prodotti
        productCards.forEach(card => {
            const img = card.querySelector('.product-image img');
            if (img && img.hasAttribute('data-hover-src')) {
                const originalSrc = img.src;
                const hoverSrc = img.getAttribute('data-hover-src');

                card.addEventListener('mouseenter', () => {
                    img.style.opacity = '0.7';
                    setTimeout(() => {
                        img.src = hoverSrc;
                        img.style.opacity = '1';
                    }, 150);
                });

                card.addEventListener('mouseleave', () => {
                    img.style.opacity = '0.7';
                    setTimeout(() => {
                        img.src = originalSrc;
                        img.style.opacity = '1';
                    }, 150);
                });
            }
        });

        // Quick View Modal
        const quickViewButtons = document.querySelectorAll('.quick-view');
        const modal = document.querySelector('.quick-view-modal');
        const modalClose = document.querySelector('.modal-close');

        if (!modal) {
            console.warn('Modal element not found');
        }

        // Dati prodotti per il modal
        const productsData = {
            1: {
                title: 'HOODIE Brick "Don\'t Fall"',
                description: 'Hoodie in 100% cotone, taglio relaxed fit. Presenta doppia tecnica di ricamo (double-technique embroidery), tasche kangaroo frontali, e ricamo su entrambi i polsini. Un capo versatile e confortevole.',
                details: ['Relaxed Fit', 'Kangaroo Pocket', 'Double-Technique Embroidery', '100% Cotton', 'Embroidery on Both Cuffs'],
                colors: ['Nero', 'Grigio antracite', 'Beige', 'Verde militare'],
                images: ['immgini/FelpaRossaFronte.png', 'immgini/FelpaRossaRetro.png','FelpaRossaDalila.JPG','https://lh3.googleusercontent.com/d/1YCDUEXSKQOIsRaFHrAAs79CG9ugwLuH0']
            },
            2: {
                title: 'HOODIE Mezzanotte "Don\'t Fall"',
                description: 'Hoodie in 100% cotone, taglio relaxed fit. Presenta doppia tecnica di ricamo (double-technique embroidery), tasche kangaroo frontali, e ricamo su entrambi i polsini. Un capo versatile e confortevole.',
                details: ['Relaxed Fit', 'Kangaroo Pocket', 'Double-Technique Embroidery', '100% Cotton', 'Embroidery on Both Cuffs'],
                colors: ['Nero', 'Grigio antracite', 'Beige', 'Verde militare'],
                images: ['immgini/FelpaBluFronte.png', 'immgini/FelpaBluRetro.png','https://lh3.googleusercontent.com/d/1UAOXAC-O3JoPv2C7C-Yjo5gawRCXheNZ','PantaloniBluDalila.JPG']
            },
            3: {
                title: 'T-Shirt Fall001 ',
                description: 'T-shirt in cotone pettinato 230gsm, stampa serigrafica fronte e retro, taglio large fit, finiture rinforzate. Grafica esclusiva disegnata in house.',
                details: ['100% cotone pettinato', 'Stampa serigrafica', 'Taglio large fit', 'Collo rinforzato', 'Lavabile a 30°'],
                colors: ['Bianco'],
                images: ['immgini/MgliettaFronte_.png', 'immgini/MgliettaRetrp.jpg']
            },
            4: {
                title: 'Pants Brick "Don\'t Fall"',
                description: 'Pants cargo in twill di cotone 340gsm, multiple tasche funzionali (2 laterali, 2 posteriori con bottone, 1 coscia), fit relaxed, passanti per cintura in pelle.',
                details: ['RELAXED FIT', 'KANGAROO POCKET', 'DOUBLE-TECHNIQUE EMBROIDERY', '100% COTTON', ' Embroidery on both cuffs'],
                colors: ['Verde militare', 'Nero', 'Beige', 'Grigio antracite'],
                images: ['immgini/PantaloniRossoFronte.png', 'immgini/PantaloniRossoRetro.png','FelpaRossaDalila.JPG','https://lh3.googleusercontent.com/d/1YCDUEXSKQOIsRaFHrAAs79CG9ugwLuH0']
            },
            5: {
                title: 'Pants Mezzanotte "Don\'t Fall"',
                description: 'Pants cargo in twill di cotone 340gsm, multiple tasche funzionali (2 laterali, 2 posteriori con bottone, 1 coscia), fit relaxed, passanti per cintura in pelle.',
                details: ['RELAXED FIT', 'KANGAROO POCKET', 'DOUBLE-TECHNIQUE EMBROIDERY', '100% COTTON', ' Embroidery on both cuffs'],
                colors: ['Verde militare', 'Nero', 'Beige', 'Grigio antracite'],
                images: ['immgini/PantaloniBluFronte.png', 'immgini/PantaloniBluRetro.jpg','https://lh3.googleusercontent.com/d/1UAOXAC-O3JoPv2C7C-Yjo5gawRCXheNZ','PantaloniBluDalila.JPG']
            },
            6: {
                title: 'Bracelet Chain Fall Studio ',
                description: 'Cappello beanie in lana merinos extrafine, ricamo logo fronte in filo nero, unisex, versatile per tutte le stagioni fredde. Confezionato in pouch di cotone.',
                details: ['-More than bracelets', '-FALL STUDIO ESSENTials', '-Built to last, limited pieces '],
                colors: ['Metall'],
                images: ['immgini/braccialetto.jpg', 'immgini/braccailetto2.png', 'immgini/braccialetto3.jpg', 'immgini/braccialetto4.jpg']
            },
            7: {
                title: 'Neckless Fall Studio',
                description: 'Cappello beanie in lana merinos extrafine, ricamo logo fronte in filo nero, unisex, versatile per tutte le stagioni fredde. Confezionato in pouch di cotone.',
                details: ['-More than neckless', '-FALL STUDIO ESSENTials', '-Built to last, limited pieces '],
                colors: ['Metall'],
                images: ['immgini/neckless1.jpeg']
            }
        };

        // Funzioni centralizzate per il modal (open/close con transizione di uscita)
        function openModal() {
            if (!modal) return;
            modal.classList.remove('closing');
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            console.log('Quick View: modal aperto');
        }

        function closeModal() {
            if (!modal) return;
            // Aggiungi classe di closing per permettere animazione
            modal.classList.add('closing');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            // Dopo la transizione rimuovi lo stato active
            setTimeout(() => {
                modal.classList.remove('active');
                modal.classList.remove('closing');
                console.log('Quick View: modal chiuso');
            }, 220);
        }

        quickViewButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productCard = this.closest('.product-card');
                const productId = productCard && (productCard.getAttribute('data-product') || (productCard.querySelector('.btn-add-to-cart') && productCard.querySelector('.btn-add-to-cart').getAttribute('data-product')));
                const product = productsData[productId];

                if (product && modal) {
                    const modalBody = modal.querySelector('.modal-body');

                    // Escape helper per evitare rotture HTML causate dalle virgolette nel title
                    const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                    // Prepara fallback o array immagini
                    const imagesArr = (product.images && product.images.length > 0) ? product.images : [productCard.querySelector('img').src];
                    const hasCarousel = imagesArr.length > 1;

                    modalBody.innerHTML = `
                    <div class="modal-product modal-grid">
                        <div class="modal-images">
                            <div class="carousel-container">
                                <div class="carousel-track">
                                    ${imagesArr.map((src, idx) => `
                                        <div class="carousel-slide">
                                            <img src="${src}" alt="${esc(product.title)} - ${idx + 1}">
                                        </div>
                                    `).join('')}
                                </div>
                                ${hasCarousel ? `
                                <button class="carousel-btn prev" aria-label="Precedente"><i class="fas fa-chevron-left"></i></button>
                                <button class="carousel-btn next" aria-label="Successivo"><i class="fas fa-chevron-right"></i></button>
                                <div class="carousel-dots">
                                    ${imagesArr.map((_, idx) => `<span class="carousel-dot ${idx === 0 ? 'active' : ''}" data-index="${idx}"></span>`).join('')}
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        <div class="modal-info">
                            <h2>${product.title}</h2>
                            <p class="modal-description">${product.description}</p>
                            <div class="modal-details">
                                <h4>Dettagli prodotto:</h4>
                                <ul>
                                    ${product.details.map(detail => `<li>${detail}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    </div>
                `;

                    // Logic per Carosello
                    if (hasCarousel) {
                        let currentIndex = 0;
                        const track = modalBody.querySelector('.carousel-track');
                        const slides = modalBody.querySelectorAll('.carousel-slide');
                        const prevBtn = modalBody.querySelector('.carousel-btn.prev');
                        const nextBtn = modalBody.querySelector('.carousel-btn.next');
                        const dots = modalBody.querySelectorAll('.carousel-dot');

                        const updateCarousel = (index) => {
                            if (index < 0) index = slides.length - 1;
                            if (index >= slides.length) index = 0;
                            currentIndex = index;
                            track.style.transform = `translateX(-${currentIndex * 100}%)`;
                            dots.forEach(dot => dot.classList.remove('active'));
                            if (dots[currentIndex]) dots[currentIndex].classList.add('active');
                        };

                        if (prevBtn) prevBtn.addEventListener('click', () => updateCarousel(currentIndex - 1));
                        if (nextBtn) nextBtn.addEventListener('click', () => updateCarousel(currentIndex + 1));
                        dots.forEach(dot => {
                            dot.addEventListener('click', (e) => {
                                updateCarousel(parseInt(e.target.getAttribute('data-index')));
                            });
                        });
                    }


                    // Sanitize difensivo
                    try {
                        ['.product-price', '.price', '.sizes', '.size-options', '.btn-add-to-cart', '.btn-wishlist', '.wishlist', '.add-to-favorites'].forEach(sel => {
                            modalBody.querySelectorAll(sel).forEach(n => n.remove());
                        });
                    } catch (err) { console.warn('sanitizeModal error:', err); }

                    openModal();
                }
            });
        });

        // Chiudi modal
        if (modalClose && modal) {
            modalClose.addEventListener('click', () => {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        // Chiudi modal cliccando fuori
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }

        // Chiudi modal con ESC
        if (modal) {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }

        // Newsletter Form con reCAPTCHA e validazione MX
        const newsletterForm = document.querySelector('.newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const emailInput = this.querySelector('input[type="email"]');
                const btn = this.querySelector('button');
                const email = emailInput.value.trim();

                if (!validateEmail(email)) {
                    showNewsletterFeedback(this, 'Per favora inserisci un email valida.', 'error');
                    return;
                }

                // Mostra stato loading
                const originalButton = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifica...';
                    btn.disabled = true;
                }

                try {
                    // 1. Genera token reCAPTCHA
                    let recaptchaToken = null;
                    if (window.recaptchaHelper) {
                        try {
                            // Questo ora aspetta automaticamente che reCAPTCHA sia pronto
                            recaptchaToken = await window.recaptchaHelper.execute('newsletter');
                        } catch (e) {
                            console.warn('reCAPTCHA non pronto:', e);
                        }
                    }

                    // 2. Invia richiesta al server
                    const response = await fetch('/api/newsletter.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: email,
                            recaptchaToken: recaptchaToken
                        })
                    });

                    const data = await response.json();

                    if (response.ok) { // Successo: email registrata
                        if (emailInput) emailInput.value = '';

                        if (btn) {
                            btn.innerHTML = '<i class="fas fa-check"></i> Iscritto!';
                            btn.style.backgroundColor = '#4CAF50';
                            btn.style.color = 'white';
                            btn.classList.add('btn-success-animate');
                        }

                        // Mostra coriandoli!
                        if (typeof showSuccessAnimation === 'function') {
                            showSuccessAnimation('Iscritto!', btn);
                        }

                        setTimeout(() => {
                            if (btn) {
                                btn.innerHTML = originalButton;
                                btn.style.backgroundColor = '';
                                btn.disabled = false;
                                btn.classList.remove('btn-success-animate');
                            }
                        }, 3000);
                    } else {
                        // Errore dal server
                        let errorMsg = 'Errore durante l\'iscrizione.';

                        if (data.reason === 'INVALID_DOMAIN') {
                            errorMsg = 'Il dominio email non è valido o non esiste.';
                        } else if (data.reason === 'INVALID_FORMAT') {
                            errorMsg = 'Formato email non valido.';
                        } else if (data.reason === 'CAPTCHA_FAILED') {
                            errorMsg = 'Verifica di sicurezza fallita. Riprova.';
                        } else if (data.error) {
                            errorMsg = data.error;
                        }

                        showNewsletterFeedback(this, errorMsg, 'error');

                        if (btn) {
                            btn.innerHTML = originalButton;
                            btn.disabled = false;
                        }
                    }
                } catch (error) {
                    console.error('Errore newsletter:', error);
                    showNewsletterFeedback(this, 'Errore di connessione. Riprova più tardi.', 'error');

                    if (btn) {
                        btn.innerHTML = originalButton;
                        btn.disabled = false;
                    }
                }
            });
        }

        // Helper per mostrare feedback newsletter
        function showNewsletterFeedback(form, message, type) {
            // Rimuovi feedback precedenti
            const oldFeedback = form.querySelector('.newsletter-feedback');
            if (oldFeedback) oldFeedback.remove();

            const feedback = document.createElement('div');
            feedback.className = `newsletter - feedback newsletter - feedback - ${type} `;
            feedback.textContent = message;
            feedback.style.cssText = `
                            margin - top: 0.5rem;
                            padding: 0.5rem;
                            border - radius: 4px;
                            font - size: 0.875rem;
                            text - align: center;
                            background - color: ${type === 'success' ? '#d4edda' : '#f8d7da'};
                            color: ${type === 'success' ? '#155724' : '#721c24'};
                            border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
                            `;

            form.appendChild(feedback);

            // Auto-rimuovi dopo 5 secondi
            setTimeout(() => {
                if (feedback.parentNode) feedback.remove();
            }, 5000);
        }

        // Contact Form con reCAPTCHA e validazione MX
        const contactForm = document.querySelector('.contact-form');
        if (contactForm) {
            contactForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const nameInput = this.querySelector('input[type="text"]');
                const emailInput = this.querySelector('input[type="email"]');
                const messageInput = this.querySelector('textarea');
                const btn = this.querySelector('button[type="submit"]');

                const name = nameInput ? nameInput.value.trim() : '';
                const email = emailInput ? emailInput.value.trim() : '';
                const message = messageInput ? messageInput.value.trim() : '';

                // Validazione campi
                if (!name || !email || !message) {
                    showContactFeedback(this, 'Tutti i campi sono obbligatori.', 'error');
                    return;
                }

                if (!validateEmail(email)) {
                    showContactFeedback(this, 'Per favore inserisci un email valida.', 'error');
                    return;
                }

                // Mostra stato loading
                const originalButton = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Invio in corso...';
                    btn.disabled = true;
                }

                try {
                    // 1. Genera token reCAPTCHA
                    let recaptchaToken = null;
                    if (window.recaptchaHelper) {
                        try {
                            recaptchaToken = await window.recaptchaHelper.execute('contact');
                        } catch (e) {
                            console.warn('reCAPTCHA non pronto:', e);
                        }
                    }

                    // 2. Invia richiesta al server
                    const response = await fetch('/api/contact.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: name,
                            email: email,
                            message: message,
                            recaptchaToken: recaptchaToken
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Successo - pulisci form
                        if (nameInput) nameInput.value = '';
                        if (emailInput) emailInput.value = '';
                        if (messageInput) messageInput.value = '';

                        if (btn) {
                            btn.innerHTML = '<i class="fas fa-check"></i> Inviato!';
                            btn.style.backgroundColor = '#4CAF50';
                        }
                        showContactFeedback(this, data.message || 'Messaggio inviato con successo!', 'success');

                        setTimeout(() => {
                            if (btn) {
                                btn.innerHTML = originalButton;
                                btn.style.backgroundColor = '';
                                btn.disabled = false;
                            }
                        }, 3000);
                    } else {
                        // Errore dal server
                        let errorMsg = 'Errore durante l\'invio.';

                        if (data.reason === 'INVALID_DOMAIN') {
                            errorMsg = 'Il dominio email non è valido o non esiste.';
                        } else if (data.reason === 'INVALID_FORMAT') {
                            errorMsg = 'Formato email non valido.';
                        } else if (data.reason === 'CAPTCHA_FAILED') {
                            errorMsg = 'Verifica di sicurezza fallita. Riprova.';
                        } else if (data.error) {
                            errorMsg = data.error;
                        }

                        showContactFeedback(this, errorMsg, 'error');

                        if (btn) {
                            btn.innerHTML = originalButton;
                            btn.disabled = false;
                        }
                    }
                } catch (error) {
                    console.error('Errore contact form:', error);
                    showContactFeedback(this, 'Errore di connessione. Riprova più tardi.', 'error');

                    if (btn) {
                        btn.innerHTML = originalButton;
                        btn.disabled = false;
                    }
                }
            });
        }

        // Helper per mostrare feedback contact form
        function showContactFeedback(form, message, type) {
            // Rimuovi feedback precedenti
            const oldFeedback = form.querySelector('.contact-feedback');
            if (oldFeedback) oldFeedback.remove();

            const feedback = document.createElement('div');
            feedback.className = `contact - feedback contact - feedback - ${type} `;
            feedback.textContent = message;
            feedback.style.cssText = `
                            margin - top: 1rem;
                            padding: 1rem;
                            border - radius: 4px;
                            font - size: 0.875rem;
                            text - align: center;
                            background - color: ${type === 'success' ? '#d4edda' : '#f8d7da'};
                            color: ${type === 'success' ? '#155724' : '#721c24'};
                            border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
                            `;

            form.appendChild(feedback);

            // Auto-rimuovi dopo 5 secondi
            setTimeout(() => {
                if (feedback.parentNode) feedback.remove();
            }, 5000);
        }

        // Animazione conteggio carrello
        const style = document.createElement('style');
        style.textContent = `
                            @keyframes bounce {
                                0 %, 100 % { transform: scale(1); }
                                50 % { transform: scale(1.3); }
                            }
                            `;
        document.head.appendChild(style);

        // Helper functions
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Show a transient success toast + confetti burst
        function showSuccessAnimation(message, originElem) {
            try {
                // Toast
                const toast = document.createElement('div');
                toast.className = 'success-toast';
                toast.innerHTML = `< i class="fas fa-check" ></i > <div>${message}</div>`;
                document.body.appendChild(toast);

                // Compute origin for confetti (~ around button)
                let rect = { left: window.innerWidth / 2, top: window.innerHeight - 80 };
                if (originElem && originElem.getBoundingClientRect) {
                    const r = originElem.getBoundingClientRect();
                    rect = { left: r.left + r.width / 2, top: r.top + r.height / 2 };
                }

                const colors = ['#ff3b30', '#ff9500', '#ffd60a', '#32d74b', '#64d2ff', '#5856d6'];

                for (let i = 0; i < 15; i++) {
                    const c = document.createElement('div');
                    c.className = 'confetti';
                    c.style.background = colors[Math.floor(Math.random() * colors.length)];
                    const offsetX = (Math.random() - 0.5) * 120;
                    const startX = rect.left + offsetX;
                    const startY = rect.top - 10 + (Math.random() - 0.5) * 20;
                    c.style.left = `${startX} px`;
                    c.style.top = `${startY} px`;
                    c.style.transform = `translateY(0) rotate(${Math.random() * 360}deg)`;
                    c.style.animationDuration = `${900 + Math.floor(Math.random() * 500)} ms`;
                    c.style.opacity = '1';
                    document.body.appendChild(c);
                    setTimeout(() => { c.remove(); }, 1600 + Math.random() * 400);
                }

                setTimeout(() => { toast.style.transition = 'opacity 300ms'; toast.style.opacity = '0'; }, 1800);
                setTimeout(() => { toast.remove(); }, 2200);
            } catch (e) {
                console.warn('Animation error', e);
            }
        }

        function getColorHex(colorName) {
            const colors = {
                'Nero': '#000000',
                'Bianco': '#ffffff',
                'Grigio antracite': '#424242',
                'Grigio chiaro': '#bdbdbd',
                'Beige': '#f5f5dc',
                'Verde militare': '#4b5320',
                'Verde scuro': '#006400',
                'Bordeaux': '#800000'
            };
            return colors[colorName] || '#cccccc';
        }

        // Carrello rimosso: non inizializziamo più lo stato dal localStorage

        // Schermata di caricamento: rimane almeno 1s, poi attende il caricamento completo di tutte le immagini (max 7s)
        function hideLoader() {
            const loader = document.getElementById('loading-screen');
            const image = document.getElementById('loading-image');
            if (!loader) {
                document.body.style.overflow = '';
                return;
            }

            // Mantieni lo scroll disabilitato fino a rimozione completa
            document.body.style.overflow = 'hidden';

            // Attiva anche la transizione del logo (opzionale)
            document.body.classList.add('loading-fade');

            // Avvia il fade-out dell'immagine (0.7s) con fallback robusto
            if (image) {
                console.log('Avvio fade immagine di caricamento');
                if (image.classList && typeof image.classList.add === 'function') {
                    image.classList.add('fade-out-image');
                } else if (image.style) {
                    console.log('Avvio fade immagine (fallback)');
                    image.style.transition = 'opacity 0.7s ease';
                    image.style.opacity = '0';
                }
            } else {
                console.warn('Elemento immagine di loading non trovato');
            }

            // Dopo 700ms rimuove il loader e ripristina lo scroll
            setTimeout(() => {
                if (loader) {
                    // Nascondi per screen reader e avvia la transizione di scomparsa del layer
                    loader.setAttribute('aria-hidden', 'true');
                    loader.classList.add('hidden');
                    // Piccolo buffer per far completare eventuale transizione di #loading-screen
                    setTimeout(() => {
                        if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
                        document.body.style.overflow = '';
                        document.body.classList.remove('loading-fade');
                        console.log('Loader rimosso');
                    }, 200);
                } else {
                    document.body.style.overflow = '';
                    document.body.classList.remove('loading-fade');
                }
            }, 700);
        }

        const maxTimeout = 7000;
        let loaderHidden = false;

        // Questo sarà il momento in cui initSite viene chiamato
        const initTime = Date.now();

        function tryHideLoader() {
            if (loaderHidden) return;
            loaderHidden = true;

            // Assicuriamoci che l'animazione stia a schermo almeno 1 secondo (per estetica)
            const elapsed = Date.now() - initTime;
            const remainingTime = Math.max(0, 1000 - elapsed);

            setTimeout(hideLoader, remainingTime);
        }

        function preloadAllImages() {
            return new Promise((resolve) => {
                const urlsToPreload = new Set();

                // 1. Immagini nel DOM
                const domImages = Array.from(document.querySelectorAll('img'));
                domImages.forEach(img => {
                    // Forza il caricamento eager nel DOM
                    if (img.loading === 'lazy') {
                        img.loading = 'eager';
                    }
                    if (img.src) urlsToPreload.add(img.src);
                    if (img.dataset.hoverSrc) urlsToPreload.add(img.dataset.hoverSrc);
                });

                // 2. Immagini nel modal (productsData)
                if (typeof productsData !== 'undefined') {
                    for (const key in productsData) {
                        const prod = productsData[key];
                        if (prod.images && Array.isArray(prod.images)) {
                            prod.images.forEach(src => urlsToPreload.add(src));
                        }
                    }
                }

                // Pulizia URL validi
                const urlsArray = Array.from(urlsToPreload).filter(url => url && url.trim() !== '');
                let totalImages = urlsArray.length;
                let loadedCount = 0;

                if (totalImages === 0) {
                    resolve();
                    return;
                }

                function checkDone() {
                    loadedCount++;
                    if (loadedCount >= totalImages) {
                        resolve();
                    }
                }

                // Precaricamento effettivo tramite nuovo oggetto Image
                urlsArray.forEach(url => {
                    const img = new Image();
                    img.addEventListener('load', checkDone, { once: true });
                    img.addEventListener('error', checkDone, { once: true });
                    img.src = url;
                });
            });
        }

        // Avvia il precaricamento attivo
        const safetyTimer = setTimeout(tryHideLoader, maxTimeout);

        preloadAllImages().then(() => {
            clearTimeout(safetyTimer);
            tryHideLoader();
        });

        console.log('Falls Studio - E-commerce loaded');
    } catch (e) {
        console.error('initSite error:', e);
    }
}

// Carica Google Analytics se consenso dato
function loadGoogleAnalytics() {
    const consent = localStorage.getItem('cookieConsent');
    if (!consent) return;

    const parsedConsent = JSON.parse(consent);
    if (!parsedConsent.analytics) return;

    // Google Analytics (cambia con il tuo ID)
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', 'GTM-MNXKJNG5', {
        'anonymize_ip': true,
        'allow_google_signals': false
    });

    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=GTM-MNXKJNG5';
    script.onload = () => console.log('Google Analytics caricato');
    document.head.appendChild(script);

    // Carica Microsoft Clarity (heatmaps + session recording)
    loadMicrosoftClarity();

    // Carica Hotjar (heatmaps + session recording)
    loadHotjar();
}

// ===== MICROSOFT CLARITY =====
// Instrazioni:
// 1. Vai su https://clarity.microsoft.com
// 2. Accedi con account Microsoft
// 3. Crea un nuovo progetto e copia il Project ID
// 4. Sostituisci "YOUR_CLARITY_PROJECT_ID" sotto con il tuo ID
function loadMicrosoftClarity() {
    const clarity_project_id = 'viy89qi2lp'; // Sostituisci con il tuo ID

    if (clarity_project_id === 'YOUR_CLARITY_PROJECT_ID') {
        console.warn('Microsoft Clarity: Project ID non configurato. Visita https://clarity.microsoft.com');
        return;
    }

    (function (c, l, a, r, i, t, y) {
        c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments) };
        t = l.createElement(r);
        t.async = 1;
        t.src = "https://web.clarity.ms/tag/" + i;
        y = l.getElementsByTagName(r)[0];
        y.parentNode.insertBefore(t, y);
    })(window, document, "clarity", "script", clarity_project_id);

    console.log('Microsoft Clarity caricato');
}

// ===== HOTJAR =====
// Instrazioni:
// 1. Vai su https://www.hotjar.com
// 2. Registrati o accedi
// 3. Crea un nuovo sito e copia l'HOTJAR ID
// 4. This is the official Hotjar tracking script
function loadHotjar() {
    const hotjarScript = document.createElement('script');
    hotjarScript.src = 'https://t.contentsquare.net/uxa/d64823176e617.js';
    hotjarScript.async = true;
    document.head.appendChild(hotjarScript);
    console.log('Hotjar caricato');
}

// ===== ADVANCED USER BEHAVIOR TRACKING =====

// Tracker globale
const behaviorTracker = {
    sessionId: null,
    pageStartTime: 0,
    maxScroll: 0,
    events: []
};

// Inizializza il tracking del comportamento
function initBehaviorTracking() {
    const consent = localStorage.getItem('cookieConsent');
    if (!consent) return;

    const parsedConsent = JSON.parse(consent);
    if (!parsedConsent.analytics && !parsedConsent.marketing) return;

    // Crea sessione
    let sessionId = localStorage.getItem('fallstudio_session_id');
    if (!sessionId) {
        sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('fallstudio_session_id', sessionId);
    }
    behaviorTracker.sessionId = sessionId;
    behaviorTracker.pageStartTime = Date.now();

    // Traccia info sessione
    trackSessionInfo();

    // Traccia pagina
    trackPageView();

    // Traccia scroll
    trackScrolling();

    // Traccia click
    trackClicks();

    // Traccia interazioni prodotti
    trackProductInteractions();

    // Traccia form
    trackFormInteractions();

    // Traccia ricerche
    trackSearches();

    // Invia dati periodicamente
    setInterval(batchSendEvents, 30000);

    // Invia prima di abbandonare
    window.addEventListener('beforeunload', batchSendEvents);
}

/**
 * Invia evento a Google Analytics
 */
function sendGAEvent(eventName, eventData = {}) {
    if (typeof window.gtag === 'function') {
        window.gtag('event', eventName, eventData);
    }
}

/**
 * Traccia info sessione iniziale
 */
function trackSessionInfo() {
    const deviceType = window.innerWidth <= 768 ? 'mobile' : 'desktop';
    const screenSize = window.screen.width + 'x' + window.screen.height;

    const sessionInfo = {
        type: 'session_info',
        deviceType: deviceType,
        screenSize: screenSize,
        language: navigator.language,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        timestamp: new Date().toISOString()
    };

    behaviorTracker.events.push(sessionInfo);

    // Invia a Google Analytics
    sendGAEvent('session_info', {
        device_type: deviceType,
        screen_size: screenSize,
        language: navigator.language,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
    });
}

/**
 * Traccia vista pagina
 */
function trackPageView() {
    const pageView = {
        type: 'page_view',
        pagePath: window.location.pathname,
        pageTitle: document.title,
        entryTime: new Date().toISOString(),
        timestamp: Date.now()
    };

    behaviorTracker.events.push(pageView);

    // Invia a Google Analytics
    sendGAEvent('page_view', {
        page_path: window.location.pathname,
        page_title: document.title
    });

    // Aggiorna exit time quando si abbandona la pagina
    window.addEventListener('beforeunload', () => {
        const timeSpent = (Date.now() - behaviorTracker.pageStartTime) / 1000;
        const scrollDepth = (behaviorTracker.maxScroll / window.innerHeight) * 100;

        const pageExit = {
            type: 'page_exit',
            pagePath: window.location.pathname,
            timeSpent: Math.round(timeSpent),
            scrollDepth: Math.round(scrollDepth),
            timestamp: new Date().toISOString()
        };

        behaviorTracker.events.push(pageExit);

        // Invia a Google Analytics
        sendGAEvent('page_exit', {
            page_path: window.location.pathname,
            time_spent: Math.round(timeSpent),
            scroll_depth: Math.round(scrollDepth)
        });
    });
}

/**
 * Traccia lo scrolling
 */
function trackScrolling() {
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        if (scrollTop > behaviorTracker.maxScroll) {
            behaviorTracker.maxScroll = scrollTop;
        }
    }, { passive: true });
}

/**
 * Traccia i click
 */
function trackClicks() {
    document.addEventListener('click', (e) => {
        const element = e.target;
        let elementType = 'generic';
        let elementText = element.textContent?.substring(0, 100) || '';
        let elementId = element.id || '';

        if (element.tagName === 'BUTTON') elementType = 'button';
        else if (element.tagName === 'A') elementType = 'link';
        else if (element.tagName === 'INPUT') elementType = 'input';
        else if (element.classList?.contains('quick-view')) elementType = 'quick_view';
        else if (element.classList?.contains('menu-toggle')) elementType = 'menu_toggle';

        const clickEvent = {
            type: 'click',
            elementType: elementType,
            elementText: elementText,
            elementId: elementId,
            pagePath: window.location.pathname,
            x: e.clientX,
            y: e.clientY,
            timestamp: new Date().toISOString()
        };

        behaviorTracker.events.push(clickEvent);

        // Invia a Google Analytics
        sendGAEvent('element_click', {
            element_type: elementType,
            element_text: elementText,
            element_id: elementId
        });
    }, { passive: true });
}

/**
 * Traccia interazioni con prodotti
 */
function trackProductInteractions() {
    // Traccia hover su prodotti
    document.addEventListener('mouseenter', (e) => {
        const productCard = e.target.closest('.product-card');
        if (productCard) {
            const productId = productCard.dataset.productId || '';
            const productName = productCard.querySelector('.product-info h3')?.textContent || '';

            const hoverEvent = {
                type: 'product_interaction',
                interactionType: 'hover',
                productId: productId,
                productName: productName,
                pagePath: window.location.pathname,
                timestamp: new Date().toISOString()
            };

            behaviorTracker.events.push(hoverEvent);

            // Invia a Google Analytics
            sendGAEvent('product_hover', {
                product_id: productId,
                product_name: productName
            });
        }
    }, { passive: true, capture: true });

    // Traccia click su dettagli prodotto
    document.addEventListener('click', (e) => {
        const quickViewBtn = e.target.closest('.quick-view');
        if (quickViewBtn) {
            const productCard = quickViewBtn.closest('.product-card');
            const productId = productCard?.dataset.productId || '';
            const productName = productCard?.querySelector('.product-info h3')?.textContent || '';

            const viewEvent = {
                type: 'product_interaction',
                interactionType: 'view_details',
                productId: productId,
                productName: productName,
                pagePath: window.location.pathname,
                timestamp: new Date().toISOString()
            };

            behaviorTracker.events.push(viewEvent);

            // Invia a Google Analytics
            sendGAEvent('view_item', {
                product_id: productId,
                product_name: productName
            });
        }
    }, { passive: true, capture: true });

    // Traccia aggiunte al carrello
    document.addEventListener('click', (e) => {
        const addCartBtn = e.target.closest('.btn-add-to-cart');
        if (addCartBtn) {
            const productCard = addCartBtn.closest('.product-card');
            const productId = productCard?.dataset.productId || '';
            const productName = productCard?.querySelector('.product-info h3')?.textContent || '';

            const cartEvent = {
                type: 'product_interaction',
                interactionType: 'add_to_cart',
                productId: productId,
                productName: productName,
                pagePath: window.location.pathname,
                timestamp: new Date().toISOString()
            };

            behaviorTracker.events.push(cartEvent);

            // Invia a Google Analytics (e-commerce event standard)
            sendGAEvent('add_to_cart', {
                product_id: productId,
                product_name: productName
            });
        }
    }, { passive: true, capture: true });
}

/**
 * Traccia interazioni form
 */
function trackFormInteractions() {
    // Newsletter form
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        const emailInput = newsletterForm.querySelector('input[type="email"]');
        const submitBtn = newsletterForm.querySelector('button');

        if (emailInput) {
            emailInput.addEventListener('focus', () => {
                const event = {
                    type: 'form_interaction',
                    formType: 'newsletter',
                    formField: 'email',
                    status: 'focus',
                    timestamp: new Date().toISOString()
                };
                behaviorTracker.events.push(event);
            });

            emailInput.addEventListener('blur', () => {
                const event = {
                    type: 'form_interaction',
                    formType: 'newsletter',
                    formField: 'email',
                    status: 'blur',
                    timestamp: new Date().toISOString()
                };
                behaviorTracker.events.push(event);
            });
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', () => {
                const event = {
                    type: 'form_interaction',
                    formType: 'newsletter',
                    formField: 'submit',
                    status: 'attempted',
                    timestamp: new Date().toISOString()
                };
                behaviorTracker.events.push(event);
            });
        }
    }

    // Contact form
    const contactForm = document.querySelector('form[name="contact"]');
    if (contactForm) {
        const inputs = contactForm.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                const event = {
                    type: 'form_interaction',
                    formType: 'contact',
                    formField: input.name || input.id,
                    status: 'focus',
                    timestamp: new Date().toISOString()
                };
                behaviorTracker.events.push(event);
            });

            input.addEventListener('change', () => {
                const event = {
                    type: 'form_interaction',
                    formType: 'contact',
                    formField: input.name || input.id,
                    status: 'filled',
                    timestamp: new Date().toISOString()
                };
                behaviorTracker.events.push(event);
            });
        });

        contactForm.addEventListener('submit', () => {
            const event = {
                type: 'form_interaction',
                formType: 'contact',
                formField: 'submit',
                status: 'submitted',
                timestamp: new Date().toISOString()
            };
            behaviorTracker.events.push(event);
        });
    }
}

/**
 * Traccia ricerche di prodotto
 */
function trackSearches() {
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = searchInput.value;
                if (query.length > 0) {
                    const searchEvent = {
                        type: 'search',
                        query: query,
                        pagePath: window.location.pathname,
                        timestamp: new Date().toISOString()
                    };
                    behaviorTracker.events.push(searchEvent);

                    // Invia a Google Analytics
                    sendGAEvent('search', {
                        search_term: query
                    });
                }
            }, 500); // Debounce di 500ms
        });
    }
}

/**
 * Invia i dati in batch al server
 */
function batchSendEvents() {
    if (!behaviorTracker.sessionId || behaviorTracker.events.length === 0) {
        return;
    }

    const eventsToSend = [...behaviorTracker.events];

    fetch('api/track-user-behavior.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            sessionId: behaviorTracker.sessionId,
            events: eventsToSend,
            timestamp: new Date().toISOString()
        })
    }).then(response => {
        if (response.ok) {
            // Svuota gli eventi dopo invio riuscito
            behaviorTracker.events = [];
        }
    }).catch(err => {
        console.error('Errore invio tracking:', err);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSite);
} else {
    initSite();
}