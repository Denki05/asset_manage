<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use App\CollectAssetProduct;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $data['products'] = CollectAssetProduct::all();
        return view('home', $data);
    }

    public function sync()
    {
        if (Auth::user()->role !== 'developer') {
            abort(403, 'Hanya developer yang dapat melakukan sinkronisasi.');
        }

        $client = new Client();

        try {
            $response = $client->request('GET', 'https://lssoft88.xyz/api/productSearah', [
                'headers' => [
                    'X-API-KEY' => 'warungkopi123',
                    'Accept'    => 'application/json',
                ]
            ]);

            $body = $response->getBody()->getContents();
            $products = json_decode($body, true);

            foreach ($products as $item) {
                CollectAssetProduct::updateOrCreate(
                    ['id' => $item['product_id']],
                    [
                        'product_code'  => $item['product_code'],
                        'product_name'  => $item['product_name'],
                        'brand_name'    => $item['brand_name'] ?? null,
                        'searah'        => $item['searah_name'] ?? null,
                        'note'          => null
                    ]
                );
            }

            return redirect()->route('products.index')->with('status', 'Sinkronisasi produk berhasil.');

        } catch (\Exception $e) {
            return redirect()->route('products.index')->withErrors(['error' => 'Gagal sinkronisasi produk: ' . $e->getMessage()]);
        }
    }
}