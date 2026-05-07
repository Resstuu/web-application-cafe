<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerCheckoutController extends Controller
{
    public function store(Request $request, MidtransService $midtrans): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'table_number' => ['required', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $menus = Menu::query()
            ->where('is_active', true)
            ->whereIn('id', collect($validated['items'])->pluck('menu_id'))
            ->get()
            ->keyBy('id');

        if ($menus->count() !== count($validated['items'])) {
            return response()->json(['message' => 'Ada menu yang tidak tersedia.'], 422);
        }

        $order = DB::transaction(function () use ($validated, $menus) {
            $order = Order::create([
                'code' => 'ORD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'customer_name' => $validated['customer_name'],
                'table_number' => $validated['table_number'],
                'source' => 'customer',
                'status' => 'pending_payment',
                'payment_status' => 'pending',
            ]);

            $total = 0;
            foreach ($validated['items'] as $line) {
                $menu = $menus->get((int) $line['menu_id']);
                $qty = (int) $line['qty'];
                $total += $menu->price * $qty;
                $order->items()->create([
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'category' => $menu->category,
                    'price' => $menu->price,
                    'qty' => $qty,
                ]);
            }

            $order->update(['total' => $total]);
            $order->payment()->create([
                'gateway' => 'midtrans',
                'gateway_order_id' => $order->code,
            ]);

            return $order->load(['items', 'payment']);
        });

        $snap = $midtrans->createSnapTransaction($order);
        $order->payment->update([
            'snap_token' => $snap['token'] ?? null,
            'redirect_url' => $snap['redirect_url'] ?? null,
        ]);

        $frontendUrl = explode(',', (string) env('FRONTEND_URL', 'http://localhost:3000'))[0] ?: 'http://localhost:3000';

        return response()->json([
            'order' => $order->fresh(['items', 'payment']),
            'snap_token' => $snap['token'] ?? null,
            'redirect_url' => $snap['redirect_url'] ?? null,
            'finish_url' => $frontendUrl.'/payment/finish?order='.$order->code,
            'unfinish_url' => $frontendUrl.'/payment/unfinish?order='.$order->code,
            'error_url' => $frontendUrl.'/payment/error?order='.$order->code,
            'demo_payment' => blank(config('midtrans.server_key')),
        ], 201);
    }
}
