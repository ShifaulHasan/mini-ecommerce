<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company'
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}