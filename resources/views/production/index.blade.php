@extends('layouts.app', ['title' => $title])

@push('head')
<meta http-equiv="refresh" content="12">
@endpush

@section('content')
<div class="row" style="margin-bottom:16px;">
    <h1 style="margin:0;">{{ $title }}</h1>
    <span class="spacer"></span>
    <span class="badge">Auto refresh 12 detik</span>
</div>

<div class="grid two">
    @forelse($orders as $order)
        <article class="card">
            <div class="row">
                <h2 style="margin:0;">{{ $order->customer_name }}</h2>
                <span class="spacer"></span>
                <span class="badge">Meja {{ $order->table_number }}</span>
            </div>
            <p style="margin:0;">{{ $order->code }}</p>
            <h3>
                {{ $order->items->map(fn($item) => $item->menu_name.' x '.$item->qty)->join(', ') }}
            </h3>
            <form method="post" action="{{ route('production.complete', [$order, $category]) }}">
                @csrf
                @method('patch')
                <button class="primary" style="width:100%;">Selesai</button>
            </form>
        </article>
    @empty
        <section class="panel">
            <h2>Tidak ada pesanan aktif.</h2>
            <p>Pesanan baru akan muncul otomatis setelah pembayaran sukses atau kasir membuat order manual.</p>
        </section>
    @endforelse
</div>
@endsection
