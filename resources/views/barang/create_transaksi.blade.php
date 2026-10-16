<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Transaksi - PT Camperniq Solusi Intermedia</title>
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
            <div class="d-flex align-items-center gap-2">
                <a href="{{ url('/') }}" class="btn btn-light btn-sm fw-bold text-dark">← Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold py-3">
                        📑 Catat Transaksi Barang
                    </div>
                    <div class="card-body p-3 p-md-4">

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                ❌ {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ url('/transaksi/store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Transaksi</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Barang</label>
                                <select name="barang_id" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Barang --</option>
                                    @foreach($barangs as $b)
                                        <option value="{{ $b->id }}">
                                            {{ $b->kode_barang }} - {{ $b->nama_barang }} (Sisa Stok: {{ $b->stok_akhir ?? $b->stok ?? 0 }} {{ $b->satuan }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Jenis Transaksi</label>
                                <select name="jenis_transaksi" class="form-select" required>
                                    <option value="MASUK">🟢 Barang Masuk (Restock / Belanja)</option>
                                    <option value="KELUAR">🔴 Barang Keluar (Pengambilan Teknisi)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Jumlah (Qty)</label>
                                <input type="number" name="jumlah" class="form-control" placeholder="Masukkan jumlah item" min="1" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Penerima / Teknisi</label>
                                <input type="text" name="penerima" class="form-control" value="{{ session('user_name') }}" placeholder="Nama Karyawan / Teknisi" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Pengambilan untuk project A / Restock supplier"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">💾 Simpan Transaksi</button>
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