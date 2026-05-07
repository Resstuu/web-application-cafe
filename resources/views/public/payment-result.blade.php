@extends('layouts.app', ['title' => 'Status Pembayaran'])

@section('content')
<section class="panel">
    <h1>Status Pembayaran</h1>
    <p>{{ $message }}</p>
    @if($order)
        <table>
            <tr><th>Kode</th><td>{{ $order->code }}</td></tr>
            <tr><th>Pelanggan</th><td>{{ $order->customer_name }} - Meja {{ $order->table_number }}</td></tr>
            <tr><th>Status Order</th><td><span class="badge">{{ $order->status }}</span></td></tr>
            <tr><th>Status Bayar</th><td><span class="badge">{{ $order->payment_status }}</span></td></tr>
            <tr><th>Total</th><td>Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
        </table>
    @endif
    <p><a class="btn primary" href="{{ route('public.order') }}">Kembali pesan</a></p>
</section>
@endsection
