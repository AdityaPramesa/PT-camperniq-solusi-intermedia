<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master Barang - SI-MONI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body { max-width: 100%; overflow-x: hidden; }
    </style>
</head>
<body class="bg-light">

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                🏢 PT Camperniq Solusi Intermedia
            </a>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="{{ url('/') }}" class="btn btn-light btn-sm fw-bold text-dark">← Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container my-4 my-md-5" style="max-width: 600px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark fw-bold py-3">
                ✏️ Edit Master Barang
            </div>
            <div class="card-body p-3 p-md-4">
                <form action="{{ url('/master-barang/update/' . $barang->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Kode Barang</label>
                        <input type="text" name="kode_barang" class="form-control" value="{{ $barang->kode_barang }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" value="{{ $barang->nama_barang }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Satuan</label>
                            <input type="text" name="satuan" class="form-control" value="{{ $barang->satuan }}" placeholder="Unit / Meter / Pcs" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Stok Awal</label>
                            <input type="number" name="stok_awal" class="form-control" value="{{ $barang->stok_awal ?? $barang->stok ?? 0 }}" min="0" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Harga Satuan Beli (Rp)</label>
                        <input type="number" name="harga_satuan" class="form-control" value="{{ $barang->harga_satuan ?? 0 }}" placeholder="Contoh: 15000" required>
                        <span class="text-muted small">Masukkan harga/nilai rupiah per 1 unit barang.</span>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                        <a href="{{ url('/') }}" class="btn btn-secondary fw-bold order-2 order-sm-1">← Batal</a>
                        <button type="submit" class="btn btn-warning fw-bold text-dark order-1 order-sm-2">💾 Perbarui Master Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>