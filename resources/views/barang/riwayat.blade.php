<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - PT Camperniq Solusi Intermedia</title>
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
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarRiwayat">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse mt-2 mt-lg-0" id="navbarRiwayat">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center ms-auto gap-2">
                    <a href="{{ url('/') }}" class="btn btn-light btn-sm fw-bold text-dark">← Dashboard</a>
                    <a href="{{ url('/transaksi/create') }}" class="btn btn-success btn-sm fw-bold">+ Transaksi Baru</a>
                    <a href="{{ url('/logout') }}" class="btn btn-danger btn-sm fw-bold mt-2 mt-lg-0 ms-lg-2" onclick="return confirm('Apakah Anda yakin ingin keluar?')">🚪 Logout</a>
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

        <!-- Card Filter Lengkap -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ url('/riwayat') }}" id="filterForm">
                    <div class="row g-2 align-items-center">
                        
                        <div class="col-12 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted">🔍</span>
                                <input type="text" id="searchInput" onkeyup="filterTable()" class="form-control border-start-0" placeholder="Cari kode, nama barang, teknisi...">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <select id="filterJenis" onchange="filterTable()" class="form-select form-select-sm fw-bold">
                                <option value="">📦 Semua Jenis Transaksi</option>
                                <option value="MASUK">📥 Barang Masuk</option>
                                <option value="KELUAR">📤 Barang Keluar</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="d-flex gap-1">
                                <select name="bulan" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                                    <option value="01" {{ request('bulan', date('m')) == '01' ? 'selected' : '' }}>Januari</option>
                                    <option value="02" {{ request('bulan', date('m')) == '02' ? 'selected' : '' }}>Februari</option>
                                    <option value="03" {{ request('bulan', date('m')) == '03' ? 'selected' : '' }}>Maret</option>
                                    <option value="04" {{ request('bulan', date('m')) == '04' ? 'selected' : '' }}>April</option>
                                    <option value="05" {{ request('bulan', date('m')) == '05' ? 'selected' : '' }}>Mei</option>
                                    <option value="06" {{ request('bulan', date('m')) == '06' ? 'selected' : '' }}>Juni</option>
                                    <option value="07" {{ request('bulan', date('m')) == '07' ? 'selected' : '' }}>Juli</option>
                                    <option value="08" {{ request('bulan', date('m')) == '08' ? 'selected' : '' }}>Agustus</option>
                                    <option value="09" {{ request('bulan', date('m')) == '09' ? 'selected' : '' }}>September</option>
                                    <option value="10" {{ request('bulan', date('m')) == '10' ? 'selected' : '' }}>Oktober</option>
                                    <option value="11" {{ request('bulan', date('m')) == '11' ? 'selected' : '' }}>November</option>
                                    <option value="12" {{ request('bulan', date('m')) == '12' ? 'selected' : '' }}>Desember</option>
                                </select>
                                <select name="tahun" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                                    <option value="2026" {{ request('tahun', date('Y')) == '2026' ? 'selected' : '' }}>2026</option>
                                    <option value="2025" {{ request('tahun', date('Y')) == '2025' ? 'selected' : '' }}>2025</option>
                                    <option value="2024" {{ request('tahun', date('Y')) == '2024' ? 'selected' : '' }}>2024</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 text-md-end">
                            <div class="btn-group btn-group-sm w-100 w-md-auto">
                                <button type="button" onclick="exportData('pdf')" class="btn btn-outline-secondary fw-bold">🖨️ PDF</button>
                                <button type="button" onclick="exportData('excel')" class="btn btn-outline-success fw-bold">📊 Excel</button>
                                <button type="button" onclick="exportData('word')" class="btn btn-outline-primary fw-bold">📝 Word</button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Riwayat Mutasi Transaksi Barang -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">📜 Riwayat Mutasi Transaksi Barang</h5>
                    <small class="text-muted">Catatan log barang masuk dan keluar secara tersimpan otomatis</small>
                </div>
                <span class="badge bg-primary px-3 py-2 fs-6 align-self-start align-self-md-auto">Total: <span id="totalCounter">{{ count($riwayats ?? []) }}</span> Transaksi</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap" id="riwayatTable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-center">Jenis Transaksi</th>
                                <th class="text-center">Jumlah (Qty)</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal (Rp)</th>
                                <th>Penerima / Teknisi</th>
                                <th>Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayats ?? [] as $index => $r)
                            <tr class="data-row" data-jenis="{{ strtoupper($r->jenis_transaksi ?? $r->jenis ?? $r->tipe ?? 'MASUK') }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ date('d/m/Y H:i', strtotime($r->created_at)) }}</td>
                                <td><code class="text-danger">{{ $r->kode_barang }}</code></td>
                                <td class="fw-bold text-dark">{{ $r->nama_barang }}</td>
                                <td class="text-center">
                                    @if(strtoupper($r->jenis_transaksi ?? $r->jenis ?? $r->tipe ?? 'MASUK') === 'MASUK')
                                        <span class="badge bg-success px-2 py-1">MASUK</span>
                                    @else
                                        <span class="badge bg-danger px-2 py-1">KELUAR</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $r->jumlah }} {{ $r->satuan }}</td>
                                <td class="text-end text-muted">Rp {{ number_format($r->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-primary">Rp {{ number_format(($r->jumlah ?? 0) * ($r->harga_satuan ?? 0), 0, ',', '.') }}</td>
                                <td>{{ $r->penerima ?? '-' }}</td>
                                <td>{{ $r->keterangan ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ url('/transaksi/delete/' . $r->id) }}" class="btn btn-sm btn-light text-muted border p-1 px-2" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">🗑️</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted p-4">Belum ada data riwayat transaksi tersimpan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        function filterTable() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const filterJenis = document.getElementById('filterJenis').value;
            const rows = document.querySelectorAll('.data-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const jenis = row.getAttribute('data-jenis');

                const matchesSearch = text.includes(searchValue);
                const matchesJenis = filterJenis === '' || jenis === filterJenis;

                if (matchesSearch && matchesJenis) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('totalCounter').innerText = visibleCount;
        }

        function exportData(format) {
            const jenis = document.getElementById('filterJenis').value || 'MASUK';
            const bulan = document.querySelector('select[name="bulan"]').value;
            const tahun = document.querySelector('select[name="tahun"]').value;

            const baseUrl = "{{ url('/riwayat/export-') }}";
            window.location.href = baseUrl + format + '?bulan=' + bulan + '&tahun=' + tahun + '&jenis=' + jenis;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>