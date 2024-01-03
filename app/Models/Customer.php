<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'zone_id',
        'service_id',
        'phone_number',
        'address',
        'status'
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function isPaid($date = null) {

        return $this->payments->where('status', PaymentStatus::PAID)
                ->whereBetween('payment_date', [
                    date('Y-m-01', strtotime($date ?? now())),
                    date('Y-m-t', strtotime($date ?? now()))
                ])->count() > 0;
    }
}
