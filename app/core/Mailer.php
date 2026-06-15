<?php
class Mailer {

    // -------------------------------------------------------------------------
    // Core transport
    // -------------------------------------------------------------------------

    public static function send(string $to, string $subject, string $html): bool {
        if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) return false;
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
        $from     = defined('MAIL_FROM')      ? MAIL_FROM      : 'noreply@orbitpesa.com';
        $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'OrbitPesa';
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$from}>\r\n";
        $headers .= "X-Mailer: OrbitPesa/1.0\r\n";
        $headers .= "X-Priority: 3\r\n";
        return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $headers);
    }

    // -------------------------------------------------------------------------
    // Templates
    // -------------------------------------------------------------------------

    public static function welcome(array $user): void {
        if (empty($user['email'])) return;
        $name = htmlspecialchars($user['business_name'] ?? 'there');
        self::send(
            $user['email'],
            'Welcome to OrbitPesa — Your account is ready',
            self::wrapTemplate(
                'Welcome to OrbitPesa!',
                '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                <p style="' . self::p() . '">Your OrbitPesa account has been created successfully. You can now start accepting payments in Kenya via M-Pesa, cards, and more.</p>
                ' . self::ctaButton('Go to Dashboard', APP_URL . '/dashboard') . '
                ' . self::divider() . '
                <p style="font-size:14px;font-weight:700;color:#0D1B3E;margin:0 0 12px">Get started in 3 steps:</p>
                ' . self::stepRow('1', 'Complete KYC Verification', 'Upload your identity documents to unlock live payments.', APP_URL . '/dashboard/kyc') . '
                ' . self::stepRow('2', 'Get Your API Keys', 'Generate test and live API keys to integrate with your app.', APP_URL . '/dashboard/api-keys') . '
                ' . self::stepRow('3', 'Try a Test Payment', 'Use our sandbox to send a test M-Pesa push with zero risk.', APP_URL . '/dashboard/mpesa') . '
                <p style="font-size:13px;color:#94a3b8;margin:24px 0 0">
                    Questions? Reply to this email or contact us at <a href="mailto:support@orbitpesa.com" style="color:#158347">support@orbitpesa.com</a>.
                </p>',
                'Your OrbitPesa account is ready — get started in minutes.'
            )
        );
    }

    public static function paymentReceived(array $merchant, array $txn): void {
        if (empty($merchant['email'])) return;
        $name   = htmlspecialchars($merchant['business_name'] ?? 'there');
        $amount = format_amount((float)$txn['amount']);
        self::send(
            $merchant['email'],
            'Payment Received — ' . $amount,
            self::wrapTemplate(
                'Payment Received',
                '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                <p style="' . self::p() . '">
                    A payment of <strong style="color:#158347;font-size:18px">' . $amount . '</strong>
                    has landed in your OrbitPesa account.
                </p>
                ' . self::txnBox($txn) . '
                ' . self::ctaButton('View Transaction', APP_URL . '/dashboard/transactions') . '
                <p style="font-size:12px;color:#94a3b8;margin:20px 0 0">
                    Funds are available in your wallet immediately.
                </p>',
                'You received ' . $amount . ' — check your dashboard.'
            )
        );
    }

    public static function paymentReceipt(string $payerEmail, array $txn, string $merchantName): void {
        if (!filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) return;
        $amount = format_amount((float)$txn['amount']);
        self::send(
            $payerEmail,
            'Payment Receipt — ' . $txn['reference'],
            self::wrapTemplate(
                'Payment Confirmed',
                '<p style="' . self::p() . '">Thank you for your payment!</p>
                <p style="' . self::p() . '">
                    Your payment of <strong style="color:#158347;font-size:18px">' . $amount . '</strong>
                    to <strong>' . htmlspecialchars($merchantName) . '</strong> has been confirmed.
                </p>
                ' . self::txnBox($txn) . '
                <p style="font-size:13px;color:#64748b;margin:20px 0 0">
                    Please keep this email as your payment receipt. If you have questions, contact
                    <strong>' . htmlspecialchars($merchantName) . '</strong> directly.
                </p>',
                'Your payment of ' . $amount . ' to ' . $merchantName . ' was successful.'
            )
        );
    }

    public static function withdrawalInitiated(array $user, array $wd): void {
        if (empty($user['email'])) return;
        $name   = htmlspecialchars($user['business_name'] ?? 'there');
        $amount = format_amount((float)$wd['amount']);
        $dest   = htmlspecialchars($wd['destination'] ?? '');
        $chan   = ucfirst($wd['channel'] ?? 'mpesa');
        self::send(
            $user['email'],
            'Withdrawal Request Submitted — ' . $amount,
            self::wrapTemplate(
                'Withdrawal Submitted',
                '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                <p style="' . self::p() . '">
                    Your withdrawal request for <strong style="color:#0D1B3E">' . $amount . '</strong>
                    has been submitted and is being reviewed.
                </p>
                ' . self::infoBox(
                    '<strong>Amount:</strong> ' . $amount . '<br>
                     <strong>Destination:</strong> ' . $dest . ' (' . $chan . ')<br>
                     <strong>Reference:</strong> ' . htmlspecialchars($wd['reference'] ?? '—') . '<br>
                     <strong>Status:</strong> Pending review'
                ) . '
                <p style="' . self::p() . '" style="margin-top:16px">
                    Withdrawals are typically processed within 1–3 business hours during business days. You will receive a confirmation email once the funds have been sent.
                </p>
                ' . self::ctaButton('View Wallet', APP_URL . '/dashboard/wallet') . '
                <p style="font-size:12px;color:#94a3b8;margin:20px 0 0">
                    If you did not request this withdrawal, please contact support immediately.
                </p>',
                'Your withdrawal of ' . $amount . ' is being processed.'
            )
        );
    }

    public static function withdrawalProcessed(array $user, array $wd, bool $approved): void {
        if (empty($user['email'])) return;
        $name   = htmlspecialchars($user['business_name'] ?? 'there');
        $amount = format_amount((float)$wd['amount']);
        $dest   = htmlspecialchars($wd['destination'] ?? '');
        $chan   = ucfirst($wd['channel'] ?? 'mpesa');
        if ($approved) {
            self::send(
                $user['email'],
                'Withdrawal Approved — ' . $amount . ' Sent',
                self::wrapTemplate(
                    'Withdrawal Approved',
                    '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                    <p style="' . self::p() . '">
                        Great news! Your withdrawal of <strong style="color:#158347">' . $amount . '</strong>
                        has been approved and sent to <strong>' . $dest . '</strong> via ' . $chan . '.
                    </p>
                    ' . self::infoBox(
                        '<strong>Amount Sent:</strong> ' . $amount . '<br>
                         <strong>Destination:</strong> ' . $dest . ' (' . $chan . ')<br>
                         <strong>Reference:</strong> ' . htmlspecialchars($wd['reference'] ?? '—')
                    ) . '
                    ' . self::ctaButton('View Wallet', APP_URL . '/dashboard/wallet') . '
                    <p style="font-size:12px;color:#94a3b8;margin:20px 0 0">
                        Allow up to 30 minutes for M-Pesa transfers to reflect in your phone.
                    </p>',
                    'Your withdrawal of ' . $amount . ' has been sent.'
                )
            );
        } else {
            self::send(
                $user['email'],
                'Withdrawal Rejected — Funds Returned to Wallet',
                self::wrapTemplate(
                    'Withdrawal Rejected',
                    '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                    <p style="' . self::p() . '">
                        Unfortunately, your withdrawal request of <strong>' . $amount . '</strong>
                        could not be processed. The funds have been returned to your wallet.
                    </p>
                    ' . self::infoBox(
                        '<strong>Amount Returned:</strong> ' . $amount . '<br>
                         <strong>Destination:</strong> ' . $dest . ' (' . $chan . ')<br>
                         <strong>Reference:</strong> ' . htmlspecialchars($wd['reference'] ?? '—'),
                        'warning'
                    ) . '
                    <p style="' . self::p() . '">
                        If you believe this was an error, please contact our support team at
                        <a href="mailto:support@orbitpesa.com" style="color:#158347">support@orbitpesa.com</a>
                        with your withdrawal reference.
                    </p>
                    ' . self::ctaButton('Try Again', APP_URL . '/dashboard/wallet') . '',
                    'Your withdrawal was rejected — funds have been returned to your wallet.'
                )
            );
        }
    }

    public static function kycSubmitted(array $user, string $docType): void {
        if (empty($user['email'])) return;
        $name    = htmlspecialchars($user['business_name'] ?? 'there');
        $docName = ucwords(str_replace('_', ' ', $docType));
        self::send(
            $user['email'],
            'KYC Document Received — Under Review',
            self::wrapTemplate(
                'Document Received',
                '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                <p style="' . self::p() . '">
                    We have received your <strong>' . $docName . '</strong> and it is now under review by our compliance team.
                </p>
                ' . self::infoBox(
                    '<strong>Document:</strong> ' . $docName . '<br>
                     <strong>Status:</strong> Pending review<br>
                     <strong>Estimated time:</strong> 1–2 business days'
                ) . '
                <p style="' . self::p() . '">
                    We will notify you by email once a decision has been made. You can check your current verification status at any time in your dashboard.
                </p>
                ' . self::ctaButton('View KYC Status', APP_URL . '/dashboard/kyc') . '',
                'Your ' . $docName . ' is under review — we\'ll update you within 1-2 business days.'
            )
        );
    }

    public static function kycApproved(array $user): void {
        if (empty($user['email'])) return;
        $name = htmlspecialchars($user['business_name'] ?? 'there');
        self::send(
            $user['email'],
            'KYC Verified — You\'re Ready for Live Payments',
            self::wrapTemplate(
                'KYC Approved!',
                '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                <p style="' . self::p() . '">
                    Congratulations! Your identity verification has been <strong style="color:#158347">approved</strong>.
                    Your account now has full access to live payment processing.
                </p>
                ' . self::infoBox(
                    '<strong>&#10003; M-Pesa STK Push</strong> — Live enabled<br>
                     <strong>&#10003; Card Payments</strong> — Live enabled<br>
                     <strong>&#10003; Withdrawals</strong> — Up to daily limit<br>
                     <strong>&#10003; Payment Links</strong> — Live enabled'
                ) . '
                ' . self::ctaButton('Start Accepting Payments', APP_URL . '/dashboard') . '
                <p style="font-size:13px;color:#64748b;margin:20px 0 0">
                    Switch your account from Test Mode to Live Mode in your dashboard settings to begin processing real payments.
                </p>',
                'Your KYC is approved — you\'re ready to go live!'
            )
        );
    }

    public static function kycRejected(array $user, string $reason): void {
        if (empty($user['email'])) return;
        $name = htmlspecialchars($user['business_name'] ?? 'there');
        self::send(
            $user['email'],
            'KYC Document Rejected — Action Required',
            self::wrapTemplate(
                'Document Rejected',
                '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                <p style="' . self::p() . '">
                    We were unable to approve one or more of your KYC documents. Please review the reason below and re-submit a corrected document.
                </p>
                ' . self::infoBox(
                    '<strong>Reason for rejection:</strong><br><em style="color:#991b1b">' . htmlspecialchars($reason) . '</em>',
                    'warning'
                ) . '
                <p style="' . self::p() . '">Common reasons include:</p>
                <ul style="color:#64748b;font-size:14px;margin:0 0 20px;padding-left:20px;line-height:1.8">
                    <li>Document is blurry or hard to read</li>
                    <li>Document is expired</li>
                    <li>Name on document does not match account name</li>
                    <li>Wrong document type uploaded</li>
                </ul>
                ' . self::ctaButton('Re-upload Document', APP_URL . '/dashboard/kyc') . '
                <p style="font-size:13px;color:#64748b;margin:20px 0 0">
                    If you have questions, contact us at <a href="mailto:support@orbitpesa.com" style="color:#158347">support@orbitpesa.com</a>.
                </p>',
                'Action required — please re-upload your KYC document.'
            )
        );
    }

    public static function mpesaApproved(array $user, array $account): void {
        if (empty($user['email'])) return;
        $name    = htmlspecialchars($user['business_name'] ?? 'there');
        $type    = ucfirst($account['application_type'] ?? 'till');
        $number  = htmlspecialchars($account['account_number'] ?? '');
        $typeStr = $account['application_type'] === 'paybill' ? 'Paybill' : 'Till';
        self::send(
            $user['email'],
            'Your M-Pesa ' . $typeStr . ' Number is Ready — ' . $number,
            self::wrapTemplate(
                'M-Pesa Account Approved!',
                '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                <p style="' . self::p() . '">
                    Great news! Your M-Pesa Business ' . $typeStr . ' application has been <strong style="color:#158347">approved</strong>.
                    You can now start collecting customer payments.
                </p>
                ' . self::infoBox(
                    '<strong>' . $typeStr . ' Number:</strong> <span style="font-size:1.4rem;font-family:monospace;font-weight:900">' . $number . '</span><br>
                     <strong>Business:</strong> ' . htmlspecialchars($account['business_name'] ?? '') . '<br>
                     <strong>Type:</strong> ' . $type . ' (' . ($account['application_type'] === 'paybill' ? 'Pay Bill' : 'Buy Goods') . ')',
                    'success'
                ) . '
                <p style="' . self::p() . '">Share your ' . $typeStr . ' Number with your customers so they can pay you directly through M-Pesa. Funds will be credited to your OrbitPesa wallet instantly.</p>
                ' . self::ctaButton('View My Business Account', APP_URL . '/dashboard/mpesa-account') . '
                <p style="font-size:12px;color:#94a3b8;margin:20px 0 0">
                    Transaction fee: 0.5% per payment (max KES 300).
                </p>',
                'Your M-Pesa ' . $typeStr . ' Number ' . $number . ' is now active!'
            )
        );
    }

    public static function mpesaRejected(array $user, array $account, string $reason): void {
        if (empty($user['email'])) return;
        $name   = htmlspecialchars($user['business_name'] ?? 'there');
        $type   = ucfirst($account['application_type'] ?? 'till');
        self::send(
            $user['email'],
            'M-Pesa Business Account Application Update',
            self::wrapTemplate(
                'Application Not Approved',
                '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                <p style="' . self::p() . '">
                    We have reviewed your M-Pesa Business ' . $type . ' application and unfortunately we are unable to approve it at this time.
                </p>
                ' . self::infoBox(
                    '<strong>Reason:</strong><br><em style="color:#991b1b">' . htmlspecialchars($reason) . '</em>',
                    'warning'
                ) . '
                <p style="' . self::p() . '">
                    You are welcome to re-apply after addressing the issue above. If you believe this decision was made in error, please contact our support team.
                </p>
                ' . self::ctaButton('Re-apply or Contact Support', APP_URL . '/dashboard/mpesa-account') . '',
                'Update on your M-Pesa business account application.'
            )
        );
    }

    public static function weeklySummary(array $user, array $stats): void {
        if (empty($user['email'])) return;
        $name      = htmlspecialchars($user['business_name'] ?? 'there');
        $week      = date('d M', strtotime('-6 days')) . ' – ' . date('d M Y');
        $received  = format_amount((float)($stats['total_received'] ?? 0));
        $txnCount  = number_format((int)($stats['txn_count'] ?? 0));
        $success   = ($stats['success_rate'] ?? 0) . '%';
        $balance   = format_amount((float)($stats['wallet_balance'] ?? 0));
        $topChan   = ucfirst($stats['top_channel'] ?? 'mpesa');
        self::send(
            $user['email'],
            'Your OrbitPesa Weekly Summary — ' . $week,
            self::wrapTemplate(
                'Weekly Summary',
                '<p style="' . self::p() . '">Hi <strong>' . $name . '</strong>,</p>
                <p style="' . self::p() . '">Here\'s your OrbitPesa activity summary for <strong>' . $week . '</strong>.</p>
                <table width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 20px">
                  <tr>
                    ' . self::summaryStatCell($received,  'Total Received',   '#158347') . '
                    ' . self::summaryStatCell($txnCount,  'Transactions',     '#0D1B3E') . '
                    ' . self::summaryStatCell($success,   'Success Rate',     '#158347') . '
                  </tr>
                </table>
                ' . self::infoBox(
                    '<strong>Current Wallet Balance:</strong> ' . $balance . '<br>
                     <strong>Most Used Channel:</strong> ' . $topChan
                ) . '
                ' . self::ctaButton('View Full Report', APP_URL . '/dashboard/analytics') . '
                <p style="font-size:12px;color:#94a3b8;margin:20px 0 0">
                    This summary covers transactions from ' . $week . '.
                    Weekly summaries are sent every Monday.
                </p>',
                'Your OrbitPesa weekly summary for ' . $week . '.'
            )
        );
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private static function p(): string {
        return 'font-size:15px;color:#475569;line-height:1.65;margin:0 0 16px';
    }

    private static function ctaButton(string $text, string $url): string {
        return '<table width="100%" cellspacing="0" cellpadding="0" style="margin:24px 0">
            <tr>
              <td align="center">
                <a href="' . $url . '" style="display:inline-block;background:#158347;color:#ffffff;font-size:15px;font-weight:700;padding:14px 36px;border-radius:8px;text-decoration:none;letter-spacing:.01em">'
            . htmlspecialchars($text) . '</a>
              </td>
            </tr>
        </table>';
    }

    private static function infoBox(string $content, string $type = 'info'): string {
        $colors = [
            'info'    => ['bg' => '#f0f9ff', 'border' => '#bae6fd', 'text' => '#0369a1'],
            'success' => ['bg' => '#ecfdf5', 'border' => '#a7f3d0', 'text' => '#065f46'],
            'warning' => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'text' => '#9a3412'],
        ];
        $c = $colors[$type] ?? $colors['info'];
        return '<div style="background:' . $c['bg'] . ';border:1px solid ' . $c['border'] . ';border-left:4px solid ' . $c['border'] . ';border-radius:6px;padding:14px 16px;margin:0 0 20px;font-size:14px;color:' . $c['text'] . ';line-height:1.7">'
            . $content . '</div>';
    }

    private static function divider(): string {
        return '<div style="border-top:1px solid #f1f5f9;margin:24px 0"></div>';
    }

    private static function stepRow(string $num, string $title, string $desc, string $url): string {
        return '<table width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 12px">
            <tr>
              <td width="36" valign="top">
                <div style="width:28px;height:28px;border-radius:50%;background:#158347;color:#fff;font-size:13px;font-weight:700;text-align:center;line-height:28px">' . $num . '</div>
              </td>
              <td style="padding-left:10px">
                <div style="font-size:14px;font-weight:700;color:#0D1B3E;margin-bottom:2px">
                  <a href="' . $url . '" style="color:#0D1B3E;text-decoration:none">' . htmlspecialchars($title) . '</a>
                </div>
                <div style="font-size:13px;color:#64748b">' . htmlspecialchars($desc) . '</div>
              </td>
            </tr>
        </table>';
    }

    private static function summaryStatCell(string $value, string $label, string $color): string {
        return '<td align="center" style="padding:16px 8px;background:#f8fafc;border-radius:8px;margin:0 4px">
            <div style="font-size:22px;font-weight:900;color:' . $color . ';margin-bottom:4px">' . $value . '</div>
            <div style="font-size:12px;color:#94a3b8;font-weight:600">' . htmlspecialchars($label) . '</div>
        </td>';
    }

    private static function txnBox(array $txn): string {
        $rows = [
            ['Reference',   htmlspecialchars($txn['reference'] ?? '—')],
            ['Amount',      format_amount((float)$txn['amount']) . ' ' . ($txn['currency'] ?? 'KES')],
            ['Channel',     ucfirst($txn['channel'] ?? '—')],
            ['Status',      ucfirst($txn['status'] ?? '—')],
            ['Date',        date('d M Y H:i', strtotime($txn['created_at'] ?? 'now'))],
        ];
        if (!empty($txn['phone'])) $rows[] = ['Phone', htmlspecialchars($txn['phone'])];
        if (!empty($txn['description'])) $rows[] = ['Description', htmlspecialchars($txn['description'])];
        $trs = '';
        $i = 0;
        foreach ($rows as [$label, $val]) {
            $bg = $i % 2 === 0 ? '#f8fafc' : '#ffffff';
            $trs .= '<tr style="background:' . $bg . '">
                <td style="padding:10px 14px;font-size:13px;color:#64748b;border-bottom:1px solid #f1f5f9;white-space:nowrap;width:36%">' . $label . '</td>
                <td style="padding:10px 14px;font-size:13px;font-weight:600;color:#1e293b;border-bottom:1px solid #f1f5f9">' . $val . '</td>
            </tr>';
            $i++;
        }
        return '<table width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;border-collapse:collapse;margin:0 0 20px">' . $trs . '</table>';
    }

    private static function wrapTemplate(string $title, string $body, string $preheader = ''): string {
        $pre = $preheader
            ? '<div style="display:none;max-height:0;overflow:hidden;font-size:1px;color:#f4f7fb">'
              . htmlspecialchars($preheader) . str_repeat(' &zwnj;&nbsp;', 60) . '</div>'
            : '';
        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin:0;padding:0;background:#f4f7fb;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Arial,sans-serif;-webkit-font-smoothing:antialiased">
' . $pre . '
<table width="100%" cellspacing="0" cellpadding="0" style="background:#f4f7fb;padding:32px 16px">
<tr><td align="center">
<table width="100%" cellspacing="0" cellpadding="0" style="max-width:580px">

  <tr>
    <td style="background:#0D1B3E;border-radius:12px 12px 0 0;padding:22px 32px;text-align:left">
      <a href="' . APP_URL . '" style="text-decoration:none">
        <span style="font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-.3px;font-family:Arial,sans-serif">Orbit<span style="color:#158347">Pesa</span></span>
      </a>
    </td>
  </tr>

  <tr><td style="height:4px;background:#158347;font-size:4px;line-height:4px">&nbsp;</td></tr>

  <tr>
    <td style="background:#ffffff;padding:36px 32px 28px">
      <h1 style="margin:0 0 20px;font-size:22px;font-weight:800;color:#0D1B3E;line-height:1.2;font-family:Arial,sans-serif">'
            . htmlspecialchars($title) . '</h1>
      ' . $body . '
    </td>
  </tr>

  <tr>
    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 12px 12px;padding:18px 32px">
      <p style="margin:0 0 6px;font-size:12px;color:#94a3b8;font-family:Arial,sans-serif">
        You\'re receiving this email because you have an OrbitPesa account.
      </p>
      <p style="margin:0;font-size:12px;color:#94a3b8;font-family:Arial,sans-serif">
        &copy; ' . date('Y') . ' OrbitPesa Ltd &nbsp;&bull;&nbsp;
        <a href="' . APP_URL . '" style="color:#158347;text-decoration:none">orbitpesa.com</a>
        &nbsp;&bull;&nbsp;
        <a href="' . APP_URL . '/dashboard/settings" style="color:#158347;text-decoration:none">Email preferences</a>
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>';
    }
}
