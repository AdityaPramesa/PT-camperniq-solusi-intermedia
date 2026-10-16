<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Master Barang - PT Camperniq Solusi Intermedia</title>
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

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white fw-bold py-3">
                        📦 Tambah Master Barang Baru
                    </div>
                    <div class="card-body p-3 p-md-4">

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                ❌ {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ url('/master-barang/store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Barang</label>
                                <input type="text" name="kode_barang" class="form-control" placeholder="Contoh: HYT-FO-024" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: KABEL FIBER OPTIC 24 CORE" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Satuan</label>
                                <input type="text" name="satuan" class="form-control" placeholder="Contoh: Meter / PCS / Unit" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Stok Awal</label>
                                <input type="number" name="stok_awal" class="form-control" placeholder="Masukkan jumlah stok awal" min="0" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Harga Satuan Beli (Rp)</label>
                                <input type="number" name="harga_satuan" class="form-control" placeholder="Contoh: 1000" min="0" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg fw-bold">💾 Simpan Master Barang</button>
                                <a href="{{ url('/') }}" class="btn btn-light border fw-bold text-secondary">Batal</a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>