<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    public function index(Request $request, string $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        $orders = Order::with(['items' => fn ($query) => $query->where('category', $category)])
            ->whereIn('status', ['confirmed', 'partially_done'])
            ->whereHas('items', fn ($query) => $query->where('category', $category)->where('status', 'waiting'))
            ->oldest('confirmed_at')
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function complete(Request $request, Order $order, string $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        $order->items()
            ->where('category', $category)
            ->where('status', 'waiting')
            ->update(['status' => 'done']);

        $order->refreshProgressStatus();

        return response()->json(['data' => $order->fresh('items')]);
    }

    private function authorizeCategory(Request $request, string $category): void
    {
        abort_unless(in_array($category, ['makanan', 'minuman'], true), 404);
        abort_if($category === 'makanan' && ! $request->user()->hasRole('kitchen'), 403);
        abort_if($category === 'minuman' && ! $request->user()->hasRole('barista'), 403);
    }
}
