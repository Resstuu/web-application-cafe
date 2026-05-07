<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMenuController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Menu::latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $menu = Menu::create($this->validated($request));

        return response()->json(['data' => $menu], 201);
    }

    public function update(Request $request, Menu $menu): JsonResponse
    {
        $menu->update($this->validated($request));

        return response()->json(['data' => $menu->fresh()]);
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return response()->json(['message' => 'Menu berhasil dihapus.']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'in:makanan,minuman'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
