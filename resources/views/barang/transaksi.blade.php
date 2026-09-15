<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Transaksi Barang - SI-MONI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                🏢 SI-MONI <span class="fs-6 fw-normal">| PT Camperniq Solusi Intermedia</span>
            </a>
            <a href="{{ url('/') }}" class="btn btn-outline-light btn-sm fw-bold">⬅️ Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold py-3">
                        📑 Catat Transaksi Barang (Masuk / Keluar)
                    </div>
                    <div class="card-body p-4">

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                ❌ {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ url('/transaksi/store') }}" method="POST">
                            @csrf

                            <!-- TANGGAL TRANSAKSI -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Transaksi</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- PILIH BARANG (HITUNG STOK AKHIR DINAMIS) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Barang</label>
                                <select name="barang_id" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Barang --</option>
                                    @foreach($barangs as $b)
                                        @php
                                            // Kalkulasi Stok Akhir Realtime
                                            $stokAwal = $b->stok_awal ?? $b->stok ?? 0;
                                            
                                            $totalMasuk = \Illuminate\Support\Facades\DB::table('transaksi_barang')
                                                ->where('barang_id', $b->id)
                                                ->where(function($q) {
                                                    $q->where('jenis_transaksi', 'MASUK')
                                                      ->orWhere('jenis', 'MASUK')
                                                      ->orWhere('tipe', 'MASUK')
                                                      ->orWhere('status', 'MASUK');
                                                })->sum('jumlah');

                                            $totalKeluar = \Illuminate\Support\Facades\DB::table('transaksi_barang')
                                                ->where('barang_id', $b->id)
                                                ->where(function($q) {
                                                    $q->where('jenis_transaksi', 'KELUAR')
                                                      ->orWhere('jenis', 'KELUAR')
                                                      ->orWhere('tipe', 'KELUAR')
                                                      ->orWhere('status', 'KELUAR');
                                                })->sum('jumlah');

                                            $sisaStokReal = $stokAwal + $totalMasuk - $totalKeluar;
                                        @endphp
                                        <option value="{{ $b->id }}">
                                            {{ $b->kode_barang }} - {{ $b->nama_barang }} (Sisa Stok: {{ $sisaStokReal }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- JENIS TRANSAKSI -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jenis Transaksi</label>
                                <select name="jenis_transaksi" class="form-select" required>
                                    <option value="MASUK">🟢 Barang Masuk (Restock / Penambahan Stok)</option>
                                    <option value="KELUAR">🔴 Barang Keluar (Pengambilan oleh Teknisi)</option>
                                </select>
                            </div>

                            <!-- JUMLAH QTY -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jumlah (Qty)</label>
                                <input type="number" name="jumlah" class="form-control" placeholder="Masukkan jumlah item" min="1" required>
                            </div>

                            <!-- PENERIMA / TEKNISI -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Teknisi / Penerima</label>
                                <input type="text" name="penerima" class="form-control" value="{{ session('user_name') ?? 'Teknisi' }}" placeholder="Nama Teknisi" required>
                            </div>

                            <!-- KETERANGAN -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Keterangan / Catatan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Restock Supplier A / Pengambilan untuk Project B"></textarea>
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