<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CollectAssetProduct;
use App\CollectAssetProductFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class AssetController extends Controller
{
    public function upload(Request $request, $id)
    {
        if (Auth::user()->role !== 'developer') {
            abort(403);
        }

        $request->validate([
            'files.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
        ]);

        $destination = public_path("assets/$id");
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = time().'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
                $file->move($destination, $filename);

                $mime = $file->getClientMimeType();
                $type = str_starts_with($mime, 'image/') ? 'image' : 'video';

                CollectAssetProductFile::create([
                    'product_id' => $id,
                    'file_path'  => "assets/$id/$filename",
                    'file_type'  => $type, // enum value: 'image' or 'video'\
                    'label'      => $request->input('label') ?: null,
                ]);
            }
        }

        return back()->with('status', 'Asset berhasil diupload.');
    }

    public function uploadImageOnly(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['developer', 'design'])) {
            abort(403);
        }

        $request->validate([
            'images.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $destination = public_path("assets/$id");
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = time().'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
                $file->move($destination, $filename);

                CollectAssetProductFile::create([
                    'product_id' => $id,
                    'file_path'  => "assets/$id/$filename",
                    'file_type'  => 'image', // Hanya image yang diizinkan
                    'label'      => $request->input('label') ?: null,
                ]);
            }
        }

        return back()->with('status', 'Gambar berhasil diupload.');
    }

    public function destroy(Request $request, $id)
    {
        if (Auth::user()->role !== 'developer') {
            abort(403, 'Akses ditolak.');
        }
    
        $filename = $request->input('filename'); // contoh: "assets/3774/image1.jpg"
        if (!$filename || !str_starts_with($filename, 'assets/')) {
            return back()->withErrors(['error' => 'Path file tidak valid.']);
        }
    
        // Pastikan file benar-benar milik produk ini
        $record = CollectAssetProductFile::where('product_id', $id)
            ->where('file_path', $filename)
            ->first();
    
        if (!$record) {
            return back()->withErrors(['error' => 'Data file tidak ditemukan di database.']);
        }
    
        $filepath = public_path($filename); // e.g., public/assets/3774/image1.jpg
    
        if (File::exists($filepath)) {
            File::delete($filepath);
    
            // Hapus dari database
            $record->delete();
    
            return back()->with('status', 'Asset berhasil dihapus.');
        }
    
        return back()->withErrors(['error' => 'File tidak ditemukan di server.']);
    }
}