<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CollectAssetProduct;

class ProductApiController extends Controller
{
    public function index()
    {
        return response()->json(
            CollectAssetProduct::select('id', 'product_code', 'product_name', 'brand_name', 'searah', 'note')
            ->withCount('files')
            ->get()
        );
    }


    public function assets($id)
    {
        $product = CollectAssetProduct::with(['files' => function ($q) {
            $q->select('id', 'product_id', 'file_path', 'file_type', 'filename');
        }])->findOrFail($id);

        return response()->json($product);
    }
}
