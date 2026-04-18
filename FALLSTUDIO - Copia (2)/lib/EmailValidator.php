<?php
/**
 * Email Validator con MX Record Check
 */

class EmailValidator
{

    /**
     * Valida formato email
     */
    public static function validateFormat($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Controlla esistenza MX records per il dominio
     */
    public static function checkMX($domain)
    {
        // checkdnsrr verifica l'esistenza di MX records
        return checkdnsrr($domain, 'MX');
    }

    /**
     * Validazione completa: formato + MX check
     * 
     * @param string $email Email da validare
     * @param array $options Opzioni: ['checkMX' => bool]
     * @return array ['valid' => bool, 'reason' => string|null]
     */
    public static function validate($email, $options = [])
    {
        $checkMX = isset($options['checkMX']) ? $options['checkMX'] : true;

        // 1. Valida formato
        if (!self::validateFormat($email)) {
            return ['valid' => false, 'reason' => 'INVALID_FORMAT'];
        }

        // 2. Estrai dominio
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return ['valid' => false, 'reason' => 'INVALID_FORMAT'];
        }

        $domain = $parts[1];

        // 3. Verifica MX (se richiesto)
        if ($checkMX) {
            if (!self::checkMX($domain)) {
                return ['valid' => false, 'reason' => 'INVALID_DOMAIN'];
            }
        }

        return ['valid' => true];
    }
}
