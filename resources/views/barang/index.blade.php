<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-MONI - PT Camperniq Solusi Intermedia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                🏢 SI-MONI <span class="fs-6 fw-normal">| PT Camperniq Solusi Intermedia</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                {{-- PERBAIKAN: session('user_name') & fallback dinamis --}}
                <span class="text-white small me-2">
                    👤 <strong>{{ session('user_name') ?? session('user_nama') ?? 'User PT Camperniq' }}</strong> ({{ session('user_role', 'TEKNISI') }})
                </span>
                
                @if(strtoupper(session('user_role')) === 'ADMIN')
                    <a href="{{ url('/master-barang/create') }}" class="btn btn-success btn-sm fw-bold">+ Master Barang</a>
                    <a href="{{ url('/kelola-user') }}" class="btn btn-warning btn-sm fw-bold text-dark me-1">👥 Kelola User</a>
                @endif
                
                <a href="{{ url('/transaksi/create') }}" class="btn btn-light btn-sm fw-bold">+ Transaksi</a>
                <a href="{{ url('/riwayat') }}" class="btn btn-outline-light btn-sm">📋 Riwayat</a>
                <a href="{{ url('/logout') }}" class="btn btn-danger btn-sm fw-bold ms-2" onclick="return confirm('Apakah Anda yakin ingin keluar?')">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Peringatan Stok Kritis / Minim -->
        @if(isset($barangStokMinim) && count($barangStokMinim) > 0)
        <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
            <h6 class="fw-bold text-dark mb-2">⚠️ Peringatan Stok Kritis/Minim!</h6>
            <p class="mb-1 small">Berikut adalah daftar barang yang perlu segera di-restock:</p>
            <ul class="mb-0 small">
                @foreach($barangStokMinim as $bm)
                    <li><strong>{{ $bm->kode_barang }} - {{ $bm->nama_barang }}</strong> (Sisa Stok: <span class="badge bg-danger">{{ $bm->stok_akhir }}</span>)</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Baris 1: Ringkasan Operasional Stok -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-white-50">Total Jenis Barang</h6>
                        <h2 class="fw-bold mb-0">{{ $totalJenisBarang ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-white-50">Total Barang Masuk</h6>
                        <h2 class="fw-bold mb-0">{{ array_sum($grafikMasuk ?? [0]) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-white-50">Total Barang Keluar</h6>
                        <h2 class="fw-bold mb-0">{{ array_sum($grafikKeluar ?? [0]) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-dark-50">Stok Habis / Kritis</h6>
                        <h2 class="fw-bold mb-0">{{ $totalStokKosong ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Baris 2: Ringkasan Valuation Finansial -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card bg-dark text-white shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Total Nilai Aset Gudang (Rp)</h6>
                            <h2 class="fw-bold text-success mb-0">Rp {{ number_format($totalNilaiAsetGudang ?? 0, 0, ',', '.') }}</h2>
                        </div>
                        <span class="fs-1">💰</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-dark text-white shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Pengeluaran Belanja Bulan Ini (Rp)</h6>
                            <h2 class="fw-bold text-info mb-0">Rp {{ number_format($totalPengeluaranBelanjaBulanIni ?? 0, 0, ',', '.') }}</h2>
                        </div>
                        <span class="fs-1">🛒</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Graphic Analytics Tren Mutasi Barang -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">
                📊 Analytics Tren Mutasi Barang (Tahun {{ date('Y') }})
            </div>
            <div class="card-body">
                <canvas id="grafikMutasi" style="max-height: 280px;"></canvas>
            </div>
        </div>

        <!-- Tabel Monitoring Stok & Financial Valuation -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                <span>📦 Realtime Monitoring Stok & Financial Valuation</span>
                <span class="badge bg-secondary">Total Item: {{ count($barangs ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Harga Satuan Beli</th>
                                <th class="text-center">Sisa Stok</th>
                                <th class="text-end">Total Nilai Aset</th>
                                <th class="text-center">Status</th>
                                @if(strtoupper(session('user_role')) === 'ADMIN')
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($barangs ?? [] as $index => $b)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $b->kode_barang }}</code></td>
                                <td class="fw-bold">{{ $b->nama_barang }}</td>
                                <td>Rp {{ number_format($b->harga_satuan ?? 0, 0, ',', '.') }} / {{ $b->satuan }}</td>
                                <td class="text-center fw-bold fs-6">{{ $b->stok_akhir }}</td>
                                <td class="text-end fw-bold text-success">Rp {{ number_format(($b->stok_akhir) * ($b->harga_satuan ?? 0), 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($b->stok_akhir <= 0)
                                        <span class="badge bg-danger">Habis</span>
                                    @elseif($b->stok_akhir <= 10)
                                        <span class="badge bg-warning text-dark">Stok Minim</span>
                                    @else
                                        <span class="badge bg-success">Aman</span>
                                    @endif
                                </td>
                                @if(strtoupper(session('user_role')) === 'ADMIN')
                                <td class="text-center">
                                    <a href="{{ url('/master-barang/edit/' . $b->id) }}" class="btn btn-sm btn-outline-warning p-1 px-2">✏️</a>
                                    <a href="{{ url('/master-barang/delete/' . $b->id) }}" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="return confirm('Yakin ingin menghapus?')">🗑️</a>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted p-4">Belum ada data master barang.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Script Chart.js untuk Grafik Analytics -->
    <script>
        const ctx = document.getElementById('grafikMutasi').getContext('2d');
        const grafikMutasi = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [
                    {
                        label: 'Barang Masuk',
                        data: @json($grafikMasuk ?? []),
                        backgroundColor: '#198754'
                    },
                    {
                        label: 'Barang Keluar',
                        data: @json($grafikKeluar ?? []),
                        backgroundColor: '#dc3545'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>