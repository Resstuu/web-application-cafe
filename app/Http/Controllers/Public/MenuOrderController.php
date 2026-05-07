<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MenuOrderController extends Controller
{
    public function index(Request $request): View
    {
        $menus = Menu::query()
            ->where('is_active', true)
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->category))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('public.order', [
            'menus' => $menus,
            'search' => $request->search,
            'category' => $request->category,
            'snapClientKey' => config('midtrans.client_key'),
            'isDemoPayment' => blank(config('midtrans.server_key')),
        ]);
    }

    public function checkout(Request $request, MidtransService $midtrans): JsonResponse
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
                'total' => 0,
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
                    'status' => 'waiting',
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

        return response()->json([
            'order_code' => $order->code,
            'snap_token' => $snap['token'] ?? null,
            'redirect_url' => $snap['redirect_url'] ?? null,
            'finish_url' => route('payment.finish', ['order' => $order->code]),
            'unfinish_url' => route('payment.unfinish', ['order' => $order->code]),
            'error_url' => route('payment.error', ['order' => $order->code]),
            'demo_payment' => blank(config('midtrans.server_key')),
            'message' => blank(config('midtrans.server_key'))
                ? 'Mode demo aktif karena MIDTRANS_SERVER_KEY belum diisi.'
                : 'Silakan lanjutkan pembayaran.',
        ]);
    }

    public function finish(string $order): View
    {
        return $this->paymentResult($order, 'Pembayaran berhasil diproses. Pesanan masuk ke dapur/barista setelah notifikasi Midtrans diterima.');
    }

    public function unfinish(string $order): View
    {
        return $this->paymentResult($order, 'Pembayaran belum selesai. Silakan lanjutkan pembayaran dari halaman Midtrans.');
    }

    public function error(string $order): View
    {
        return $this->paymentResult($order, 'Pembayaran gagal atau dibatalkan.');
    }

    private function paymentResult(string $orderCode, string $message): View
    {
        return view('public.payment-result', [
            'order' => Order::where('code', $orderCode)->first(),
            'message' => $message,
        ]);
    }
}
