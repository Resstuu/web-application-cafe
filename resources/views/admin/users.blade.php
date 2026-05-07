@extends('layouts.app', ['title' => 'Kelola User'])

@section('content')
<div class="grid two" style="grid-template-columns:.8fr 1.2fr;">
    <section class="panel">
        <h1>Tambah User</h1>
        <form method="post" action="{{ route('admin.users.store') }}">
            @csrf
            <label>Nama</label>
            <input name="name" required>
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Role</label>
            <select name="role" required>
                <option value="kasir">Kasir</option>
                <option value="kitchen">Kitchen</option>
                <option value="barista">Barista</option>
                <option value="super_admin">Super Admin</option>
            </select>
            <label class="row" style="font-weight:400;">
                <input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Aktif
            </label>
            <button class="primary">Simpan</button>
        </form>
    </section>

    <section>
        <h1>Daftar User</h1>
        <table>
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <form method="post" action="{{ route('admin.users.update', $user) }}">
                            @csrf
                            @method('put')
                            <td>
                                <input name="name" value="{{ $user->name }}" required>
                                <input type="email" name="email" value="{{ $user->email }}" required style="margin-top:6px;">
                                <input type="password" name="password" placeholder="Password baru opsional" style="margin-top:6px;">
                            </td>
                            <td>
                                <select name="role">
                                    @foreach(['super_admin' => 'Super Admin', 'kasir' => 'Kasir', 'kitchen' => 'Kitchen', 'barista' => 'Barista'] as $value => $label)
                                        <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <label class="row" style="font-weight:400;margin:0;">
                                    <input type="checkbox" name="is_active" value="1" @checked($user->is_active) style="width:auto;"> Aktif
                                </label>
                            </td>
                            <td class="row">
                                <button class="primary">Update</button>
                        </form>
                                <form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('delete')
                                    <button class="danger">Hapus</button>
                                </form>
                            </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $users->links() }}
    </section>
</div>
@endsection
