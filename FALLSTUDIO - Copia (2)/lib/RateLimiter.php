<?php
/**
 * Rate Limiter - File-based tracking
 */

class RateLimiter
{
    private $windowMs;
    private $maxRequests;
    private $storageDir;

    public function __construct($windowMs = 900000, $maxRequests = 5)
    {
        $this->windowMs = $windowMs; // 15 minuti default
        $this->maxRequests = $maxRequests;
        $this->storageDir = sys_get_temp_dir();
    }

    /**
     * Verifica se l'IP ha superato il rate limit
     * @return array ['limited' => bool, 'remaining' => int]
     */
    public function check($identifier)
    {
        $filePath = $this->getFilePath($identifier);
        $now = microtime(true) * 1000; // milliseconds
        $windowStart = $now - $this->windowMs;

        // Leggi richieste precedenti
        $requests = $this->readRequests($filePath);

        // Filtra solo richieste nella finestra temporale
        $validRequests = array_filter($requests, function ($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });

        $count = count($validRequests);

        if ($count >= $this->maxRequests) {
            return ['limited' => true, 'remaining' => 0];
        }

        // Aggiungi nuova richiesta
        $validRequests[] = $now;
        $this->writeRequests($filePath, $validRequests);

        return [
            'limited' => false,
            'remaining' => $this->maxRequests - count($validRequests)
        ];
    }

    private function getFilePath($identifier)
    {
        $hash = md5($identifier);
        return $this->storageDir . '/ratelimit_' . $hash . '.txt';
    }

    private function readRequests($filePath)
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        if (empty($content)) {
            return [];
        }

        return array_map('floatval', explode("\n", trim($content)));
    }

    private function writeRequests($filePath, $requests)
    {
        $content = implode("\n", $requests);
        file_put_contents($filePath, $content);
    }
}
