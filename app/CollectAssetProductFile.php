<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CollectAssetProductFile extends Model
{
    protected $fillable = [
        'product_id', 'file_path', 'file_type', 'label'
    ];

    public function product()
    {
        return $this->belongsTo(CollectAssetProduct::class, 'product_id');
    }
}