@extends('layouts.app')

@section('title', 'Inicio')
@section('max-width', 'max-w-7xl')
@section('body-class', 'bg-gray-100')

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-800">¡Bienvenido a {{ config('services.negocio.nombre') }}! ✨</h1>
       <p class="text-gray-600 mt-2">FELIZ INICIO DE LA JORNADA</p>
    </div>
@endsection
