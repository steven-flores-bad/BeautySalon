<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VentasController extends Controller
{
    public function index()
    {
        // Aquí puedes pasar datos desde un "Modelo" si lo necesitas más adelante
        return view('home'); 
    }
}