<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Inventaris - PT Camperniq Solusi Intermedia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        @media print {
            .no-print { 
                display: none !important; 
            }
            body {
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-white">

    <!-- Header Navigation dengan Logo SI-MONI (Akan Otomatis Sembunyi Saat Cetak Print) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4 no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}" title="Klik untuk Kembali ke Dashboard">
                🏢 SI-MONI <span class="fs-6 fw-normal">| PT Camperniq Solusi Intermedia</span>
            </a>
            <div>
                <a href="{{ url('/') }}" class="btn btn-light btn-sm fw-bold me-1">🏠 Dashboard</a>
                <a href="{{ url('/riwayat') }}" class="btn btn-outline-light btn-sm">📋 Riwayat Transaksi</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        
        <div class="text-center mb-4">
            <h3 class="fw-bold">LAPORAN MUTASI STOK INVENTARIS</h3>
            <h5>PT CAMPERNIQ SOLUSI INTERMEDIA</h5>
            <p class="text-muted small mb-1">
                Periode: <strong>{{ date('F', mktime(0, 0, 0, $bulan, 10)) }} {{ $tahun }}</strong>
            </p>
            <p class="text-muted small">Dicetak pada: <span id="waktu-cetak"></span></p>
        </div>

        <!-- Panel Filter Periode & Pilihan Export -->
        <div class="card mb-4 no-print shadow-sm border-primary">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <!-- Form Filter Bulan & Tahun -->
                    <div class="col-md-7">
                        <form action="{{ url('/cetak-laporan') }}" method="GET" class="d-flex align-items-center gap-2">
                            <label class="fw-bold mb-0">Periode:</label>
                            <select name="bulan" class="form-select" style="width: 150px;">
                                @foreach([
                                    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April',
                                    '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus',
                                    '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
                                ] as $key => $val)
                                    <option value="{{ $key }}" {{ $bulan == $key ? 'selected' : '' }}>{{ $val }}</option>
                                @endforeach
                            </select>

                            <!-- Pilihan Tahun Otomatis Realtime dari Database -->
                            <select name="tahun" class="form-select" style="width: 110px;">
                                @foreach($daftarTahun as $t)
                                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary btn-sm">🔍 Tampilkan</button>
                        </form>
                    </div>

                    <!-- Dropdown Simpan / Cetak & Tombol Kembali -->
                    <div class="col-md-5 d-flex justify-content-end align-items-center gap-2">
                        <select id="pilihan-tujuan" class="form-select" style="width: 200px;">
                            <option value="print">🖨️ Cetak / Simpan PDF</option>
                            <option value="excel">📊 Simpan sebagai Excel (.xlsx)</option>
                            <option value="word">📄 Simpan sebagai Word (.docx)</option>
                        </select>
                        <button onclick="prosesSimpan()" class="btn btn-success fw-bold btn-sm">Proses</button>
                        <a href="{{ url('/') }}" class="btn btn-secondary btn-sm fw-bold">← Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data Laporan -->
        <div id="area-laporan">
            <table id="tabel-laporan" class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Kode & Nama Barang</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Teknisi / PJ</th>
                        <th>Lokasi Pemasangan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $index => $t)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ date('d-m-Y', strtotime($t->tanggal_transaksi)) }}</td>
                        <td>{{ $t->kode_barang }} - {{ $t->nama_barang }}</td>
                        <td class="text-center">{{ $t->tipe_transaksi }}</td>
                        <td class="text-center">{{ $t->jumlah }} {{ $t->satuan }}</td>
                        <td>{{ $t->penerima_teknisi ?? '-' }}</td>
                        <td>{{ $t->lokasi_pemasangan ?? '-' }}</td>
                        <td>{{ $t->keterangan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Tidak ada data transaksi pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function getWaktuSekarang() {
            var now = new Date();
            var tgl = String(now.getDate()).padStart(2, '0');
            var bln = String(now.getMonth() + 1).padStart(2, '0');
            var thn = now.getFullYear();
            var jam = String(now.getHours()).padStart(2, '0');
            var menit = String(now.getMinutes()).padStart(2, '0');
            var detik = String(now.getSeconds()).padStart(2, '0');

            return tgl + '-' + bln + '-' + thn + ' ' + jam + ':' + menit + ':' + detik;
        }

        document.getElementById('waktu-cetak').innerText = getWaktuSekarang();

        function prosesSimpan() {
            var tujuan = document.getElementById('pilihan-tujuan').value;

            if (tujuan === 'print') {
                window.print();
            } else if (tujuan === 'excel') {
                simpanExcel();
            } else if (tujuan === 'word') {
                simpanWord();
            }
        }

        function simpanExcel() {
            var waktuSekarang = getWaktuSekarang();
            var excelData = [
                ["LAPORAN MUTASI STOK INVENTARIS"],
                ["PT CAMPERNIQ SOLUSI INTERMEDIA"],
                ["Tanggal Cetak: " + waktuSekarang],
                [] 
            ];

            var headers = [];
            document.querySelectorAll("#tabel-laporan thead th").forEach(function(th) {
                headers.push(th.innerText);
            });
            excelData.push(headers);

            document.querySelectorAll("#tabel-laporan tbody tr").forEach(function(tr) {
                var row = [];
                tr.querySelectorAll("td").forEach(function(td) {
                    row.push(td.innerText);
                });
                excelData.push(row);
            });

            var ws = XLSX.utils.aoa_to_sheet(excelData);
            ws['!cols'] = [
                { wch: 8 }, { wch: 16 }, { wch: 45 }, { wch: 14 },
                { wch: 18 }, { wch: 22 }, { wch: 28 }, { wch: 35 }
            ];

            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Mutasi Stok");
            XLSX.writeFile(wb, 'Laporan_Mutasi_Stok_Camperniq.xlsx');
        }

        function simpanWord() {
            var waktuSekarang = getWaktuSekarang();
            var headerHTML = "<div style='text-align:center;'><h2 style='margin:0;'>LAPORAN MUTASI STOK INVENTARIS</h2><h3>PT CAMPERNIQ SOLUSI INTERMEDIA</h3><p>Dicetak pada: " + waktuSekarang + "</p></div><br>";
            var tableHTML = document.getElementById('area-laporan').innerHTML;
            
            var html = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>" +
                "<head><meta charset='utf-8'><title>Laporan Word</title>" +
                "<style>table {border-collapse: collapse; width: 100%;} td, th {border: 1px solid #000; padding: 8px; text-align: left;} th {background-color: #f2f2f2;}</style>" +
                "</head><body>" + headerHTML + tableHTML + "</body></html>";

            var blob = new Blob(['\ufeff', html], { type: 'application/msword' });
            var url = 'data:application/vnd.ms-word;charset=utf-8,' + encodeURIComponent(html);
            
            var downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            downloadLink.href = url;
            downloadLink.download = 'Laporan_Mutasi_Stok_Camperniq.doc';
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>

</body>
</html>