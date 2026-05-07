@extends('layouts.app', ['title' => 'Order Baru Kasir'])

@section('content')
<form method="post" action="{{ route('cashier.orders.store') }}">
    @csrf
    <div class="grid two">
        <section class="panel">
            <h1>Order Baru</h1>
            <label>Nama pelanggan</label>
            <input name="customer_name" required>
            <label>Nomor meja</label>
            <input name="table_number" required>
            <label>Status bayar</label>
            <select name="payment_status">
                <option value="belum_bayar">Belum bayar</option>
                <option value="lunas">Lunas</option>
            </select>
            <button class="primary" style="margin-top:14px;">Buat Pesanan</button>
        </section>
        <section>
            <h1>Pilih Menu</h1>
            <div class="grid two">
                @foreach($menus as $menu)
                    <div class="card">
                        <div class="row">
                            <strong>{{ $menu->name }}</strong>
                            <span class="spacer"></span>
                            <span class="badge">{{ $menu->category }}</span>
                        </div>
                        <span class="price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                        <input type="hidden" name="items[{{ $loop->index }}][menu_id]" value="{{ $menu->id }}">
                        <label>Qty</label>
                        <input class="qty" type="number" min="0" max="50" name="items[{{ $loop->index }}][qty]" value="0">
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</form>
@endsection
