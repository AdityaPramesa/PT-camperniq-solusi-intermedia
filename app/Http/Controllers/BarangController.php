<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangController extends Controller
{
    private function getKolomJenisTransaksi()
    {
        if (Schema::hasColumn('transaksi_barang', 'jenis_transaksi')) {
            return 'jenis_transaksi';
        } elseif (Schema::hasColumn('transaksi_barang', 'jenis')) {
            return 'jenis';
        } elseif (Schema::hasColumn('transaksi_barang', 'tipe')) {
            return 'tipe';
        } elseif (Schema::hasColumn('transaksi_barang', 'status')) {
            return 'status';
        }
        return 'jenis_transaksi';
    }

    /**
     * Helper untuk menentukan batas stok minim/kritis berdasarkan kustom DB atau Satuan Barang
     */
    private function getBatasStokMinim($barang)
    {
        // 1. Jika ada kolom stok_minimal / min_stok di database, gunakan nilainya
        if (isset($barang->stok_minimal) && is_numeric($barang->stok_minimal)) {
            return (int) $barang->stok_minimal;
        }
        if (isset($barang->min_stok) && is_numeric($barang->min_stok)) {
            return (int) $barang->min_stok;
        }

        // 2. Jika tidak ada, gunakan logika otomatis berdasarkan jenis Satuan
        $satuanClean = strtoupper(trim($barang->satuan ?? ''));
        
        if (str_contains($satuanClean, 'METER') || $satuanClean === 'M') {
            return 200; // Perkabelan: Kritis jika <= 200 Meter
        }

        return 5; // Barang PCS / Unit / ODP / Dll: Kritis jika <= 5 PCS
    }

    // ----------------------------------------------------
    // DASHBOARD UTAMA
    // ----------------------------------------------------
    public function index()
    {
        // 🔒 CEK APAKAH USER SUDAH LOGIN?
        // Jika belum ada session login, paksa arahkan ke halaman LOGIN dulu
        if (!session()->has('user_id') && !session()->has('user_role') && !session()->has('user_name')) {
            return redirect('/login');
        }

        $barangs = DB::table('master_barang')->get();

        $totalJenisBarang = count($barangs);
        $totalBarangMasuk = 0;
        $totalBarangKeluar = 0;
        $totalStokKosong = 0;

        $totalNilaiAsetGudang = 0;
        $totalPengeluaranBelanjaBulanIni = 0;
        $totalPemasukanBarangKeluarBulanIni = 0;

        $bulanIni = date('m');
        $tahunIni = date('Y');

        $kolomJenis = $this->getKolomJenisTransaksi();

        foreach ($barangs as $b) {
            $masuk = DB::table('transaksi_barang')
                ->where('barang_id', $b->id)
                ->where($kolomJenis, 'MASUK')
                ->sum('jumlah');

            $keluar = DB::table('transaksi_barang')
                ->where('barang_id', $b->id)
                ->where($kolomJenis, 'KELUAR')
                ->sum('jumlah');

            $stokAwal = $b->stok_awal ?? $b->stok ?? 0;
            $hargaSatuan = $b->harga_satuan ?? 0;

            $b->total_masuk = $masuk;
            $b->total_keluar = $keluar;
            $b->stok_akhir = $stokAwal + $masuk - $keluar;
            $b->harga_satuan = $hargaSatuan;

            // Hitung Batas Kritis Spesifik
            $b->batas_kritis = $this->getBatasStokMinim($b);

            $totalNilaiAsetGudang += ($b->stok_akhir * $hargaSatuan);

            // Cek apakah sisa stok <= batas kritis item ini
            if ($b->stok_akhir <= $b->batas_kritis) {
                $totalStokKosong++;
            }
        }

        // Hitung Belanja (Barang Masuk) Bulan Ini
        $transaksiMasukBulanIni = DB::table('transaksi_barang')
            ->join('master_barang', 'transaksi_barang.barang_id', '=', 'master_barang.id')
            ->where('transaksi_barang.' . $kolomJenis, 'MASUK')
            ->whereMonth('transaksi_barang.created_at', $bulanIni)
            ->whereYear('transaksi_barang.created_at', $tahunIni)
            ->select('transaksi_barang.jumlah', 'master_barang.harga_satuan')
            ->get();

        foreach ($transaksiMasukBulanIni as $t) {
            $totalPengeluaranBelanjaBulanIni += ($t->jumlah * ($t->harga_satuan ?? 0));
        }

        // Hitung Pemasukan (Barang Keluar) Bulan Ini
        $transaksiKeluarBulanIni = DB::table('transaksi_barang')
            ->join('master_barang', 'transaksi_barang.barang_id', '=', 'master_barang.id')
            ->where('transaksi_barang.' . $kolomJenis, 'KELUAR')
            ->whereMonth('transaksi_barang.created_at', $bulanIni)
            ->whereYear('transaksi_barang.created_at', $tahunIni)
            ->select('transaksi_barang.jumlah', 'master_barang.harga_satuan')
            ->get();

        foreach ($transaksiKeluarBulanIni as $tk) {
            $totalPemasukanBarangKeluarBulanIni += ($tk->jumlah * ($tk->harga_satuan ?? 0));
        }

        // Filter Barang yang Stoknya Kritis/Minim berdasarkan batas masing-masing
        $barangStokMinim = collect($barangs)->filter(function ($b) {
            return $b->stok_akhir <= $b->batas_kritis;
        })->values();

        $grafikMasuk = [];
        $grafikKeluar = [];
        for ($m = 1; $m <= 12; $m++) {
            $grafikMasuk[] = DB::table('transaksi_barang')
                ->where($kolomJenis, 'MASUK')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $tahunIni)
                ->sum('jumlah');

            $grafikKeluar[] = DB::table('transaksi_barang')
                ->where($kolomJenis, 'KELUAR')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $tahunIni)
                ->sum('jumlah');
        }

        if (view()->exists('barang.index')) {
            return view('barang.index', compact(
                'barangs', 'totalJenisBarang', 'totalBarangMasuk', 'totalBarangKeluar',
                'totalStokKosong', 'barangStokMinim', 'grafikMasuk', 'grafikKeluar',
                'totalNilaiAsetGudang', 'totalPengeluaranBelanjaBulanIni', 'totalPemasukanBarangKeluarBulanIni'
            ));
        }

        return view('index', compact(
            'barangs', 'totalJenisBarang', 'totalBarangMasuk', 'totalBarangKeluar',
            'totalStokKosong', 'barangStokMinim', 'grafikMasuk', 'grafikKeluar',
            'totalNilaiAsetGudang', 'totalPengeluaranBelanjaBulanIni', 'totalPemasukanBarangKeluarBulanIni'
        ));
    }

    // ----------------------------------------------------
    // KELOLA MASTER BARANG
    // ----------------------------------------------------
    public function createMasterBarang()
    {
        return view('barang.create_master');
    }

    public function storeMasterBarang(Request $request)
    {
        $request->validate([
            'kode_barang'  => 'required|max:50',
            'nama_barang'  => 'required|max:255',
            'satuan'       => 'required|max:20',
            'stok_awal'    => 'required|numeric',
            'harga_satuan' => 'required|numeric',
        ]);

        $satuanClean = substr($request->satuan, 0, 20);
        $kodeClean   = substr($request->kode_barang, 0, 50);

        $data = [
            'kode_barang'  => $kodeClean,
            'nama_barang'  => $request->nama_barang,
            'satuan'       => $satuanClean,
            'harga_satuan' => $request->harga_satuan,
        ];

        $columns = Schema::getColumnListing('master_barang');

        if (in_array('stok_awal', $columns)) {
            $data['stok_awal'] = $request->stok_awal;
        } elseif (in_array('stok', $columns)) {
            $data['stok'] = $request->stok_awal;
        }

        // Jika ada input kustom stok minimal dari form
        if ($request->has('stok_minimal') && in_array('stok_minimal', $columns)) {
            $data['stok_minimal'] = $request->stok_minimal;
        }

        DB::table('master_barang')->insert($data);

        return redirect('/')->with('success', 'Master Barang baru berhasil ditambahkan!');
    }

    public function editMasterBarang($id)
    {
        $barang = DB::table('master_barang')->where('id', $id)->first();
        return view('barang.edit_master', compact('barang'));
    }

    public function updateMasterBarang(Request $request, $id)
    {
        $request->validate([
            'kode_barang'  => 'required|max:50',
            'nama_barang'  => 'required|max:255',
            'satuan'       => 'required|max:20',
            'stok_awal'    => 'required|numeric',
            'harga_satuan' => 'required|numeric',
        ]);

        $satuanClean = substr($request->satuan, 0, 20);
        $kodeClean   = substr($request->kode_barang, 0, 50);

        $data = [
            'kode_barang'  => $kodeClean,
            'nama_barang'  => $request->nama_barang,
            'satuan'       => $satuanClean,
            'harga_satuan' => $request->harga_satuan,
        ];

        $columns = Schema::getColumnListing('master_barang');

        if (in_array('stok_awal', $columns)) {
            $data['stok_awal'] = $request->stok_awal;
        } elseif (in_array('stok', $columns)) {
            $data['stok'] = $request->stok_awal;
        }

        if ($request->has('stok_minimal') && in_array('stok_minimal', $columns)) {
            $data['stok_minimal'] = $request->stok_minimal;
        }

        DB::table('master_barang')->where('id', $id)->update($data);

        return redirect('/')->with('success', 'Master Barang berhasil diperbarui!');
    }

    public function destroyMasterBarang($id)
    {
        DB::table('master_barang')->where('id', $id)->delete();
        DB::table('transaksi_barang')->where('barang_id', $id)->delete();

        return redirect('/')->with('success', 'Master Barang dan riwayat transaksinya berhasil dihapus!');
    }

    // ----------------------------------------------------
    // KELOLA TRANSAKSI
    // ----------------------------------------------------
    public function createTransaksi()
    {
        $barangs = DB::table('master_barang')->get();
        $kolomJenis = $this->getKolomJenisTransaksi();

        foreach ($barangs as $b) {
            $masuk = DB::table('transaksi_barang')
                ->where('barang_id', $b->id)
                ->where($kolomJenis, 'MASUK')
                ->sum('jumlah');

            $keluar = DB::table('transaksi_barang')
                ->where('barang_id', $b->id)
                ->where($kolomJenis, 'KELUAR')
                ->sum('jumlah');

            $stokAwal = $b->stok_awal ?? $b->stok ?? 0;
            $b->stok_akhir = $stokAwal + $masuk - $keluar;
        }

        if (view()->exists('create_transaksi')) {
            return view('create_transaksi', compact('barangs'));
        }
        
        return view('barang.create_transaksi', compact('barangs'));
    }

    public function storeTransaksi(Request $request)
    {
        $request->validate([
            'barang_id'       => 'required',
            'jenis_transaksi' => 'required',
            'jumlah'          => 'required|numeric|min:1',
        ]);

        $kolomJenis = $this->getKolomJenisTransaksi();
        $waktuInput = $request->tanggal ? $request->tanggal . ' ' . date('H:i:s') : now();

        $dataInsert = [
            'barang_id' => $request->barang_id,
            $kolomJenis => $request->jenis_transaksi,
            'jumlah'    => $request->jumlah,
        ];

        if (Schema::hasColumn('transaksi_barang', 'tanggal_transaksi')) {
            $dataInsert['tanggal_transaksi'] = $waktuInput;
        }

        if (Schema::hasColumn('transaksi_barang', 'user_id')) {
            $userNama = session('user_name') ?? 'Admin';
            $tabelUser = Schema::hasTable('users') ? 'users' : 'user';
            
            $userDB = DB::table($tabelUser)
                ->where('nama_lengkap', $userNama)
                ->orWhere('username', $userNama)
                ->first();

            $dataInsert['user_id'] = $userDB->id ?? session('user_id') ?? 1;
        }

        if (Schema::hasColumn('transaksi_barang', 'created_at')) {
            $dataInsert['created_at'] = $waktuInput;
        }

        if (Schema::hasColumn('transaksi_barang', 'updated_at')) {
            $dataInsert['updated_at'] = $waktuInput;
        }

        if (Schema::hasColumn('transaksi_barang', 'penerima')) {
            $dataInsert['penerima'] = $request->penerima ?? session('user_name') ?? 'Teknisi';
        }

        if (Schema::hasColumn('transaksi_barang', 'keterangan')) {
            $dataInsert['keterangan'] = $request->keterangan ?? '-';
        }

        DB::table('transaksi_barang')->insert($dataInsert);

        return redirect('/')->with('success', 'Transaksi barang berhasil dicatat!');
    }

    public function destroyTransaksi($id)
    {
        DB::table('transaksi_barang')->where('id', $id)->delete();
        return redirect('/riwayat')->with('success', 'Transaksi berhasil dihapus!');
    }

    // ----------------------------------------------------
    // HALAMAN RIWAYAT & REKAPITULASI BULANAN
    // ----------------------------------------------------
    public function riwayat(Request $request)
    {
        $bulanPilihan = $request->get('bulan', date('m'));
        $tahunPilihan = $request->get('tahun', date('Y'));

        $namaBulans = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $namaBulanPilihan = $namaBulans[str_pad($bulanPilihan, 2, '0', STR_PAD_LEFT)] ?? 'September';

        $riwayats = DB::table('transaksi_barang')
            ->join('master_barang', 'transaksi_barang.barang_id', '=', 'master_barang.id')
            ->whereMonth('transaksi_barang.created_at', $bulanPilihan)
            ->whereYear('transaksi_barang.created_at', $tahunPilihan)
            ->select('transaksi_barang.*', 'master_barang.kode_barang', 'master_barang.nama_barang', 'master_barang.satuan', 'master_barang.harga_satuan')
            ->orderBy('transaksi_barang.id', 'DESC')
            ->get();

        $totalPengeluaranBulanIni = 0;
        $totalQtyMasukBulanIni = 0;
        $totalQtyKeluarBulanIni = 0;

        foreach ($riwayats as $r) {
            $jenis = $r->jenis_transaksi ?? $r->jenis ?? $r->tipe ?? $r->status ?? 'MASUK';
            if (strtoupper($jenis) === 'MASUK') {
                $totalPengeluaranBulanIni += ($r->jumlah * ($r->harga_satuan ?? 0));
                $totalQtyMasukBulanIni += $r->jumlah;
            } else {
                $totalQtyKeluarBulanIni += $r->jumlah;
            }
        }

        if (view()->exists('barang.riwayat')) {
            return view('barang.riwayat', compact(
                'riwayats', 'bulanPilihan', 'tahunPilihan', 'namaBulanPilihan',
                'totalPengeluaranBulanIni', 'totalQtyMasukBulanIni', 'totalQtyKeluarBulanIni'
            ));
        }

        return view('riwayat', compact(
            'riwayats', 'bulanPilihan', 'tahunPilihan', 'namaBulanPilihan',
            'totalPengeluaranBulanIni', 'totalQtyMasukBulanIni', 'totalQtyKeluarBulanIni'
        ));
    }

    // ----------------------------------------------------
    // FITUR EKSPOR SERVER-SIDE
    // ----------------------------------------------------
    public function exportPdf(Request $request)
    {
        $bulanPilihan = $request->get('bulan', date('m'));
        $tahunPilihan = $request->get('tahun', date('Y'));
        $jenisFilter  = strtoupper($request->get('jenis', 'MASUK'));

        $namaBulans = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $namaBulanPilihan = $namaBulans[str_pad($bulanPilihan, 2, '0', STR_PAD_LEFT)] ?? 'September';

        $kolomJenis = $this->getKolomJenisTransaksi();

        $query = DB::table('transaksi_barang')
            ->join('master_barang', 'transaksi_barang.barang_id', '=', 'master_barang.id')
            ->whereMonth('transaksi_barang.created_at', $bulanPilihan)
            ->whereYear('transaksi_barang.created_at', $tahunPilihan);

        if (in_array($jenisFilter, ['MASUK', 'KELUAR'])) {
            $query->where('transaksi_barang.' . $kolomJenis, $jenisFilter);
        }

        $riwayats = $query->select('transaksi_barang.*', 'master_barang.kode_barang', 'master_barang.nama_barang', 'master_barang.satuan', 'master_barang.harga_satuan')
            ->orderBy('transaksi_barang.id', 'DESC')
            ->get();

        $totalNilai = 0;
        foreach ($riwayats as $r) {
            $totalNilai += ($r->jumlah * ($r->harga_satuan ?? 0));
        }

        $judulLaporan = ($jenisFilter === 'MASUK') ? 'LAPORAN REKAPITULASI BARANG MASUK' : 'LAPORAN REKAPITULASI BARANG KELUAR';
        $labelTotal   = ($jenisFilter === 'MASUK') ? 'TOTAL BELANJA BARANG MASUK:' : 'TOTAL EVALUASI NILAI BARANG KELUAR:';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>' . $judulLaporan . '</title>
            <style>
                body { font-family: sans-serif; font-size: 10px; margin: 0; padding: 0; }
                .header { text-align: center; margin-bottom: 15px; }
                .header h2 { margin: 0; font-size: 16px; color: #1F4E78; }
                .header h4 { margin: 4px 0; font-size: 12px; color: #333; }
                .header p { margin: 0; font-size: 9px; color: #666; }
                .rekap-box { background-color: #EBF1F5; padding: 8px; margin-bottom: 15px; border-left: 4px solid #1F4E78; font-size: 11px; }
                table { width: 100%; border-collapse: collapse; margin-top: 5px; }
                th, td { border: 1px solid #777; padding: 5px; text-align: left; }
                th { background-color: #1F4E78; color: #ffffff; text-align: center; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>PT CAMPERNIQ SOLUSI INTERMEDIA</h2>
                <h4>' . $judulLaporan . '</h4>
                <p>Periode Rekapan: ' . $namaBulanPilihan . ' ' . $tahunPilihan . '</p>
            </div>

            <div class="rekap-box">
                <b>' . $labelTotal . '</b> 
                <span style="color: #1F4E78; font-size: 13px; font-weight: bold;">Rp ' . number_format($totalNilai, 0, ',', '.') . '</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="12%">Tanggal</th>
                        <th width="12%">Kode</th>
                        <th width="24%">Nama Barang</th>
                        <th width="10%">Jumlah</th>
                        <th width="12%">Harga Satuan</th>
                        <th width="12%">Subtotal</th>
                        <th width="14%">Penerima / Teknisi</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($riwayats as $index => $r) {
            $subtotal = ($r->jumlah ?? 0) * ($r->harga_satuan ?? 0);
            $html .= '<tr>
                <td class="text-center">' . ($index + 1) . '</td>
                <td class="text-center">' . date('d/m/Y H:i', strtotime($r->created_at)) . '</td>
                <td><code>' . htmlspecialchars($r->kode_barang) . '</code></td>
                <td><b>' . htmlspecialchars($r->nama_barang) . '</b></td>
                <td class="text-center">' . $r->jumlah . ' ' . htmlspecialchars($r->satuan) . '</td>
                <td class="text-right">Rp ' . number_format($r->harga_satuan ?? 0, 0, ',', '.') . '</td>
                <td class="text-right"><b>Rp ' . number_format($subtotal, 0, ',', '.') . '</b></td>
                <td>' . htmlspecialchars($r->penerima ?? '-') . '</td>
            </tr>';
        }

        if (count($riwayats) === 0) {
            $html .= '<tr><td colspan="8" class="text-center">Tidak ada data transaksi ' . strtolower($jenisFilter) . '.</td></tr>';
        }

        $html .= '
                </tbody>
            </table>
        </body>
        </html>';

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
        return $pdf->download('Laporan_' . $jenisFilter . '_Camperniq_' . $namaBulanPilihan . '_' . $tahunPilihan . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $bulanPilihan = $request->get('bulan', date('m'));
        $tahunPilihan = $request->get('tahun', date('Y'));
        $jenisFilter  = strtoupper($request->get('jenis', 'MASUK'));
        
        $namaBulans = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $namaBulanPilihan = $namaBulans[str_pad($bulanPilihan, 2, '0', STR_PAD_LEFT)] ?? 'September';

        $kolomJenis = $this->getKolomJenisTransaksi();

        $query = DB::table('transaksi_barang')
            ->join('master_barang', 'transaksi_barang.barang_id', '=', 'master_barang.id')
            ->whereMonth('transaksi_barang.created_at', $bulanPilihan)
            ->whereYear('transaksi_barang.created_at', $tahunPilihan);

        if (in_array($jenisFilter, ['MASUK', 'KELUAR'])) {
            $query->where('transaksi_barang.' . $kolomJenis, $jenisFilter);
        }

        $riwayats = $query->select('transaksi_barang.*', 'master_barang.kode_barang', 'master_barang.nama_barang', 'master_barang.satuan', 'master_barang.harga_satuan')
            ->orderBy('transaksi_barang.id', 'DESC')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $judulSheet = ($jenisFilter === 'MASUK') ? 'REKAPITULASI TRANSAKSI BARANG MASUK' : 'REKAPITULASI TRANSAKSI BARANG KELUAR';

        $sheet->setCellValue('A1', 'PT CAMPERNIQ SOLUSI INTERMEDIA');
        $sheet->setCellValue('A2', $judulSheet . ' - PERIODE ' . strtoupper($namaBulanPilihan) . ' ' . $tahunPilihan);
        
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['No', 'Tanggal', 'Kode Barang', 'Nama Barang', 'Jumlah (Qty)', 'Harga Satuan (Rp)', 'Subtotal (Rp)', 'Penerima / Teknisi', 'Keterangan'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $col++;
        }

        $sheet->getStyle('A4:I4')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle('A4:I4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($jenisFilter === 'MASUK' ? '198754' : 'DC3545');

        $row = 5;
        $totalNilai = 0;
        foreach ($riwayats as $index => $r) {
            $subtotal = ($r->jumlah ?? 0) * ($r->harga_satuan ?? 0);
            $totalNilai += $subtotal;

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, date('d M Y H:i', strtotime($r->created_at)));
            $sheet->setCellValue('C' . $row, $r->kode_barang);
            $sheet->setCellValue('D' . $row, $r->nama_barang);
            $sheet->setCellValue('E' . $row, $r->jumlah . ' ' . $r->satuan);
            $sheet->setCellValue('F' . $row, $r->harga_satuan ?? 0);
            $sheet->setCellValue('G' . $row, $subtotal);
            $sheet->setCellValue('H' . $row, $r->penerima ?? '-');
            $sheet->setCellValue('I' . $row, $r->keterangan ?? '-');

            $sheet->getStyle('F' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->setCellValue('A' . $row, 'TOTAL REKAPITULASI:');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValue('G' . $row, $totalNilai);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_' . $jenisFilter . '_Camperniq_' . $namaBulanPilihan . '_' . $tahunPilihan . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function exportWord(Request $request)
    {
        $bulanPilihan = $request->get('bulan', date('m'));
        $tahunPilihan = $request->get('tahun', date('Y'));
        $jenisFilter  = strtoupper($request->get('jenis', 'MASUK'));

        $namaBulans = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $namaBulanPilihan = $namaBulans[str_pad($bulanPilihan, 2, '0', STR_PAD_LEFT)] ?? 'September';

        $kolomJenis = $this->getKolomJenisTransaksi();

        $query = DB::table('transaksi_barang')
            ->join('master_barang', 'transaksi_barang.barang_id', '=', 'master_barang.id')
            ->whereMonth('transaksi_barang.created_at', $bulanPilihan)
            ->whereYear('transaksi_barang.created_at', $tahunPilihan);

        if (in_array($jenisFilter, ['MASUK', 'KELUAR'])) {
            $query->where('transaksi_barang.' . $kolomJenis, $jenisFilter);
        }

        $riwayats = $query->select('transaksi_barang.*', 'master_barang.kode_barang', 'master_barang.nama_barang', 'master_barang.satuan', 'master_barang.harga_satuan')
            ->orderBy('transaksi_barang.id', 'DESC')
            ->get();

        $totalNilai = 0;
        foreach ($riwayats as $r) {
            $totalNilai += ($r->jumlah * ($r->harga_satuan ?? 0));
        }

        $judulLaporan = ($jenisFilter === 'MASUK') ? 'LAPORAN REKAPITULASI BARANG MASUK' : 'LAPORAN REKAPITULASI BARANG KELUAR';

        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'orientation'  => 'landscape',
            'marginLeft'   => 720,
            'marginRight'  => 720,
            'marginTop'    => 720,
            'marginBottom' => 720
        ]);

        $section->addText('PT CAMPERNIQ SOLUSI INTERMEDIA', ['bold' => true, 'size' => 14, 'name' => 'Arial', 'color' => '1F4E78'], ['alignment' => Jc::CENTER]);
        $section->addText($judulLaporan, ['bold' => true, 'size' => 11, 'name' => 'Arial', 'color' => '333333'], ['alignment' => Jc::CENTER]);
        $section->addText('Periode Rekapan: ' . $namaBulanPilihan . ' ' . $tahunPilihan, ['italic' => true, 'size' => 9, 'name' => 'Arial', 'color' => '666666'], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        $rekapTable = $section->addTable([
            'alignment'   => Jc::CENTER,
            'cellMargin'  => 120,
            'borderSize'  => 0,
        ]);
        $rekapTable->addRow();
        $rekapCell = $rekapTable->addCell(15200, [
            'bgColor'           => 'EBF1F5',
            'borderLeftSize'    => 36,
            'borderLeftColor'   => ($jenisFilter === 'MASUK' ? '198754' : 'DC3545'),
            'borderTopSize'     => 0,
            'borderRightSize'   => 0,
            'borderBottomSize'  => 0,
        ]);
        
        $textRun = $rekapCell->addTextRun();
        $textRun->addText('TOTAL NILAI REKAPITULASI TRANSAKSI (' . $jenisFilter . '): ', ['bold' => true, 'size' => 10, 'name' => 'Arial', 'color' => '333333']);
        $textRun->addText('Rp ' . number_format($totalNilai, 0, ',', '.'), ['bold' => true, 'size' => 12, 'name' => 'Arial', 'color' => ($jenisFilter === 'MASUK' ? '198754' : 'DC3545')]);

        $section->addTextBreak(1);

        $table = $section->addTable([
            'borderSize'  => 4,
            'borderColor' => '777777',
            'cellMargin'  => 90,
            'alignment'   => Jc::CENTER
        ]);
        
        $table->addRow(380, ['tblHeader' => true]);
        $headers = [
            ['No', 600, Jc::CENTER],
            ['Tanggal', 1800, Jc::CENTER],
            ['Kode', 1600, Jc::CENTER],
            ['Nama Barang', 3600, Jc::LEFT],
            ['Jumlah', 1400, Jc::CENTER],
            ['Harga Satuan', 1800, Jc::RIGHT],
            ['Subtotal', 2000, Jc::RIGHT],
            ['Penerima / Teknisi', 2400, Jc::LEFT]
        ];

        $headerBg = ($jenisFilter === 'MASUK' ? '198754' : 'DC3545');

        foreach ($headers as $h) {
            $table->addCell($h[1], ['bgColor' => $headerBg])->addText($h[0], ['bold' => true, 'color' => 'FFFFFF', 'size' => 9, 'name' => 'Arial'], ['alignment' => $h[2]]);
        }

        foreach ($riwayats as $index => $r) {
            $subtotal = ($r->jumlah ?? 0) * ($r->harga_satuan ?? 0);

            $table->addRow();
            $table->addCell(600)->addText($index + 1, ['size' => 8.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $table->addCell(1800)->addText(date('d/m/Y H:i', strtotime($r->created_at)), ['size' => 8.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $table->addCell(1600)->addText($r->kode_barang, ['size' => 8.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $table->addCell(3600)->addText($r->nama_barang, ['size' => 8.5, 'bold' => true, 'name' => 'Arial']);
            $table->addCell(1400)->addText($r->jumlah . ' ' . $r->satuan, ['size' => 8.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $table->addCell(1800)->addText('Rp ' . number_format($r->harga_satuan ?? 0, 0, ',', '.'), ['size' => 8.5, 'name' => 'Arial'], ['alignment' => Jc::RIGHT]);
            $table->addCell(2000)->addText('Rp ' . number_format($subtotal, 0, ',', '.'), ['size' => 8.5, 'bold' => true, 'name' => 'Arial'], ['alignment' => Jc::RIGHT]);
            $table->addCell(2400)->addText($r->penerima ?? '-', ['size' => 8.5, 'name' => 'Arial']);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'Laporan_' . $jenisFilter . '_Camperniq_' . $namaBulanPilihan . '_' . $tahunPilihan . '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $objWriter->save('php://output');
        exit;
    }
}