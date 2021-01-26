<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'header',
        'image',
        'description',
        'end_time'
    ];

    protected $touches = [
        'store'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
