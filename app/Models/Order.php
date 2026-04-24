<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'menafest_id',
        'driver_id',
        'assigned_at',
        'order_number', //
        'content', // 
        'count', //
        'sender', //
        'recipient', // 
        'pay_type', //
        'amount', // 
        'anti_charger', // 
        'transmitted', //
        'miscellaneous', //
        'discount', //
        'is_paid',
        'paid_at',
        'is_exist',
        'notes' //
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_exist' => 'boolean',
        'paid_at' => 'datetime',
        'assigned_at' => 'datetime',
        'amount' => 'decimal:2',
        'anti_charger' => 'decimal:2',
        'transmitted' => 'decimal:2',
        'miscellaneous' => 'decimal:2',
        'discount' => 'decimal:2',
    ];
    protected $dates = ['deleted_at'];

    public function menafest()
    {
        return $this->belongsTo(Menafest::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function scopeIncoming($query)
    {
        $localCity = City::where('is_local', true)->first();

        return $query->whereHas('menafest', function ($q) use ($localCity) {
            $q->where('from_city_id', '!=', $localCity->id);
        });
    }

    public function scopeOutgoing($query)
    {
        $localCity = City::where('is_local', true)->first();

        return $query->whereHas('menafest', function ($q) use ($localCity) {
            $q->where('from_city_id', $localCity->id);
        });
    }
}
