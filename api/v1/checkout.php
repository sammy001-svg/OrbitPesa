<?php
class CheckoutController {
    public function __construct(
        private array $body,
        private array $params
    ) {}

    public function pay(): void {
        $slug    = $this->params['slug'] ?? '';
        $channel = $this->body['channel'] ?? '';
        $amount  = $this->body['amount'] ?? null;

        if (!$slug)    api_error('Invalid payment link', 400);
        if (!$channel) api_error('channel is required', 422);

        $link = PaymentLink::findBySlug($slug);
        if (!$link || $link['status'] !== 'active') api_error('Payment link not found or inactive', 404);

        if ($link['expires_at'] && strtotime($link['expires_at']) < time()) {
            api_error('This payment link has expired', 410);
        }
        if ($link['max_uses'] && $link['uses'] >= $link['max_uses']) {
            api_error('This payment link has reached its maximum number of uses', 410);
        }

        // Resolve amount
        if ($link['is_fixed_amount']) {
            $amount = (float)$link['amount'];
        } else {
            $amount = (float)($amount ?? 0);
            if ($amount < 1) api_error('amount is required and must be at least KES 1', 422);
        }

        // Get merchant's active API key (prefer live, fall back to test)
        $apiKeyRow = DB::fetch(
            "SELECT * FROM api_keys WHERE user_id = ? AND status = 'active' ORDER BY environment='live' DESC LIMIT 1",
            [$link['user_id']]
        );
        if (!$apiKeyRow) api_error('Merchant payment configuration error', 500);

        $merchantData = ['user_id' => $link['user_id'], 'environment' => $apiKeyRow['environment']];

        $desc = $link['title'] . ($link['description'] ? ' — ' . $link['description'] : '');

        if ($channel === 'mpesa') {
            $this->processMpesa($merchantData, $amount, $desc, $link['id'], $slug);
        } elseif ($channel === 'card') {
            $this->processCard($merchantData, $amount, $desc, $link['id']);
        } else {
            api_error('Unsupported channel. Use mpesa or card.', 422);
        }
    }

    public function status(): void {
        $ref = $this->params['ref'] ?? '';
        if (!$ref) api_error('ref is required', 422);

        $txn = Transaction::findByRef($ref);
        if (!$txn) api_error('Transaction not found', 404);

        // Auto-complete simulated M-Pesa in test mode after 9 seconds
        if ($txn['status'] === 'pending' && $txn['channel'] === 'mpesa'
            && MPESA_ENV !== 'production'
            && (time() - strtotime($txn['created_at'])) > 9)
        {
            Transaction::updateStatus($ref, 'completed');
            $txn = Transaction::findByRef($ref);
            Wallet::credit($txn['user_id'], (float)$txn['amount'], 'M-Pesa checkout ' . $ref);
            WebhookDispatcher::dispatch($txn['user_id'], 'payment.completed', WebhookDispatcher::buildPayload($txn), $ref);
            $merchant = DB::fetch("SELECT * FROM users WHERE id = ?", [$txn['user_id']]);
            if ($merchant) Mailer::paymentReceived($merchant, $txn);
            // Payer email receipt if stored in metadata
            $meta = json_decode($txn['metadata'] ?? '{}', true);
            if (!empty($meta['payer_email'])) {
                Mailer::paymentReceipt($meta['payer_email'], $txn, $merchant['business_name'] ?? APP_NAME);
            }
            Notification::create($txn['user_id'], 'payment', 'Payment Received', format_amount((float)$txn['amount']) . ' received via M-Pesa (payment link).', '/dashboard/transactions');
        }

        api_success([
            'reference' => $txn['reference'],
            'status'    => $txn['status'],
            'amount'    => $txn['amount'],
            'channel'   => $txn['channel'],
        ]);
    }

    private function processMpesa(array $merchant, float $amount, string $desc, string $linkId, string $slug): void {
        $phone = trim($this->body['phone'] ?? '');
        if (!$phone) api_error('phone is required for M-Pesa', 422);

        $phone = $this->formatPhone($phone);
        if (!$phone) api_error('Invalid phone number. Use 07XXXXXXXX or 254XXXXXXXXX', 422);

        $txnId = Transaction::create([
            'user_id'     => $merchant['user_id'],
            'amount'      => $amount,
            'currency'    => 'KES',
            'channel'     => 'mpesa',
            'phone'       => $phone,
            'description' => $desc,
            'metadata'    => ['payment_link_id' => $linkId, 'slug' => $slug, 'source' => 'checkout', 'payer_email' => $this->body['email'] ?? null],
        ]);
        $txn = DB::fetch("SELECT * FROM transactions WHERE id = ?", [$txnId]);
        $ref = $txn['reference'];

        if (MPESA_ENV === 'production' && MPESA_CONSUMER_KEY) {
            $result = $this->sendRealStk($phone, (int)$amount, $desc, $ref);
        } else {
            $result = ['success' => true, 'checkout_request_id' => 'SIM_' . strtoupper(bin2hex(random_bytes(8)))];
        }

        if (!$result['success']) {
            Transaction::updateStatus($ref, 'failed');
            api_error($result['message'] ?? 'STK Push failed', 502);
        }

        DB::insert(
            "INSERT INTO mpesa_requests (id,transaction_ref,checkout_request_id,merchant_request_id,phone,amount,status)
             VALUES (UUID(),?,?,?,?,?,'pending')",
            [$ref, $result['checkout_request_id'], 'MER_'.uniqid(), $phone, $amount]
        );

        api_success(['reference' => $ref, 'status' => 'pending', 'amount' => $amount],
            'STK Push sent. Ask customer to check their phone.');
    }

    private function processCard(array $merchant, float $amount, string $desc, string $linkId): void {
        $number  = preg_replace('/\D/', '', $this->body['card_number'] ?? '');
        $expiry  = $this->body['card_expiry'] ?? '';
        $cvv     = $this->body['cvv'] ?? '';
        $holder  = trim($this->body['card_holder'] ?? '');

        if (!$number || strlen($number) < 13) api_error('Valid card number is required', 422);
        if (!$expiry)  api_error('Card expiry is required', 422);
        if (!$cvv)     api_error('CVV is required', 422);
        if (!$holder)  api_error('Cardholder name is required', 422);

        [$em, $ey] = array_pad(explode('/', $expiry), 2, '');
        if (!$em || !$ey || (int)$ey < (int)date('y') || ((int)$ey === (int)date('y') && (int)$em < (int)date('m'))) {
            api_error('Card has expired', 422);
        }

        $last4 = substr($number, -4);

        $txnId = Transaction::create([
            'user_id'     => $merchant['user_id'],
            'amount'      => $amount,
            'currency'    => 'KES',
            'channel'     => 'card',
            'description' => $desc,
            'metadata'    => ['payment_link_id' => $linkId, 'card_last4' => $last4, 'source' => 'checkout', 'payer_email' => $this->body['email'] ?? null],
        ]);
        $txn = DB::fetch("SELECT * FROM transactions WHERE id = ?", [$txnId]);

        DB::query("UPDATE transactions SET card_last4 = ? WHERE id = ?", [$last4, $txnId]);
        Transaction::updateStatus($txn['reference'], 'completed');
        Wallet::credit($merchant['user_id'], $amount, 'Card checkout ' . $txn['reference']);

        $completedTxn = Transaction::findByRef($txn['reference']);
        WebhookDispatcher::dispatch($merchant['user_id'], 'payment.completed', WebhookDispatcher::buildPayload($completedTxn), $txn['reference']);
        $merchantRow = DB::fetch("SELECT * FROM users WHERE id = ?", [$merchant['user_id']]);
        if ($merchantRow) Mailer::paymentReceived($merchantRow, $completedTxn);
        $payerEmail = $this->body['email'] ?? null;
        if ($payerEmail && $merchantRow) Mailer::paymentReceipt($payerEmail, $completedTxn, $merchantRow['business_name']);
        Notification::create($merchant['user_id'], 'payment', 'Payment Received', format_amount($amount) . ' received via Card (payment link).', '/dashboard/transactions');

        api_success([
            'reference' => $txn['reference'],
            'status'    => 'completed',
            'amount'    => $amount,
            'card_last4'=> $last4,
        ], 'Payment successful');
    }

    private function formatPhone(string $phone): ?string {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 10 && str_starts_with($phone, '0'))   return '254' . substr($phone, 1);
        if (strlen($phone) === 12 && str_starts_with($phone, '254')) return $phone;
        return null;
    }

    private function sendRealStk(string $phone, int $amount, string $desc, string $ref): array {
        try {
            $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_USERPWD=>MPESA_CONSUMER_KEY.':'.MPESA_CONSUMER_SECRET]);
            $token = json_decode(curl_exec($ch), true)['access_token'] ?? '';
            curl_close($ch);

            $ts  = date('YmdHis');
            $pwd = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $ts);
            $ch  = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
                CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token", 'Content-Type: application/json'],
                CURLOPT_POSTFIELDS     => json_encode([
                    'BusinessShortCode' => MPESA_SHORTCODE, 'Password' => $pwd, 'Timestamp' => $ts,
                    'TransactionType'   => 'CustomerPayBillOnline', 'Amount' => $amount,
                    'PartyA' => $phone, 'PartyB' => MPESA_SHORTCODE, 'PhoneNumber' => $phone,
                    'CallBackURL' => MPESA_CALLBACK_URL, 'AccountReference' => $ref,
                    'TransactionDesc' => substr($desc, 0, 100),
                ]),
            ]);
            $res = json_decode(curl_exec($ch), true);
            curl_close($ch);
            return ['success' => isset($res['CheckoutRequestID']), 'checkout_request_id' => $res['CheckoutRequestID'] ?? null];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'M-Pesa unavailable'];
        }
    }
}
