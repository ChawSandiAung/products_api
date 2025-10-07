<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['product_id','carat','metal_type','price','stock','sku'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}