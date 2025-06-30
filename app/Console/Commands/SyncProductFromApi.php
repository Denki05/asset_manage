<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\CollectAssetProduct;
use GuzzleHttp\Client;

class SyncProductFromApi extends Command
{
    protected $signature = 'sync:product';
    protected $description = 'Sinkronisasi data produk dari API external';

    public function handle()
    {
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

            $this->info('Jumlah data diterima: ' . count($products));

            foreach ($products as $item) {
                CollectAssetProduct::updateOrCreate(
                    ['id' => $item['product_id']], // id yang bisa berisi slash
                    [
                        'product_code'  => $item['product_code'],
                        'product_name'  => $item['product_name'],
                        'brand_name'    => $item['brand_name'] ?? null,
                        'searah'        => $item['searah_name'] ?? null,
                        'note'          => null
                    ]
                );
            }

            $this->info('Sinkronisasi produk selesai.');
        } catch (\Exception $e) {
            $this->error('Gagal request: ' . $e->getMessage());
        }
    }
}