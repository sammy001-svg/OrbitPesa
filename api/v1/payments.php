<?php
class PaymentsController {
    public function __construct(
        private ?array $merchant,
        private array  $body,
        private array  $params
    ) {}

    public function mpesa_stk(): void {
        $phone  = trim($this->body['phone'] ?? '');
        $amount = $this->body['amount'] ?? null;
        $desc   = trim($this->body['description'] ?? '');
        $cbUrl  = trim($this->body['callback_url'] ?? MPESA_CALLBACK_URL);

        if (!$phone)  api_error('phone is required', 422);
        if (!$amount) api_error('amount is required', 422);
        if (!$desc)   api_error('description is required', 422);

        $phone = $this->formatPhone($phone);
        if (!$phone) api_error('Invalid phone number. Use format 07XXXXXXXX or 254XXXXXXXXX', 422);

        $amount = (int)$amount;
        if ($amount < 1) api_error('amount must be at least 1 KES', 422);

        $txnId = Transaction::create([
            'user_id'     => $this->merchant['user_id'],
            'amount'      => $amount,
            'currency'    => 'KES',
            'channel'     => 'mpesa',
            'phone'       => $phone,
            'description' => $desc,
            'metadata'    => ['callback_url' => $cbUrl],
        ]);

        $txn = DB::fetch("SELECT * FROM transactions WHERE id = ?", [$txnId]);
        $ref = $txn['reference'];

        if (MPESA_ENV === 'production' && MPESA_CONSUMER_KEY) {
            $result = $this->sendRealStkPush($phone, $amount, $desc, $ref, $cbUrl);
        } else {
            $result = $this->simulateStkPush($phone, $amount, $ref);
        }

        if ($result['success']) {
            DB::query(
                "INSERT INTO mpesa_requests (id, transaction_ref, checkout_request_id, merchant_request_id, phone, amount, status)
                 VALUES (UUID(), ?, ?, ?, ?, ?, 'pending')",
                [$ref, $result['checkout_request_id'] ?? 'SIM_' . uniqid(), $result['merchant_request_id'] ?? 'MER_' . uniqid(), $phone, $amount]
            );

            api_success([
                'reference'           => $ref,
                'checkout_request_id' => $result['checkout_request_id'] ?? null,
                'status'              => 'pending',
            ], 'STK Push sent. Awaiting customer confirmation.');
        } else {
            Transaction::updateStatus($ref, 'failed');
            api_error($result['message'] ?? 'Failed to initiate STK Push', 502);
        }
    }

    public function mpesa_callback(): void {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data) api_error('Invalid callback payload', 400);

        $stkCallback = $data['Body']['stkCallback'] ?? null;
        if (!$stkCallback) api_error('Invalid M-Pesa callback structure', 400);

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? '';
        $resultCode        = $stkCallback['ResultCode'] ?? -1;
        $resultDesc        = $stkCallback['ResultDesc'] ?? '';

        $mpesaReq = DB::fetch(
            "SELECT * FROM mpesa_requests WHERE checkout_request_id = ?",
            [$checkoutRequestId]
        );

        if (!$mpesaReq) {
            api_success([], 'Acknowledged');
            return;
        }

        if ($resultCode == 0) {
            $items = [];
            foreach ($stkCallback['CallbackMetadata']['Item'] ?? [] as $item) {
                $items[$item['Name']] = $item['Value'] ?? null;
            }
            $mpesaReceipt = $items['MpesaReceiptNumber'] ?? '';
            $phone        = $items['PhoneNumber'] ?? $mpesaReq['phone'];

            DB::query(
                "UPDATE mpesa_requests SET status='completed', result_code=?, result_desc=?, mpesa_receipt=? WHERE checkout_request_id=?",
                [$resultCode, $resultDesc, $mpesaReceipt, $checkoutRequestId]
            );

            Transaction::updateStatus($mpesaReq['transaction_ref'], 'completed', [
                'mpesa_receipt' => $mpesaReceipt,
                'provider'      => 'mpesa',
            ]);

            $txn = Transaction::findByRef($mpesaReq['transaction_ref']);
            if ($txn) {
                Wallet::credit($txn['user_id'], (float)$txn['amount'], 'M-Pesa payment ' . $mpesaReceipt);
                WebhookDispatcher::dispatch($txn['user_id'], 'payment.completed', WebhookDispatcher::buildPayload($txn), $txn['reference']);
                $merchant = DB::fetch("SELECT * FROM users WHERE id = ?", [$txn['user_id']]);
                if ($merchant) Mailer::paymentReceived($merchant, $txn);
                Notification::create($txn['user_id'], 'payment', 'Payment Received', format_amount((float)$txn['amount']) . ' received via M-Pesa.', '/dashboard/transactions');
            }
        } else {
            DB::query(
                "UPDATE mpesa_requests SET status='failed', result_code=?, result_desc=? WHERE checkout_request_id=?",
                [$resultCode, $resultDesc, $checkoutRequestId]
            );
            Transaction::updateStatus($mpesaReq['transaction_ref'], 'failed', ['result_desc' => $resultDesc]);

            $txn = Transaction::findByRef($mpesaReq['transaction_ref']);
            if ($txn) WebhookDispatcher::dispatch($txn['user_id'], 'payment.failed', WebhookDispatcher::buildPayload($txn), $txn['reference']);
        }

        api_success([], 'Callback processed');
    }

    public function card_charge(): void {
        $amount     = $this->body['amount'] ?? null;
        $desc       = trim($this->body['description'] ?? 'Card payment');
        $cardNumber = preg_replace('/\D/', '', $this->body['card_number'] ?? '');
        $expMonth   = $this->body['exp_month'] ?? '';
        $expYear    = $this->body['exp_year'] ?? '';
        $cvv        = $this->body['cvv'] ?? '';
        $holder     = trim($this->body['card_holder'] ?? '');

        if (!$amount)     api_error('amount is required', 422);
        if (!$cardNumber) api_error('card_number is required', 422);
        if (!$expMonth || !$expYear) api_error('exp_month and exp_year are required', 422);
        if (!$cvv)        api_error('cvv is required', 422);

        $amount = (float)$amount;
        if ($amount < 50) api_error('Minimum card charge is KES 50', 422);

        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            api_error('Invalid card number', 422);
        }

        $last4 = substr($cardNumber, -4);

        $txnId = Transaction::create([
            'user_id'     => $this->merchant['user_id'],
            'amount'      => $amount,
            'currency'    => 'KES',
            'channel'     => 'card',
            'description' => $desc,
            'metadata'    => ['card_last4' => $last4, 'card_holder' => $holder],
        ]);

        $txn = DB::fetch("SELECT * FROM transactions WHERE id = ?", [$txnId]);

        // Update card_last4 directly
        DB::query("UPDATE transactions SET card_last4 = ? WHERE id = ?", [$last4, $txnId]);

        Transaction::updateStatus($txn['reference'], 'completed', ['provider' => 'simulated']);
        Wallet::credit($this->merchant['user_id'], $amount, 'Card payment ' . $txn['reference']);
        $completedTxn = DB::fetch("SELECT * FROM transactions WHERE reference = ?", [$txn['reference']]);
        WebhookDispatcher::dispatch($this->merchant['user_id'], 'payment.completed', WebhookDispatcher::buildPayload($completedTxn), $txn['reference']);
        $merchant = DB::fetch("SELECT * FROM users WHERE id = ?", [$this->merchant['user_id']]);
        if ($merchant) Mailer::paymentReceived($merchant, $completedTxn);
        Notification::create($this->merchant['user_id'], 'payment', 'Payment Received', format_amount($amount) . ' received via Card.', '/dashboard/transactions');

        api_success([
            'reference' => $txn['reference'],
            'status'    => 'completed',
            'amount'    => $amount,
            'card_last4'=> $last4,
        ], 'Card charged successfully');
    }

    public function wallet_pay(): void {
        $recipientId = trim($this->body['recipient_id'] ?? '');
        $amount      = $this->body['amount'] ?? null;
        $desc        = trim($this->body['description'] ?? 'Wallet payment');

        if (!$recipientId) api_error('recipient_id is required', 422);
        if (!$amount)      api_error('amount is required', 422);

        $amount = (float)$amount;
        if ($amount < 1) api_error('Minimum wallet payment is KES 1', 422);

        // Verify recipient exists
        $recipient = DB::fetch("SELECT id, business_name FROM users WHERE id = ? AND status = 'active'", [$recipientId]);
        if (!$recipient) api_error('Recipient not found or inactive', 404);

        if ($recipientId === $this->merchant['user_id']) {
            api_error('Cannot send payment to yourself', 422);
        }

        // Debit sender
        $ok = Wallet::debit($this->merchant['user_id'], $amount, "$desc → {$recipient['business_name']}");
        if (!$ok) api_error('Insufficient wallet balance', 422);

        // Create transaction record
        $txnId = Transaction::create([
            'user_id'     => $this->merchant['user_id'],
            'amount'      => $amount,
            'currency'    => 'KES',
            'channel'     => 'wallet',
            'description' => $desc,
            'metadata'    => ['recipient_id' => $recipientId, 'recipient_name' => $recipient['business_name']],
        ]);
        $txn = DB::fetch("SELECT * FROM transactions WHERE id = ?", [$txnId]);
        Transaction::updateStatus($txn['reference'], 'completed');

        // Credit recipient
        Wallet::credit($recipientId, $amount, "Wallet receive from merchant " . $this->merchant['user_id']);

        $walletTxn = DB::fetch("SELECT * FROM transactions WHERE reference = ?", [$txn['reference']]);
        WebhookDispatcher::dispatch($this->merchant['user_id'], 'payment.completed', WebhookDispatcher::buildPayload($walletTxn), $txn['reference']);

        api_success([
            'reference'      => $txn['reference'],
            'status'         => 'completed',
            'amount'         => $amount,
            'recipient_name' => $recipient['business_name'],
        ], 'Wallet payment sent successfully');
    }

    public function status(): void {
        $ref = $this->params['ref'] ?? $_GET['ref'] ?? '';
        if (!$ref) api_error('ref (transaction reference) is required', 422);

        $txn = Transaction::findByRef($ref);
        if (!$txn || $txn['user_id'] !== $this->merchant['user_id']) {
            api_error('Transaction not found', 404);
        }

        // Auto-complete simulated STK push after ~9 seconds in test mode
        if ($txn['status'] === 'pending' && $txn['channel'] === 'mpesa'
            && MPESA_ENV !== 'production'
            && (time() - strtotime($txn['created_at'])) > 9)
        {
            Transaction::updateStatus($ref, 'completed');
            $txn = Transaction::findByRef($ref);
            Wallet::credit($txn['user_id'], (float)$txn['net_amount'] ?: (float)$txn['amount'], 'M-Pesa simulated ' . $ref);
            WebhookDispatcher::dispatch($txn['user_id'], 'payment.completed', WebhookDispatcher::buildPayload($txn), $ref);
            $merchant = DB::fetch("SELECT * FROM users WHERE id = ?", [$txn['user_id']]);
            if ($merchant) Mailer::paymentReceived($merchant, $txn);
            Notification::create($txn['user_id'], 'payment', 'Payment Received', format_amount((float)$txn['amount']) . ' received via M-Pesa (sandbox).', '/dashboard/transactions');
        }

        api_success([
            'reference'  => $txn['reference'],
            'status'     => $txn['status'],
            'amount'     => $txn['amount'],
            'fee'        => $txn['fee'],
            'currency'   => $txn['currency'],
            'channel'    => $txn['channel'],
            'created_at' => $txn['created_at'],
        ]);
    }

    private function formatPhone(string $phone): ?string {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        }
        if (strlen($phone) === 12 && str_starts_with($phone, '254')) {
            return $phone;
        }
        return null;
    }

    private function simulateStkPush(string $phone, int $amount, string $ref): array {
        return [
            'success'              => true,
            'checkout_request_id'  => 'ws_CO_SIM_' . strtoupper(bin2hex(random_bytes(8))),
            'merchant_request_id'  => 'MER_' . strtoupper(bin2hex(random_bytes(6))),
            'simulated'            => true,
        ];
    }

    private function sendRealStkPush(string $phone, int $amount, string $desc, string $ref, string $cbUrl): array {
        try {
            $token     = $this->getMpesaToken();
            $timestamp = date('YmdHis');
            $password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

            $payload = [
                'BusinessShortCode' => MPESA_SHORTCODE,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'TransactionType'   => 'CustomerPayBillOnline',
                'Amount'            => $amount,
                'PartyA'            => $phone,
                'PartyB'            => MPESA_SHORTCODE,
                'PhoneNumber'       => $phone,
                'CallBackURL'       => $cbUrl,
                'AccountReference'  => $ref,
                'TransactionDesc'   => substr($desc, 0, 100),
            ];

            $ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token", 'Content-Type: application/json'],
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_TIMEOUT        => 30,
            ]);
            $res = json_decode(curl_exec($ch), true);
            curl_close($ch);

            return [
                'success'             => isset($res['CheckoutRequestID']),
                'checkout_request_id' => $res['CheckoutRequestID'] ?? null,
                'merchant_request_id' => $res['MerchantRequestID'] ?? null,
                'message'             => $res['CustomerMessage'] ?? 'Failed',
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'M-Pesa service unavailable'];
        }
    }

    private function getMpesaToken(): string {
        $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET,
        ]);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $res['access_token'] ?? '';
    }

}
