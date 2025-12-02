<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['name', 'location'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}