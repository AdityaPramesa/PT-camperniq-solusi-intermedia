<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Master Barang - SI-MONI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                🏢 SI-MONI <span class="fs-6 fw-normal">| PT Camperniq Solusi Intermedia</span>
            </a>
            <div>
                <a href="{{ url('/') }}" class="btn btn-light btn-sm fw-bold">🏠 Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">📦 Tambah Master Barang Baru</h4>
                    <a href="{{ url('/') }}" class="btn btn-secondary btn-sm fw-bold">← Kembali</a>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="{{ url('/master-barang/store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Barang</label>
                                <input type="text" name="kode_barang" class="form-control" placeholder="Contoh: HYT-FO-048" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Barang / Deskripsi</label>
                                <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: KABEL FIBER OPTIC 48 CORE" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Satuan</label>
                                <select name="satuan" class="form-select" required>
                                    <option value="Meter">Meter</option>
                                    <option value="PCS">PCS</option>
                                    <option value="Unit">Unit</option>
                                    <option value="Roll">Roll</option>
                                    <option value="Box">Box</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Stok Awal Inventaris</label>
                                <input type="number" name="stok" class="form-control" value="0" min="0" required>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ url('/') }}" class="btn btn-light border">Batal</a>
                                <button type="submit" class="btn btn-primary fw-bold">💾 Simpan Barang</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>