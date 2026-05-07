<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransService
{
    public function createSnapTransaction(Order $order): array
    {
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '') {
            $demoToken = 'demo-snap-token-'.$order->code;

            return [
                'token' => $demoToken,
                'redirect_url' => route('payment.finish', ['order' => $order->code]),
            ];
        }

        $baseUrl = config('midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($baseUrl.'/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id' => $order->payment->gateway_order_id,
                    'gross_amount' => $order->total,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name,
                ],
                'item_details' => $order->items->map(fn ($item) => [
                    'id' => (string) ($item->menu_id ?? Str::slug($item->menu_name)),
                    'price' => $item->price,
                    'quantity' => $item->qty,
                    'name' => Str::limit($item->menu_name, 45, ''),
                ])->values()->all(),
                'callbacks' => [
                    'finish' => route('payment.finish', ['order' => $order->code]),
                    'unfinish' => route('payment.unfinish', ['order' => $order->code]),
                    'error' => route('payment.error', ['order' => $order->code]),
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Midtrans gagal membuat Snap token: '.$response->body());
        }

        return $response->json();
    }

    public function isValidSignature(array $payload): bool
    {
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '' || empty($payload['signature_key'])) {
            return $serverKey === '';
        }

        $expected = hash('sha512', ($payload['order_id'] ?? '').($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').$serverKey);

        return hash_equals($expected, (string) $payload['signature_key']);
    }
}
