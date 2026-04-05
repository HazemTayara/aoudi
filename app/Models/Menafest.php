<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menafest extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'from_city_id',
        'to_city_id',
        'manafest_code',
        'driver_name',
        'car',
        'notes'
    ];

    protected $dates = ['deleted_at'];

    public function fromCity()
    {
        return $this->belongsTo(City::class, 'from_city_id');
    }

    public function toCity()
    {
        return $this->belongsTo(City::class, 'to_city_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($menafest) {
            // Soft delete all related orders
            foreach ($menafest->orders as $order) {
                $order->delete();
            }
        });

        static::restoring(function ($menafest) {
            // Restore all related orders
            foreach ($menafest->orders()->onlyTrashed()->get() as $order) {
                $order->restore();
            }
        });
    }

    public function menafestType(): string
    {
        $localCity = City::where('is_local', true)->first();
        $type = 'incoming';
        if ($localCity->name == $this->fromCity->name) {
            $type = 'outgoing';
        }
        return $type;
    }

    public function isLocal(): bool
    {
        $localCity = City::where('is_local', true)->first();
        if ($localCity->name == $this->fromCity->name) {
            return true;
        }
        return false;
    }

}