<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionController extends Controller
{
    public function index(Request $request, string $category): View
    {
        abort_unless(in_array($category, ['makanan', 'minuman'], true), 404);
        abort_if($category === 'makanan' && ! $request->user()->hasRole('kitchen'), 403);
        abort_if($category === 'minuman' && ! $request->user()->hasRole('barista'), 403);

        $orders = Order::with(['items' => fn ($query) => $query->where('category', $category)])
            ->whereIn('status', ['confirmed', 'partially_done'])
            ->whereHas('items', fn ($query) => $query->where('category', $category)->where('status', 'waiting'))
            ->oldest('confirmed_at')
            ->get();

        return view('production.index', [
            'orders' => $orders,
            'category' => $category,
            'title' => $category === 'makanan' ? 'Kitchen' : 'Barista',
        ]);
    }

    public function complete(Request $request, Order $order, string $category): RedirectResponse
    {
        abort_unless(in_array($category, ['makanan', 'minuman'], true), 404);
        abort_if($category === 'makanan' && ! $request->user()->hasRole('kitchen'), 403);
        abort_if($category === 'minuman' && ! $request->user()->hasRole('barista'), 403);

        $order->items()
            ->where('category', $category)
            ->where('status', 'waiting')
            ->update(['status' => 'done']);

        $order->refreshProgressStatus();

        return back()->with('status', 'Pesanan '.$order->code.' ditandai selesai.');
    }
}
