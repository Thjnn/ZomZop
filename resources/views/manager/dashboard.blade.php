@extends('layouts.manager')

@section('title', 'Tổng quan')

@section('content')
    <h1 class="text-xl font-bold mb-1">Tổng quan</h1>
    <p class="text-sm text-slate-500">{{ $branch->name }} · {{ now()->format('d/m/Y') }}</p>
@endsection
