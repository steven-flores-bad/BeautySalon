<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $table = 'service_categories';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Servicios que pertenecen a esta categoría.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
