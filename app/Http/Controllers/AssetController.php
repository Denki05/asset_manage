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
    private function decodeId($encodedId)
    {
        return base64_decode(strtr($encodedId, '-_', '+/'));
    }

    public function upload(Request $request, $encodedId)
    {
        $id = $this->decodeId($encodedId);

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
                    'file_type'  => $type,
                    'label'      => $request->input('label') ?: null,
                ]);
            }
        }

        return back()->with('status', 'Asset berhasil diupload.');
    }

    public function uploadImageOnly(Request $request, $encodedId)
    {
        $id = $this->decodeId($encodedId);

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
                    'file_type'  => 'image',
                    'label'      => $request->input('label') ?: null,
                ]);
            }
        }

        return back()->with('status', 'Gambar berhasil diupload.');
    }

    public function destroy(Request $request, $encodedId)
    {
        $id = $this->decodeId($encodedId);

        if (Auth::user()->role !== 'developer') {
            abort(403);
        }

        $filePath = $request->input('filename');
        $fullPath = public_path($filePath);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        CollectAssetProductFile::where('product_id', $id)
            ->where('file_path', $filePath)
            ->delete();

        return back()->with('status', 'File berhasil dihapus.');
    }
}
