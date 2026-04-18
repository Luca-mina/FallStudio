require('dotenv').config();
const express = require('express');
const path = require('path');
const sqlite3 = require('sqlite3').verbose();
const axios = require('axios');
const rateLimit = require('express-rate-limit');
const { validateEmail } = require('./lib/emailValidator');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());

// Serve static files (site)
app.use(express.static(path.join(__dirname)));

// Rate limiter per API
const apiLimiter = rateLimit({
  windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS) || 900000, // 15 minuti default
  max: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS) || 5,
  message: { error: 'Troppe richieste, riprova più tardi' },
  standardHeaders: true,
  legacyHeaders: false,
});

// Init DB
const DB_FILE = path.join(__dirname, 'newsletter.db');
const db = new sqlite3.Database(DB_FILE, (err) => {
  if (err) {
    console.error('Unable to open database', err);
    process.exit(1);
  }
});

db.serialize(() => {
  // Tabella subscribers
  db.run(`CREATE TABLE IF NOT EXISTS subscribers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    created_at TEXT NOT NULL
  )`);

  // Tabella contact messages
  db.run(`CREATE TABLE IF NOT EXISTS contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    message TEXT NOT NULL,
    created_at TEXT NOT NULL,
    ip_address TEXT
  )`);
});

/**
 * Verifica token reCAPTCHA con Google API
 */
async function verifyRecaptcha(token) {
  if (!token) {
    return { success: false, error: 'No token provided' };
  }

  try {
    const response = await axios.post(
      'https://www.google.com/recaptcha/api/siteverify',
      null,
      {
        params: {
          secret: process.env.RECAPTCHA_SECRET_KEY,
          response: token
        }
      }
    );

    const data = response.data;

    // Per reCAPTCHA v3, verifica lo score (0.0 = bot, 1.0 = umano)
    if (data.success && data.score >= 0.5) {
      return { success: true, score: data.score };
    }

    return {
      success: false,
      error: 'CAPTCHA failed',
      score: data.score
    };
  } catch (error) {
    console.error('reCAPTCHA verification error:', error.message);
    return { success: false, error: 'Verification failed' };
  }
}

// Newsletter endpoint con validazione MX e CAPTCHA
app.post('/api/newsletter', apiLimiter, async (req, res) => {
  const { email, recaptchaToken } = req.body || {};

  // 1. Validazione base
  if (!email || typeof email !== 'string') {
    return res.status(400).json({ error: 'Email non valida' });
  }

  // 2. Verifica CAPTCHA
  const captchaResult = await verifyRecaptcha(recaptchaToken);
  if (!captchaResult.success) {
    return res.status(400).json({
      error: 'Verifica CAPTCHA fallita',
      reason: 'CAPTCHA_FAILED'
    });
  }

  const normalized = email.trim().toLowerCase();

  // 3. Validazione email con MX check
  const validation = await validateEmail(normalized, {
    checkMX: true,
    timeout: parseInt(process.env.MX_CHECK_TIMEOUT) || 5000
  });

  if (!validation.valid) {
    const errorMessages = {
      'INVALID_FORMAT': 'Formato email non valido',
      'INVALID_DOMAIN': 'Il dominio email non esiste o non accetta email'
    };

    return res.status(400).json({
      error: errorMessages[validation.reason] || 'Email non valida',
      reason: validation.reason
    });
  }

  // 4. Salva nel database
  const now = new Date().toISOString();
  const stmt = db.prepare('INSERT OR IGNORE INTO subscribers (email, created_at) VALUES (?, ?)');

  stmt.run(normalized, now, function (err) {
    if (err) {
      console.error('DB insert error', err);
      return res.status(500).json({ error: 'Errore server' });
    }

    if (this.changes === 0) {
      return res.status(200).json({
        message: 'Email già registrata',
        alreadySubscribed: true
      });
    }

    return res.status(201).json({
      message: 'Email registrata con successo',
      success: true
    });
  });

  stmt.finalize();
});

// Contact form endpoint
app.post('/api/contact', apiLimiter, async (req, res) => {
  const { name, email, message, recaptchaToken } = req.body || {};

  // 1. Validazione campi
  if (!name || !email || !message) {
    return res.status(400).json({ error: 'Tutti i campi sono obbligatori' });
  }

  if (typeof name !== 'string' || typeof email !== 'string' || typeof message !== 'string') {
    return res.status(400).json({ error: 'Dati non validi' });
  }

  // 2. Verifica CAPTCHA
  const captchaResult = await verifyRecaptcha(recaptchaToken);
  if (!captchaResult.success) {
    return res.status(400).json({
      error: 'Verifica CAPTCHA fallita',
      reason: 'CAPTCHA_FAILED'
    });
  }

  const normalizedEmail = email.trim().toLowerCase();

  // 3. Validazione email con MX check
  const validation = await validateEmail(normalizedEmail, {
    checkMX: true,
    timeout: parseInt(process.env.MX_CHECK_TIMEOUT) || 5000
  });

  if (!validation.valid) {
    const errorMessages = {
      'INVALID_FORMAT': 'Formato email non valido',
      'INVALID_DOMAIN': 'Il dominio email non esiste o non accetta email'
    };

    return res.status(400).json({
      error: errorMessages[validation.reason] || 'Email non valida',
      reason: validation.reason
    });
  }

  // 4. Salva nel database
  const now = new Date().toISOString();
  const ipAddress = req.ip || req.connection.remoteAddress || 'unknown';

  const stmt = db.prepare(
    'INSERT INTO contact_messages (name, email, message, created_at, ip_address) VALUES (?, ?, ?, ?, ?)'
  );

  stmt.run(name.trim(), normalizedEmail, message.trim(), now, ipAddress, function (err) {
    if (err) {
      console.error('DB insert error', err);
      return res.status(500).json({ error: 'Errore server' });
    }

    return res.status(201).json({
      message: 'Messaggio inviato con successo',
      success: true
    });
  });

  stmt.finalize();
});

// Admin endpoints (da proteggere in produzione)
app.get('/api/newsletter/list', (req, res) => {
  db.all('SELECT id, email, created_at FROM subscribers ORDER BY created_at DESC', (err, rows) => {
    if (err) return res.status(500).json({ error: 'Errore DB' });
    res.json(rows);
  });
});

app.get('/api/contact/list', (req, res) => {
  db.all('SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC', (err, rows) => {
    if (err) return res.status(500).json({ error: 'Errore DB' });
    res.json(rows);
  });
});

// Endpoint per ottenere la site key (pubblico)
app.get('/api/recaptcha/sitekey', (req, res) => {
  res.json({ siteKey: process.env.RECAPTCHA_SITE_KEY });
});

app.listen(PORT, () => {
  console.log(`Server in ascolto su http://localhost:${PORT}`);
  console.log('reCAPTCHA configurato:', process.env.RECAPTCHA_SITE_KEY ? 'SI' : 'NO');
});
