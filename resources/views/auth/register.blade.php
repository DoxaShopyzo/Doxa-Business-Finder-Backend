@extends('layouts.guest')
@section('title', 'Login')
@section('content')
<div class="w-full max-w-md p-8 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-bold text-center mb-6">Login to Doxa</h2>
    <form>
        <div class="mb-4"><label class="block mb-2">Email</label><input type="email" class="w-full border p-2 rounded"></div>
        <div class="mb-4"><label class="block mb-2">Password</label><input type="password" class="w-full border p-2 rounded"></div>
        <button class="w-full bg-blue-600 text-white p-2 rounded">Login</button>
    </form>
</div>
@endsection