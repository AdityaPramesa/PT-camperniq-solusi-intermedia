<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT Camperniq Solusi Intermedia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @if(strtoupper(session('user_role')) === 'ADMIN')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endif
    <style>
        html, body { max-width: 100%; overflow-x: hidden; }
    </style>
</head>
<body class="bg-light">

    @php
        $userRole = strtoupper(session('user_role', 'TEKNISI'));
    @endphp

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                🏢 PT Camperniq Solusi Intermedia
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse mt-2 mt-lg-0" id="navbarMain">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center ms-auto gap-2">
                    <span class="text-white small me-lg-2 my-1 my-lg-0">
                        👤 <strong>{{ session('user_name') ?? session('user_nama') ?? 'User PT Camperniq' }}</strong> ({{ $userRole }})
                    </span>
                    
                    <!-- MENU KHUSUS ADMIN -->
                    @if($userRole === 'ADMIN')
                        <a href="{{ url('/master-barang/create') }}" class="btn btn-success btn-sm fw-bold">+ Master Barang</a>
                        <a href="{{ url('/kelola-user') }}" class="btn btn-warning btn-sm fw-bold text-dark">👥 Kelola User</a>
                        <a href="{{ url('/transaksi/create') }}" class="btn btn-light btn-sm fw-bold">+ Transaksi</a>
                        <a href="{{ url('/riwayat') }}" class="btn btn-outline-light btn-sm">📋 Riwayat Transaksi</a>
                    @endif
                    
                    <a href="{{ url('/logout') }}" class="btn btn-danger btn-sm fw-bold mt-2 mt-lg-0" onclick="return confirm('Apakah Anda yakin ingin keluar?')">🚪 Logout</a>
                </div>
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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ❌ {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- ALERT STOK MINIM / KRITIS -->
        @if(isset($barangStokMinim) && count($barangStokMinim) > 0)
        <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
            <h6 class="fw-bold text-dark mb-2">⚠️ Peringatan Stok Kritis / Perlu Restock!</h6>
            <p class="mb-1 small">Daftar barang yang sudah mencapai atau di bawah batas aman minim:</p>
            <ul class="mb-0 small ps-3">
                @foreach($barangStokMinim as $bm)
                    <li>
                        <strong>{{ $bm->kode_barang }} - {{ $bm->nama_barang }}</strong> 
                        (Sisa Stok: <span class="badge bg-danger">{{ $bm->stok_akhir }} {{ $bm->satuan }}</span> 
                        | Batas Minim: <u>{{ $bm->batas_kritis ?? 5 }} {{ $bm->satuan }}</u>)
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- DASHBOARD ELEMEN (KHUSUS ROLE ADMIN) -->
        @if($userRole === 'ADMIN')
            <!-- Baris 1: Ringkasan Operasional Stok -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card bg-primary text-white shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="card-title text-white-50">Total Jenis Barang</h6>
                            <h2 class="fw-bold mb-0">{{ $totalJenisBarang ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card bg-success text-white shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="card-title text-white-50">Total Barang Masuk</h6>
                            <h2 class="fw-bold mb-0">{{ array_sum($grafikMasuk ?? [0]) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card bg-danger text-white shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="card-title text-white-50">Total Barang Keluar</h6>
                            <h2 class="fw-bold mb-0">{{ array_sum($grafikKeluar ?? [0]) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card bg-warning text-dark shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="card-title text-dark-50">Stok Habis / Kritis</h6>
                            <h2 class="fw-bold mb-0">{{ $totalStokKosong ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Baris 2: Ringkasan Valuation Finansial -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <div class="card bg-dark text-white shadow-sm border-0 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-1">Total Nilai Aset Gudang (Rp)</h6>
                                <h2 class="fw-bold text-success mb-0">Rp {{ number_format($totalNilaiAsetGudang ?? 0, 0, ',', '.') }}</h2>
                            </div>
                            <span class="fs-1">💰</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card bg-dark text-white shadow-sm border-0 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-1">Pengeluaran Belanja Bulan Ini (Rp)</h6>
                                <h2 class="fw-bold text-info mb-0">Rp {{ number_format($totalPengeluaranBelanjaBulanIni ?? 0, 0, ',', '.') }}</h2>
                            </div>
                            <span class="fs-1">🛒</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card bg-dark text-white shadow-sm border-0 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-1">Pemasukan PT Bulan Ini (Rp)</h6>
                                <h2 class="fw-bold text-warning mb-0">Rp {{ number_format($totalPemasukanBarangKeluarBulanIni ?? 0, 0, ',', '.') }}</h2>
                            </div>
                            <span class="fs-1">📈</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Graphic Analytics Tren Mutasi Barang -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    📊 Analytics Tren Mutasi Barang (Tahun {{ date('Y') }})
                </div>
                <div class="card-body p-2 p-md-3">
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="grafikMutasi"></canvas>
                    </div>
                </div>
            </div>
        @endif

        <!-- TABEL KONTROL STOK -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                <span class="small-mobile">📦 {{ $userRole === 'ADMIN' ? 'Realtime Monitoring Stok & Financial Valuation' : 'Ketersediaan Stok Barang Gudang' }}</span>
                <span class="badge bg-secondary">Total Item: {{ count($barangs ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                
                                @if($userRole === 'ADMIN')
                                    <th>Harga Satuan Beli</th>
                                @endif
                                
                                <th class="text-center">Sisa Stok</th>

                                @if($userRole === 'ADMIN')
                                    <th class="text-end">Total Nilai Aset</th>
                                @endif

                                <th class="text-center">Status Stok</th>

                                @if($userRole === 'ADMIN')
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($barangs ?? [] as $index => $b)
                            @php
                                $limitKritis = $b->batas_kritis ?? 5;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $b->kode_barang }}</code></td>
                                <td class="fw-bold">{{ $b->nama_barang }}</td>
                                
                                @if($userRole === 'ADMIN')
                                    <td>Rp {{ number_format($b->harga_satuan ?? 0, 0, ',', '.') }} / {{ $b->satuan }}</td>
                                @endif

                                <td class="text-center fw-bold fs-6">{{ $b->stok_akhir }} {{ $b->satuan }}</td>

                                @if($userRole === 'ADMIN')
                                    <td class="text-end fw-bold text-success">Rp {{ number_format(($b->stok_akhir) * ($b->harga_satuan ?? 0), 0, ',', '.') }}</td>
                                @endif

                                <td class="text-center">
                                    @if($b->stok_akhir <= 0)
                                        <span class="badge bg-danger">Habis</span>
                                    @elseif($b->stok_akhir <= $limitKritis)
                                        <span class="badge bg-warning text-dark">Stok Minim (&le; {{ $limitKritis }})</span>
                                    @else
                                        <span class="badge bg-success">Aman</span>
                                    @endif
                                </td>

                                @if($userRole === 'ADMIN')
                                <td class="text-center">
                                    <a href="{{ url('/master-barang/edit/' . $b->id) }}" class="btn btn-sm btn-outline-warning p-1 px-2">✏️</a>
                                    <a href="{{ url('/master-barang/delete/' . $b->id) }}" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="return confirm('Yakin ingin menghapus?')">🗑️</a>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $userRole === 'ADMIN' ? 8 : 5 }}" class="text-center text-muted p-4">Belum ada data master barang.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @if($userRole === 'ADMIN')
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
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>