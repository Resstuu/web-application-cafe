@extends('layouts.app', ['title' => 'Dashboard Kasir'])

@section('content')
<div class="row" style="margin-bottom:16px;">
    <h1 style="margin:0;">Dashboard Kasir</h1>
    <span class="spacer"></span>
    <a class="btn primary" href="{{ route('cashier.orders.create') }}">Order Baru</a>
</div>

<table>
    <thead>
        <tr><th>Kode</th><th>Pelanggan</th><th>Item</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
            <tr>
                <td>
                    <strong>{{ $order->code }}</strong><br>
                    <span class="muted">{{ $order->source }}</span>
                </td>
                <td>{{ $order->customer_name }}<br><span class="muted">Meja {{ $order->table_number }}</span></td>
                <td>
                    @foreach($order->items as $item)
                        <div>{{ $item->menu_name }} x {{ $item->qty }} <span class="badge">{{ $item->status }}</span></div>
                    @endforeach
                </td>
                <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                <td>
                    <span class="badge">{{ $order->status }}</span><br>
                    <span class="badge">{{ $order->payment_status }}</span>
                </td>
                <td class="row">
                    @if($order->payment_status !== 'lunas' && $order->status !== 'cancelled')
                        <form method="post" action="{{ route('cashier.orders.paid', $order) }}">
                            @csrf
                            @method('patch')
                            <button class="primary">Lunas</button>
                        </form>
                    @endif
                    @if(! in_array($order->status, ['done', 'cancelled'], true))
                        <form method="post" action="{{ route('cashier.orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
                            @csrf
                            @method('patch')
                            <button class="danger">Batal</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
{{ $orders->links() }}
@endsection
