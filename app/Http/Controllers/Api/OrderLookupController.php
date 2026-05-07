<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderLookupController extends Controller
{
    public function show(string $code): JsonResponse
    {
        $order = Order::with(['items', 'payment'])->where('code', $code)->firstOrFail();

        return response()->json(['data' => $order]);
    }
}
