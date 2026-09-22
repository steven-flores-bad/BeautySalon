<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'service_category_id',
        'nombre',
        'precio',
        'descripcion',
    ];

    /**
     * Categoría a la que pertenece este servicio.
     */
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
    public function serviceSaleDetails()
    {
        return $this->hasMany(ServiceSaleDetail::class, 'service_id');
    }
}
