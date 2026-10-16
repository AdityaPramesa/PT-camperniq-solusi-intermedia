<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - PT Camperniq Solusi Intermedia</title>
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
                <a href="{{ url('/') }}" class="btn btn-light btn-sm fw-bold text-dark">🏠 Dashboard</a>
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

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
            <h4 class="fw-bold mb-0">👥 Manajemen Pengguna Aplikasi</h4>
            <a href="{{ url('/') }}" class="btn btn-secondary btn-sm fw-bold align-self-start align-self-sm-auto">← Kembali ke Dashboard</a>
        </div>

        <div class="row g-4">
            <!-- Form Tambah User (Responsif HP: Full Width 12 Kolom di HP, 4 Kolom di Desktop) -->
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold py-3">
                        ➕ Tambah Akun Karyawan
                    </div>
                    <div class="card-body">
                        <form action="{{ url('/kelola-user/store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Lengkap Karyawan</label>
                                <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Budi Santoso" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Contoh: budi" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Role / Hak Akses</label>
                                <select name="role" class="form-select" required>
                                    <option value="TEKNISI">TEKNISI (Hanya Lihat Stok Gudang)</option>
                                    <option value="NOC">NOC (Hanya Lihat Stok Gudang)</option>
                                    <option value="ADMIN">ADMIN (Akses Penuh System)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">💾 Buat Akun Karyawan</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar User (Responsif HP: Dapat Di-scroll Samping) -->
            <div class="col-12 col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                        <span>👥 Daftar Pengguna Terdaftar</span>
                        <span class="badge bg-primary">Total: {{ count($users ?? []) }} User</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Karyawan</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users ?? [] as $index => $u)
                                    @php
                                        $rNama = $u->nama_lengkap ?? $u->nama ?? $u->name ?? $u->username;
                                        $rUser = $u->username ?? $u->email;
                                        $rRole = strtoupper($u->role ?? $u->hak_akses ?? $u->level ?? 'TEKNISI');
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-bold">{{ $rNama }}</td>
                                        <td><code class="text-danger">{{ $rUser }}</code></td>
                                        <td>
                                            @if($rRole === 'ADMIN')
                                                <span class="badge bg-danger">ADMIN</span>
                                            @elseif($rRole === 'NOC')
                                                <span class="badge bg-warning text-dark">NOC</span>
                                            @else
                                                <span class="badge bg-info text-dark">TEKNISI</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ url('/kelola-user/delete/' . $u->id) }}" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini?')">🗑️ Hapus</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-4">Belum ada pengguna terdaftar.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>