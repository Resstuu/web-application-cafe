<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicMenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $menus = Menu::query()
            ->where('is_active', true)
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->category))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $menus]);
    }
}
