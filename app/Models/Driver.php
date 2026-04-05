<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'notes'];
    protected $dates = ['deleted_at'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Check if driver has any orders
    public function hasOrders()
    {
        return $this->orders()->exists();
    }

    // Get orders count
    public function getOrdersCountAttribute()
    {
        return $this->orders()->count();
    }
}
