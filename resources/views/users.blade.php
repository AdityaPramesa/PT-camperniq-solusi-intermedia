<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - SI-MONI</title>
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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">👥 Manajemen Pengguna Aplikasi</h4>
            <a href="{{ url('/') }}" class="btn btn-secondary btn-sm fw-bold">← Kembali ke Dashboard</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ⚠️ {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Form Tambah User -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">
                        ➕ Tambah Akun Karyawan
                    </div>
                    <div class="card-body">
                        <form action="{{ url('/kelola-user/store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nama Lengkap Karyawan</label>
                                <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Contoh: budi" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small">Role / Hak Akses</label>
                                <select name="role" class="form-select" required>
                                    <option value="TEKNISI">TEKNISI (Akses Transaksi & Monitor)</option>
                                    <option value="ADMIN">ADMIN (Akses Penuh System)</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold">💾 Buat Akun Karyawan</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar User -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                        <span>👥 Daftar Pengguna Terdaftar</span>
                        <span class="badge bg-primary">Total: {{ count($users) }} User</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Karyawan</th>
                                        <th>Username</th>
                                        <th class="text-center">Role</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $index => $u)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-bold">{{ $u->nama_lengkap ?? $u->nama ?? $u->name ?? '-' }}</td>
                                        <td><code>{{ $u->username }}</code></td>
                                        <td class="text-center">
                                            @if(($u->role ?? '') === 'ADMIN')
                                                <span class="badge bg-danger">ADMIN</span>
                                            @else
                                                <span class="badge bg-info text-dark">TEKNISI</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($u->id != session('user_id'))
                                                <a href="{{ url('/kelola-user/delete/' . $u->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')" title="Hapus Akun">🗑️ Hapus</a>
                                            @else
                                                <span class="badge bg-secondary">Akun Saya</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
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