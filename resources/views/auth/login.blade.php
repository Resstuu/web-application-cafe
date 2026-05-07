@extends('layouts.app', ['title' => 'Login Staf'])

@section('content')
<div class="panel" style="max-width:460px;margin:60px auto;">
    <h1>Login Staf</h1>
    <p>Masuk sebagai Super Admin, Kasir, Kitchen, atau Barista.</p>
    <form method="post" action="{{ route('login') }}">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        <label>Password</label>
        <input type="password" name="password" required>
        <label class="row" style="font-weight:400;">
            <input type="checkbox" name="remember" value="1" style="width:auto;"> Ingat saya
        </label>
        <button class="primary" style="width:100%;margin-top:12px;">Login</button>
    </form>
</div>
@endsection
