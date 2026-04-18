const dns = require('dns').promises;

/**
 * Valida il formato dell'email usando regex
 * @param {string} email - Email da validare
 * @returns {boolean} - True se il formato è valido
 */
function validateEmailFormat(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Verifica l'esistenza di MX records per il dominio email
 * @param {string} domain - Dominio da verificare
 * @param {number} timeout - Timeout in millisecondi (default 5000)
 * @returns {Promise<boolean>} - True se esistono MX records
 */
async function checkDomainMX(domain, timeout = 5000) {
    try {
        // Crea una promise con timeout
        const mxPromise = dns.resolveMx(domain);
        const timeoutPromise = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('MX lookup timeout')), timeout)
        );

        const mxRecords = await Promise.race([mxPromise, timeoutPromise]);

        // Verifica che esistano MX records
        return mxRecords && mxRecords.length > 0;
    } catch (error) {
        console.log(`MX check failed for domain ${domain}:`, error.message);
        return false;
    }
}

/**
 * Validazione email completa: formato + MX records
 * @param {string} email - Email da validare
 * @param {object} options - Opzioni di validazione
 * @returns {Promise<{valid: boolean, reason?: string}>} - Risultato validazione
 */
async function validateEmail(email, options = {}) {
    const { checkMX = true, timeout = 5000 } = options;

    // 1. Valida formato
    if (!validateEmailFormat(email)) {
        return { valid: false, reason: 'INVALID_FORMAT' };
    }

    // 2. Estrai dominio
    const domain = email.split('@')[1];
    if (!domain) {
        return { valid: false, reason: 'INVALID_FORMAT' };
    }

    // 3. Verifica MX (se richiesto)
    if (checkMX) {
        const hasMX = await checkDomainMX(domain, timeout);
        if (!hasMX) {
            return { valid: false, reason: 'INVALID_DOMAIN' };
        }
    }

    return { valid: true };
}

module.exports = {
    validateEmailFormat,
    checkDomainMX,
    validateEmail
};
