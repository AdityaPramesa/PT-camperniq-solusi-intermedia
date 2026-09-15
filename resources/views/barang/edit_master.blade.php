<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Master Barang - SI-MONI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container my-5" style="max-width: 600px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark fw-bold">
                ✏️ Edit Master Barang
            </div>
            <div class="card-body p-4">
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
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Satuan</label>
                            <input type="text" name="satuan" class="form-control" value="{{ $barang->satuan }}" placeholder="Unit / Meter / Pcs" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Stok Awal</label>
                            <input type="number" name="stok_awal" class="form-control" value="{{ $barang->stok_awal ?? $barang->stok ?? 0 }}" min="0" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Harga Satuan Beli (Rp)</label>
                        <input type="number" name="harga_satuan" class="form-control" value="{{ $barang->harga_satuan ?? 0 }}" placeholder="Contoh: 15000" required>
                        <span class="text-muted small">Masukkan harga/nilai rupiah per 1 unit barang.</span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ url('/') }}" class="btn btn-secondary fw-bold">← Batal</a>
                        <button type="submit" class="btn btn-warning fw-bold text-dark">💾 Perbarui Master Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>