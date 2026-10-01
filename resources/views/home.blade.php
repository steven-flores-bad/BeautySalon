@extends('layouts.app')

@section('title', 'BeautyControl - Inicio')
@section('max-width', 'max-w-7xl')
@section('body-class', 'bg-gray-100')

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-800">¡Bienvenido a BeautyControl! ✨</h1>
       <p class="text-gray-600 mt-2">El menú se ha cargado correctamente mediante include y el botón de <strong>Inicio</strong> muestra el fondo blanco.</p>
    </div>
@endsection
