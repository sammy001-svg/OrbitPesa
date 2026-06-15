<?php
class TransactionsController {
    public function __construct(
        private ?array $merchant,
        private array  $body,
        private array  $params
    ) {}

    public function index(): void {
        $limit  = min((int)($_GET['limit']  ?? 20), 100);
        $page   = max((int)($_GET['page']   ?? 1), 1);
        $offset = ($page - 1) * $limit;

        $filters = [
            'status'    => $_GET['status']    ?? '',
            'channel'   => $_GET['channel']   ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to']   ?? '',
        ];

        $txns  = Transaction::getForUser($this->merchant['user_id'], $limit, $offset, $filters);
        $total = Transaction::countForUser($this->merchant['user_id'], $filters);

        $data = array_map(fn($t) => $this->formatTxn($t), $txns);

        api_success([
            'data' => $data,
            'meta' => ['total' => $total, 'page' => $page, 'limit' => $limit, 'pages' => (int)ceil($total / $limit)],
        ]);
    }

    public function show(): void {
        $ref = $this->params['ref'] ?? '';
        $txn = Transaction::findByRef($ref);

        if (!$txn || $txn['user_id'] !== $this->merchant['user_id']) {
            api_error('Transaction not found', 404);
        }

        api_success(['transaction' => $this->formatTxn($txn)]);
    }

    private function formatTxn(array $t): array {
        return [
            'reference'    => $t['reference'],
            'amount'       => (float)$t['amount'],
            'fee'          => (float)$t['fee'],
            'net_amount'   => (float)($t['amount'] - $t['fee']),
            'currency'     => $t['currency'],
            'channel'      => $t['channel'],
            'phone'        => $t['phone'] ?? null,
            'card_last4'   => $t['card_last4'] ?? null,
            'description'  => $t['description'],
            'status'       => $t['status'],
            'provider_ref' => $t['provider_ref'] ?? null,
            'created_at'   => $t['created_at'],
            'updated_at'   => $t['updated_at'] ?? null,
        ];
    }
}
