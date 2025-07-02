@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h5 class="mb-0">📦 Daftar Produk</h5>
        <select id="brandFilter" class="form-select form-select-sm w-auto">
            <option value="">🔍 Semua Brand</option>
            <option value="GCF">GCF</option>
            <option value="Senses">Senses</option>
        </select>
    </div>
    <div class="card-body">

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            if (!function_exists('base64url_encode')) {
                function base64url_encode($data) {
                    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
                }
            }
        @endphp

        <div class="table-responsive">
            <table id="productsTable" class="table table-hover table-bordered align-middle text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Brand</th>
                        <th>Searah</th>
                        <th>Asset</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $p)
                        @php
                            $encodedId = base64url_encode($p->id);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->product_code }}</td>
                            <td>{{ $p->product_name }}</td>
                            <td>{{ $p->brand_name }}</td>
                            <td>{{ $p->searah }}</td>
                            <td style="min-width: 200px;">
                                @if(in_array(Auth::user()->role, ['developer', 'design']))
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $encodedId }}">
                                        📤 Upload
                                    </button>

                                    <div class="modal fade" id="uploadModal{{ $encodedId }}" tabindex="-1" aria-labelledby="uploadModalLabel{{ $encodedId }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ Auth::user()->role === 'developer' ? url('/product/'.$encodedId.'/upload') : url('/product/'.$encodedId.'/upload-image') }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Upload File - {{ $p->product_name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="file" name="{{ Auth::user()->role === 'developer' ? 'files[]' : 'images[]' }}" class="form-control mb-3" multiple required>
                                                        <label for="label">Label / Kategori</label>
                                                        <select name="label" class="form-select" required>
                                                            <option value="">- Pilih Label -</option>
                                                            <option value="katalog">Katalog</option>
                                                            <option value="thumbnail">Thumbnail</option>
                                                            <option value="vt">VT</option>
                                                            <option value="searah">Searah</option>
                                                        </select>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Upload</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($p->files->count() > 0)
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @foreach ($p->files as $file)
                                            @php
                                                $filename = basename($file->file_path);
                                                $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png']);
                                                $previewUrl = route('file.preview', ['encodedId' => $encodedId, 'filename' => $filename]);
                                            @endphp
                                            <div class="border p-1 text-center rounded bg-light" style="width: 80px;">
                                                @if ($file->label)
                                                    <span class="badge bg-info d-block small mb-1">{{ $file->label }}</span>
                                                @endif

                                                @if ($isImage)
                                                    <img src="{{ $previewUrl }}" alt="" class="img-fluid rounded" style="height: 60px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#previewModal{{ $file->id }}">
                                                    <div class="modal fade" id="previewModal{{ $file->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-body text-center">
                                                                    <img src="{{ $previewUrl }}" class="img-fluid">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($ext === 'mp4')
                                                    <video src="{{ $previewUrl }}" style="height: 60px;" controls muted class="w-100 rounded"></video>
                                                @else
                                                    <small class="text-muted">File?</small>
                                                @endif

                                                @if (Auth::user()->role === 'developer')
                                                    <form action="{{ url('/product/'.$encodedId.'/delete') }}" method="POST" onsubmit="return confirm('Yakin hapus file?')">
                                                        @csrf
                                                        <input type="hidden" name="filename" value="{{ $file->file_path }}">
                                                        <button type="submit" class="btn btn-sm btn-danger btn-block mt-1">🗑</button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small d-block mt-2">Belum ada file</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {
        const table = $('#productsTable').DataTable({
            pageLength: 5,
            responsive: true,
            language: {
                searchPlaceholder: 'Cari produk...'
            }
        });

        $('#brandFilter').on('change', function () {
            const val = this.value;
            table.column(3).search(val).draw();
        });
    });
</script>
@endpush