<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return match ($request->user()->role) {
            'super_admin' => redirect()->route('admin.menus.index'),
            'kasir' => redirect()->route('cashier.orders.index'),
            'kitchen' => redirect()->route('production.index', 'makanan'),
            'barista' => redirect()->route('production.index', 'minuman'),
            default => abort(403),
        };
    }
}
