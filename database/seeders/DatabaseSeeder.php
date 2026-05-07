<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Super Admin', 'admin@cafe.test', 'super_admin'],
            ['Kasir Cafe', 'kasir@cafe.test', 'kasir'],
            ['Kitchen Cafe', 'kitchen@cafe.test', 'kitchen'],
            ['Barista Cafe', 'barista@cafe.test', 'barista'],
        ];

        foreach ($users as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'is_active' => true,
                ]
            );
        }

        $menus = [
            ['Nasi Goreng', 'makanan', 18000, 'Nasi goreng cafe dengan telur dan acar.'],
            ['Mie Goreng', 'makanan', 17000, 'Mie goreng gurih dengan sayuran.'],
            ['Chicken Katsu', 'makanan', 25000, 'Ayam katsu renyah dengan saus spesial.'],
            ['French Fries', 'makanan', 14000, 'Kentang goreng cocok untuk sharing.'],
            ['Espresso', 'minuman', 15000, 'Kopi pekat satu shot.'],
            ['Thai Tea', 'minuman', 16000, 'Thai tea manis creamy.'],
            ['Ice Latte', 'minuman', 20000, 'Espresso, susu, dan es.'],
            ['Lemon Tea', 'minuman', 12000, 'Teh lemon segar.'],
        ];

        foreach ($menus as [$name, $category, $price, $description]) {
            Menu::updateOrCreate(
                ['name' => $name],
                [
                    'category' => $category,
                    'price' => $price,
                    'description' => $description,
                    'is_active' => true,
                ]
            );
        }
    }
}
