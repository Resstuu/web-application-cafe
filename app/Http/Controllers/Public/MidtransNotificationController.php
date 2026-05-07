<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans): JsonResponse
    {
        $payload = $request->all();

        if (! $midtrans->isValidSignature($payload)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $payment = Payment::with('order.items')
            ->where('gateway_order_id', $payload['order_id'] ?? null)
            ->firstOrFail();

        DB::transaction(function () use ($payment, $payload) {
            $transactionStatus = $payload['transaction_status'] ?? null;
            $fraudStatus = $payload['fraud_status'] ?? null;
            $isSuccess = in_array($transactionStatus, ['capture', 'settlement'], true)
                && ($transactionStatus !== 'capture' || $fraudStatus === 'accept');
            $isFailed = in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true);

            $payment->update([
                'transaction_status' => $transactionStatus,
                'payment_type' => $payload['payment_type'] ?? null,
                'fraud_status' => $fraudStatus,
                'paid_at' => $isSuccess ? now() : $payment->paid_at,
                'raw_notification' => $payload,
            ]);

            if ($isSuccess) {
                $payment->order->update([
                    'status' => 'confirmed',
                    'payment_status' => 'lunas',
                    'confirmed_at' => now(),
                ]);
            }

            if ($isFailed) {
                $payment->order->update([
                    'status' => 'payment_failed',
                    'payment_status' => 'gagal',
                ]);
            }
        });

        return response()->json(['message' => 'Notification processed.']);
    }
}
