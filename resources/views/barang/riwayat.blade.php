<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat & Rekapitulasi Transaksi - SI-MONI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4 no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                🏢 SI-MONI <span class="fs-6 fw-normal">| PT Camperniq Solusi Intermedia</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ url('/') }}" class="btn btn-light btn-sm fw-bold">← Dashboard</a>
                <a href="{{ url('/transaksi/create') }}" class="btn btn-success btn-sm fw-bold">+ Transaksi Baru</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3 no-print" role="alert">
                ✅ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Card Widget Rekapitulasi Finansial Bulanan -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-primary text-white h-100">
                    <div class="card-body p-3">
                        <small class="text-white-50 text-uppercase fw-bold">🛒 Total Pengeluaran Belanja (Barang Masuk)</small>
                        <h3 class="fw-bold my-1">Rp {{ number_format($totalPengeluaranBulanIni ?? 0, 0, ',', '.') }}</h3>
                        <small class="text-white-50">Periode: {{ $namaBulanPilihan }} {{ $tahunPilihan }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-success text-white h-100">
                    <div class="card-body p-3">
                        <small class="text-white-50 text-uppercase fw-bold">📥 Total Qty Barang Masuk</small>
                        <h3 class="fw-bold my-1">{{ number_format($totalQtyMasukBulanIni ?? 0, 0, ',', '.') }} Item</h3>
                        <small class="text-white-50">Periode: {{ $namaBulanPilihan }} {{ $tahunPilihan }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-danger text-white h-100">
                    <div class="card-body p-3">
                        <small class="text-white-50 text-uppercase fw-bold">📤 Total Qty Barang Keluar</small>
                        <h3 class="fw-bold my-1">{{ number_format($totalQtyKeluarBulanIni ?? 0, 0, ',', '.') }} Item</h3>
                        <small class="text-white-50">Periode: {{ $namaBulanPilihan }} {{ $tahunPilihan }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Filter Periode, Search & Export Server-Side -->
        <div class="card shadow-sm border-0 mb-4 no-print">
            <div class="card-body p-3">
                <form action="{{ url('/riwayat') }}" method="GET" class="row g-2 align-items-center mb-3 border-bottom pb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1">Pilih Bulan Rekapan</label>
                        <select name="bulan" class="form-select form-select-sm">
                            @php
                                $bulans = [
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ];
                            @endphp
                            @foreach($bulans as $key => $val)
                                <option value="{{ $key }}" {{ ($bulanPilihan == $key) ? 'selected' : '' }}>{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-1">Pilih Tahun</label>
                        <select name="tahun" class="form-select form-select-sm">
                            @for($t = date('Y'); $t >= 2024; $t--)
                                <option value="{{ $t }}" {{ ($tahunPilihan == $t) ? 'selected' : '' }}>{{ $t }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">🔍 Rekap Periode</button>
                    </div>
                </form>

                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" id="cariBarang" class="form-control form-control-sm" placeholder="🔍 Cari kode, nama, teknisi..." onkeyup="filterTabel()">
                    </div>
                    <div class="col-md-3">
                        <select id="filterJenis" class="form-select form-select-sm" onchange="filterTabel()">
                            <option value="">-- Semua Jenis Transaksi --</option>
                            <option value="MASUK">📥 Barang Masuk</option>
                            <option value="KELUAR">📤 Barang Keluar</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-end d-flex gap-1 justify-content-end">
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold">🖨️ Cetak</button>
                        <a href="{{ url('/riwayat/export-pdf?bulan='.$bulanPilihan.'&tahun='.$tahunPilihan) }}" class="btn btn-outline-danger btn-sm fw-bold">📄 Export PDF</a>
                        <a href="{{ url('/riwayat/export-word?bulan='.$bulanPilihan.'&tahun='.$tahunPilihan) }}" class="btn btn-outline-primary btn-sm fw-bold">📝 Export Word</a>
                        <a href="{{ url('/riwayat/export-excel?bulan='.$bulanPilihan.'&tahun='.$tahunPilihan) }}" class="btn btn-outline-success btn-sm fw-bold">📊 Export Excel</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Utama -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">📋 Riwayat & Mutasi Barang - {{ $namaBulanPilihan }} {{ $tahunPilihan }}</h5>
                    <small class="text-muted">Catatan log barang masuk dan keluar tersimpan otomatis</small>
                </div>
                <span class="badge bg-primary fs-6 no-print">Total: {{ count($riwayats ?? []) }} Transaksi</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabelRiwayat">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-center">Jenis Transaksi</th>
                                <th class="text-center">Jumlah (Qty)</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal (Rp)</th>
                                <th>Penerima / Teknisi</th>
                                <th>Keterangan</th>
                                @if(session('user_role') === 'ADMIN')
                                    <th class="text-center no-print">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayats ?? [] as $index => $r)
                            @php
                                $jenis = $r->jenis_transaksi ?? $r->jenis ?? $r->tipe ?? $r->status ?? 'MASUK';
                                $subtotal = ($r->jumlah ?? 0) * ($r->harga_satuan ?? 0);
                                $tglFormatted = \Carbon\Carbon::parse($r->created_at)->translatedFormat('d M Y H:i');
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><small class="text-muted fw-bold">{{ $tglFormatted }}</small></td>
                                <td><code>{{ $r->kode_barang }}</code></td>
                                <td class="fw-bold">{{ $r->nama_barang }}</td>
                                <td class="text-center">
                                    @if(strtoupper($jenis) === 'MASUK')
                                        <span class="badge bg-success">📥 MASUK</span>
                                    @else
                                        <span class="badge bg-danger">📤 KELUAR</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $r->jumlah }} {{ $r->satuan }}</td>
                                <td>Rp {{ number_format($r->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                <td class="fw-bold text-primary">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                <td>{{ $r->penerima ?? '-' }}</td>
                                <td><small class="text-muted">{{ $r->keterangan ?? '-' }}</small></td>
                                @if(session('user_role') === 'ADMIN')
                                <td class="text-center no-print">
                                    <a href="{{ url('/transaksi/delete/' . $r->id) }}" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="return confirm('Hapus transaksi ini?')" title="Hapus Transaksi">🗑️</a>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted p-5">
                                    📜 Tidak ada riwayat transaksi barang pada bulan {{ $namaBulanPilihan }} {{ $tahunPilihan }}.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Filter Pencarian Realtime -->
    <script>
        function filterTabel() {
            let inputCari = document.getElementById("cariBarang").value.toUpperCase();
            let inputJenis = document.getElementById("filterJenis").value.toUpperCase();
            let tr = document.getElementById("tabelRiwayat").getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let tdKode = tr[i].getElementsByTagName("td")[2];
                let tdNama = tr[i].getElementsByTagName("td")[3];
                let tdJenis = tr[i].getElementsByTagName("td")[4];
                let tdPenerima = tr[i].getElementsByTagName("td")[8];

                if (tdKode || tdNama) {
                    let txtKode = tdKode.textContent || tdKode.innerText;
                    let txtNama = tdNama.textContent || tdNama.innerText;
                    let txtJenis = tdJenis.textContent || tdJenis.innerText;
                    let txtPenerima = tdPenerima ? (tdPenerima.textContent || tdPenerima.innerText) : "";

                    let matchCari = (txtKode.toUpperCase().indexOf(inputCari) > -1) || 
                                    (txtNama.toUpperCase().indexOf(inputCari) > -1) ||
                                    (txtPenerima.toUpperCase().indexOf(inputCari) > -1);
                    
                    let matchJenis = (inputJenis === "") || (txtJenis.toUpperCase().indexOf(inputJenis) > -1);

                    if (matchCari && matchJenis) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>