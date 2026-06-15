<?php
class WebhookDispatcher {
    const TIMEOUT = 10; // seconds per attempt
    const MAX_ATTEMPTS = 3;

    public static function dispatch(string $userId, string $event, array $data, string $ref = ''): void {
        $webhooks = Webhook::getActiveForUser($userId);
        foreach ($webhooks as $wh) {
            $events = json_decode($wh['events'], true) ?? [];
            if (!in_array($event, $events, true)) continue;
            self::deliver($wh, $event, $data, $ref);
        }
    }

    private static function deliver(array $wh, string $event, array $data, string $ref): void {
        $payload = [
            'event'     => $event,
            'timestamp' => date('c'),
            'data'      => $data,
        ];
        $body      = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $body, $wh['secret']);

        $status    = 'failed';
        $httpCode  = null;
        $response  = null;
        $attempts  = 0;

        for ($i = 1; $i <= self::MAX_ATTEMPTS; $i++) {
            $attempts = $i;
            [$httpCode, $response] = self::httpPost($wh['url'], $body, $signature);
            if ($httpCode >= 200 && $httpCode < 300) {
                $status = 'success';
                break;
            }
            if ($i < self::MAX_ATTEMPTS) usleep(500000 * $i); // 0.5s, 1s backoff
        }

        DB::insert(
            "INSERT INTO webhook_deliveries (id, webhook_id, event, transaction_ref, payload, response_status, response_body, status, attempts)
             VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?)",
            [$wh['id'], $event, $ref ?: null, $body, $httpCode, $response ? substr($response, 0, 1000) : null, $status, $attempts]
        );
    }

    private static function httpPost(string $url, string $body, string $signature): array {
        if (!function_exists('curl_init')) return [null, 'cURL not available'];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'User-Agent: OrbitPesa-Webhook/1.0',
                'X-OrbitPesa-Event: ' . explode('.', $signature)[0] ?? '',
                'X-OrbitPesa-Signature: ' . $signature,
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$httpCode, $response ?: ''];
    }

    public static function buildPayload(array $txn): array {
        return [
            'reference'   => $txn['reference'],
            'amount'      => (float)$txn['amount'],
            'currency'    => $txn['currency'] ?? 'KES',
            'channel'     => $txn['channel'],
            'status'      => $txn['status'],
            'phone'       => $txn['phone'] ?? null,
            'card_last4'  => $txn['card_last4'] ?? null,
            'description' => $txn['description'],
            'created_at'  => $txn['created_at'],
        ];
    }
}
