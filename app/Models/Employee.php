<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'nombre',
        'telefono',
        'activo',
    ];

    /**
     * Líneas de servicio atendidas por esta empleada.
     */
    public function serviceSaleDetails()
    {
        return $this->hasMany(ServiceSaleDetail::class);
    }
}
