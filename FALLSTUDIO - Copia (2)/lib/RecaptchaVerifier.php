<?php
/**
 * reCAPTCHA Verifier
 */

class RecaptchaVerifier
{
    private $secretKey;
    private $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Verifica token reCAPTCHA
     * @param string $token Token da verificare
     * @return array ['success' => bool, 'score' => float|null, 'error' => string|null]
     */
    public function verify($token)
    {
        if (empty($token)) {
            return ['success' => false, 'error' => 'No token provided'];
        }

        $data = [
            'secret' => $this->secretKey,
            'response' => $token
        ];

        // Usa cURL per maggiore compatibilità (necessario su molti server AlterVista)
        if (extension_loaded('curl')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->verifyUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            // Su alcuni host potrebbe essere necessario disabilitare SSL verify (non consigliato ma a volte necessario)
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            curl_close($ch);
        }
        else {
            // Fallback su file_get_contents se cURL non è disponibile
            $options = [
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query($data),
                    'timeout' => 10
                ]
            ];
            $context = stream_context_create($options);
            $response = @file_get_contents($this->verifyUrl, false, $context);
        }

        if ($response === false) {
            return ['success' => false, 'error' => 'Verification request failed'];
        }

        $result = json_decode($response, true);

        if (!$result) {
            return ['success' => false, 'error' => 'Invalid response'];
        }

        // Per reCAPTCHA v3, verifica lo score (≥ 0.5 = umano)
        $score = isset($result['score']) ? $result['score'] : null;

        if ($result['success'] && ($score === null || $score >= 0.5)) {
            return ['success' => true, 'score' => $score];
        }

        return [
            'success' => false,
            'error' => 'CAPTCHA failed',
            'score' => $score,
            'error_codes' => isset($result['error-codes']) ? $result['error-codes'] : null,
            'hostname' => isset($result['hostname']) ? $result['hostname'] : null,
            'action' => isset($result['action']) ? $result['action'] : null,
            'response_raw' => $result // Per debug completo
        ];
    }
}
