# Web Application Cafe PKL

Project ini memakai arsitektur terpisah:

- **Backend**: Laravel 11 + MySQL + Sanctum token auth + Midtrans Snap
- **Frontend**: Next.js
- **Database**: MySQL/MariaDB

## Fitur

- Pemesanan mandiri pelanggan tanpa login.
- Checkout pelanggan via Midtrans Snap.
- Login staf memakai Sanctum Bearer Token.
- Role: `super_admin`, `kasir`, `kitchen`, `barista`.
- Super Admin dapat mengelola menu dan user.
- Kasir dapat membuat pesanan manual, membatalkan pesanan, dan menandai lunas.
- Kitchen melihat item makanan.
- Barista melihat item minuman.
- Order menjadi selesai saat semua bagian produksi selesai.

## Setup Backend Laravel

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Konfigurasi penting di `.env`:

```env
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:3000,http://127.0.0.1:3000

DB_CONNECTION=mysql
DB_DATABASE=cafe_pkl
DB_USERNAME=root
DB_PASSWORD=

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

## Setup Frontend Next.js

```bash
cd frontend
npm install
npm run dev
```

Frontend berjalan di:

```text
http://127.0.0.1:3000
```

Jika memakai Midtrans Sandbox asli, buat `frontend/.env.local`:

```env
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api
NEXT_PUBLIC_MIDTRANS_CLIENT_KEY=isi_client_key_midtrans
```

## Akun Demo

Semua password default:

```text
password
```

Daftar akun:

- Super Admin: `admin@cafe.test`
- Kasir: `kasir@cafe.test`
- Kitchen: `kitchen@cafe.test`
- Barista: `barista@cafe.test`

## API Auth

Login staf:

```http
POST /api/auth/login
```

Response berisi token:

```json
{
  "token": "SANCTUM_TOKEN",
  "user": {
    "role": "kasir"
  }
}
```

Request dashboard staf memakai header:

```http
Authorization: Bearer SANCTUM_TOKEN
Accept: application/json
```

## Test

```bash
php artisan test
cd frontend
npm run build
```
