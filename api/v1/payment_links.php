<?php
class PaymentLinksController {
    public function __construct(
        private ?array $merchant,
        private array  $body,
        private array  $params
    ) {}

    public function index(): void {
        $links = PaymentLink::getForUser($this->merchant['user_id']);
        api_success(['data' => $links]);
    }

    public function create(): void {
        $title  = trim($this->body['title'] ?? '');
        $amount = $this->body['amount'] ?? null;

        if (!$title) api_error('title is required', 422);

        $isFixed = $amount !== null;
        if ($isFixed && (float)$amount < 1) {
            api_error('amount must be at least 1 KES', 422);
        }

        PaymentLink::create([
            'user_id'         => $this->merchant['user_id'],
            'title'           => $title,
            'description'     => $this->body['description'] ?? '',
            'amount'          => $isFixed ? (float)$amount : null,
            'is_fixed_amount' => $isFixed,
            'max_uses'        => $this->body['max_uses'] ?? null,
            'expires_at'      => $this->body['expires_at'] ?? null,
        ]);

        $links = PaymentLink::getForUser($this->merchant['user_id']);
        $link  = reset($links);

        api_success([
            'link' => [
                'slug'   => $link['slug'],
                'url'    => APP_URL . '/pay/' . $link['slug'],
                'title'  => $link['title'],
                'amount' => $link['amount'],
                'status' => $link['status'],
            ]
        ], 'Payment link created successfully');
    }
}
