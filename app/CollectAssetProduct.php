<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CollectAssetProduct extends Model
{
    public $incrementing = false;             // karena id pakai string
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'product_code', 'product_name',
        'brand_name', 'searah', 'note'
    ];

    public function files()
    {
        return $this->hasMany(CollectAssetProductFile::class, 'product_id', 'id');
    }
}