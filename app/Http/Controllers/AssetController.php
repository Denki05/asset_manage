<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CollectAssetProduct;
use App\CollectAssetProductFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                    'filename'   => $filename,
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
                    'filename'   => $filename,
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
            abort(403);
        }

        $filename = $request->input('filename');
        $filepath = $filename;

        if (file_exists($filepath)) {
            unlink($filepath);

            // Hapus juga dari database jika ada
            \App\CollectAssetProductFile::where('product_id', $id)
                ->where('file_path', $filename)
                ->delete();

            return back()->with('status', 'Asset berhasil dihapus.');
        }

        return back()->withErrors(['error' => 'File tidak ditemukan.']);
    }

}