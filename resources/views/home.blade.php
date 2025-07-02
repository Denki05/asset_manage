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
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->product_code }}</td>
                            <td>{{ $p->product_name }}</td>
                            <td>{{ $p->brand_name }}</td>
                            <td>{{ $p->searah }}</td>
                            <td style="min-width: 200px;">
                                @if(in_array(Auth::user()->role, ['developer', 'design']))
                                    <form action="{{ Auth::user()->role === 'developer' ? url('/product/'.$p->id.'/upload') : url('/product/'.$p->id.'/upload-image') }}"
                                          method="POST" enctype="multipart/form-data" class="mb-2">
                                        @csrf
                                        <input type="file" name="{{ Auth::user()->role === 'developer' ? 'files[]' : 'images[]' }}"
                                               multiple class="form-control form-control-sm mb-1" required>
                                        <input type="text" name="label" class="form-control form-control-sm mb-1"
                                               placeholder="Kategori / label file (opsional)">
                                        <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                                    </form>
                                @endif

                                @if ($p->files->count() > 0)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($p->files as $file)
                                            @php
                                                $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png']);
                                
                                                // Ambil productId dan filename dari file_path
                                                $segments = explode('/', $file->file_path); // e.g., ['assets', '3774', 'filename.jpg']
                                                $productId = $segments[1] ?? null;
                                                $filename  = $segments[2] ?? null;
                                
                                                $fullPath = $productId && $filename
                                                    ? route('file.preview', ['productId' => $productId, 'filename' => $filename])
                                                    : '#';
                                            @endphp
                                            <div class="border p-1 text-center rounded bg-light" style="width: 80px;">
                                                @if ($file->label)
                                                    <span class="badge bg-info d-block small mb-1">{{ $file->label }}</span>
                                                @endif
                                
                                                @if ($isImage)
                                                    <img src="{{ $fullPath }}" alt="" class="img-fluid rounded" style="height: 60px; cursor: pointer;"
                                                         data-bs-toggle="modal" data-bs-target="#previewModal{{ $file->id }}">
                                                    <div class="modal fade" id="previewModal{{ $file->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-body text-center">
                                                                    <img src="{{ $fullPath }}" class="img-fluid">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($ext === 'mp4')
                                                    <video src="{{ $fullPath }}" style="height: 60px;" controls muted class="w-100 rounded"></video>
                                                @else
                                                    <small class="text-muted">File?</small>
                                                @endif
                                
                                                @if (Auth::user()->role === 'developer')
                                                    <form action="{{ url('/product/'.$p->id.'/delete') }}" method="POST"
                                                          onsubmit="return confirm('Yakin hapus file?')">
                                                        @csrf
                                                        <input type="hidden" name="filename" value="{{ $file->file_path }}">
                                                        <button type="submit" class="btn btn-sm btn-danger btn-block mt-1">🗑</button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">Belum ada file</span>
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