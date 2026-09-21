<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSale extends Model
{
    use HasFactory;

    protected $table = 'service_sales';

    protected $fillable = [
        'user_id',
        'cliente_nombre',
        'metodo_pago',
        'subtotal',
        'descuento',
        'total',
        'estado',
        'notas',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(ServiceSaleDetail::class);
    }
}
