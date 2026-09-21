<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSaleDetail extends Model
{
    use HasFactory;

    protected $table = 'service_sale_details';

    protected $fillable = [
        'service_sale_id',
        'service_id',
        'employee_id',
        'precio',
        'descuento',
        'subtotal',
        'comision_porcentaje',
        'comision_monto',
    ];

    public function serviceSale()
    {
        return $this->belongsTo(ServiceSale::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
