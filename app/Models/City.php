<?php

// app/Models/City.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'is_local'];

    protected $dates = ['deleted_at'];

    public function hasOrders()
    {
        return Order::whereHas('menafest', function ($query) {
            $query->where('from_city_id', $this->id)
                ->orWhere('to_city_id', $this->id);
        })->exists();
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Menafest::class, 'from_city_id', 'menafest_id');
    }


}