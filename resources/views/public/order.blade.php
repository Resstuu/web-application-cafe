@extends('layouts.app', ['title' => 'Pesan Menu'])

@push('head')
@if($snapClientKey)
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $snapClientKey }}"></script>
@endif
@endpush

@section('content')
<div class="grid two" style="grid-template-columns:1.6fr .9fr;">
    <section>
        <div class="row" style="margin-bottom:14px;">
            <h1 style="margin:0;">Pesan Menu</h1>
            <span class="spacer"></span>
            @if($isDemoPayment)
                <span class="badge">Mode demo tanpa key Midtrans</span>
            @endif
        </div>
        <form class="row" method="get" action="{{ route('public.order') }}" style="margin-bottom:16px;">
            <input name="search" placeholder="Cari makanan atau minuman" value="{{ $search }}" style="min-width:240px;flex:1;">
            <select name="category" style="max-width:180px;">
                <option value="">Semua kategori</option>
                <option value="makanan" @selected($category === 'makanan')>Makanan</option>
                <option value="minuman" @selected($category === 'minuman')>Minuman</option>
            </select>
            <button class="primary">Cari</button>
        </form>

        <div class="grid three">
            @forelse($menus as $menu)
                <article class="card">
                    <div class="row">
                        <h3 style="margin:0;">{{ $menu->name }}</h3>
                        <span class="spacer"></span>
                        <span class="badge">{{ $menu->category }}</span>
                    </div>
                    <p style="margin:0;">{{ $menu->description ?: 'Menu cafe siap dipesan.' }}</p>
                    <div class="row">
                        <span class="price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                        <span class="spacer"></span>
                        <input class="qty" type="number" min="0" value="0" data-menu-id="{{ $menu->id }}" data-name="{{ $menu->name }}" data-price="{{ $menu->price }}">
                    </div>
                </article>
            @empty
                <div class="panel">Menu belum tersedia.</div>
            @endforelse
        </div>
    </section>

    <aside class="panel summary">
        <h2>Checkout</h2>
        <label>Nama pelanggan</label>
        <input id="customer_name" placeholder="Contoh: Restu">
        <label>Nomor meja</label>
        <input id="table_number" placeholder="Contoh: 07">
        <div id="cart" style="margin:16px 0;" class="muted">Belum ada menu dipilih.</div>
        <div class="row">
            <strong>Total</strong>
            <span class="spacer"></span>
            <strong id="total">Rp 0</strong>
        </div>
        <button id="checkout" class="primary" style="width:100%;margin-top:14px;">Bayar dengan Midtrans</button>
        <p id="checkout-message" class="muted"></p>
    </aside>
</div>

<script>
const rupiah = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
const qtyInputs = [...document.querySelectorAll('[data-menu-id]')];
const cartEl = document.querySelector('#cart');
const totalEl = document.querySelector('#total');
const messageEl = document.querySelector('#checkout-message');

function selectedItems() {
    return qtyInputs
        .map(input => ({ menu_id: Number(input.dataset.menuId), name: input.dataset.name, price: Number(input.dataset.price), qty: Number(input.value || 0) }))
        .filter(item => item.qty > 0);
}

function renderCart() {
    const items = selectedItems();
    if (!items.length) {
        cartEl.textContent = 'Belum ada menu dipilih.';
        totalEl.textContent = rupiah(0);
        return;
    }

    cartEl.innerHTML = items.map(item => `<div class="row"><span>${item.name} x ${item.qty}</span><span class="spacer"></span><strong>${rupiah(item.price * item.qty)}</strong></div>`).join('');
    totalEl.textContent = rupiah(items.reduce((sum, item) => sum + item.price * item.qty, 0));
}

qtyInputs.forEach(input => input.addEventListener('input', renderCart));

document.querySelector('#checkout').addEventListener('click', async () => {
    messageEl.textContent = '';
    const payload = {
        customer_name: document.querySelector('#customer_name').value,
        table_number: document.querySelector('#table_number').value,
        items: selectedItems().map(({ menu_id, qty }) => ({ menu_id, qty })),
    };

    if (!payload.customer_name || !payload.table_number || !payload.items.length) {
        messageEl.textContent = 'Isi nama, nomor meja, dan pilih minimal satu menu.';
        return;
    }

    const response = await fetch('{{ route('public.checkout') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
    });

    const data = await response.json();
    if (!response.ok) {
        messageEl.textContent = data.message || 'Checkout gagal.';
        return;
    }

    if (data.demo_payment || typeof window.snap === 'undefined') {
        messageEl.innerHTML = `${data.message} Kode order: <strong>${data.order_code}</strong>. Isi key Midtrans agar Snap terbuka.`;
        return;
    }

    window.snap.pay(data.snap_token, {
        onSuccess: () => window.location.href = data.finish_url,
        onPending: () => window.location.href = data.unfinish_url,
        onError: () => window.location.href = data.error_url,
        onClose: () => { messageEl.textContent = 'Pembayaran belum selesai.'; },
    });
});
</script>
@endsection
