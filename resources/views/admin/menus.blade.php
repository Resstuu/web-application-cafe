@extends('layouts.app', ['title' => 'Kelola Menu'])

@section('content')
<div class="grid two" style="grid-template-columns:.8fr 1.2fr;">
    <section class="panel">
        <h1>Tambah Menu</h1>
        <form method="post" action="{{ route('admin.menus.store') }}">
            @csrf
            <label>Nama</label>
            <input name="name" required>
            <label>Kategori</label>
            <select name="category" required>
                <option value="makanan">Makanan</option>
                <option value="minuman">Minuman</option>
            </select>
            <label>Harga</label>
            <input type="number" name="price" min="0" required>
            <label>Deskripsi</label>
            <textarea name="description" rows="3"></textarea>
            <label class="row" style="font-weight:400;">
                <input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Aktif
            </label>
            <button class="primary">Simpan</button>
        </form>
    </section>

    <section>
        <h1>Daftar Menu</h1>
        <table>
            <thead><tr><th>Menu</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($menus as $menu)
                    <tr>
                        <form method="post" action="{{ route('admin.menus.update', $menu) }}">
                            @csrf
                            @method('put')
                            <td>
                                <input name="name" value="{{ $menu->name }}" required>
                                <textarea name="description" rows="2" style="margin-top:6px;">{{ $menu->description }}</textarea>
                            </td>
                            <td>
                                <select name="category">
                                    <option value="makanan" @selected($menu->category === 'makanan')>Makanan</option>
                                    <option value="minuman" @selected($menu->category === 'minuman')>Minuman</option>
                                </select>
                            </td>
                            <td><input type="number" name="price" min="0" value="{{ $menu->price }}"></td>
                            <td>
                                <label class="row" style="font-weight:400;margin:0;">
                                    <input type="checkbox" name="is_active" value="1" @checked($menu->is_active) style="width:auto;"> Aktif
                                </label>
                            </td>
                            <td class="row">
                                <button class="primary">Update</button>
                        </form>
                                <form method="post" action="{{ route('admin.menus.destroy', $menu) }}" onsubmit="return confirm('Hapus menu ini?')">
                                    @csrf
                                    @method('delete')
                                    <button class="danger">Hapus</button>
                                </form>
                            </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $menus->links() }}
    </section>
</div>
@endsection
