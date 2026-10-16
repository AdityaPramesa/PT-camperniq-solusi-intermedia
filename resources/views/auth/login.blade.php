<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT Camperniq Solusi Intermedia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0d6efd;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
        }
    </style>
</head>
<body>

    <div class="card login-card shadow-lg border-0 p-3 p-md-4">
        <div class="card-body">
            
            <div class="text-center mb-4">
                <span class="fs-1">🏢</span>
                <h4 class="fw-bold text-primary mb-1">PT Camperniq Solusi Intermedia</h4>
                <p class="text-muted small mb-0">Sistem Inventory & Monitoring Stok</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show small" role="alert">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                    ❌ {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Masuk ke Sistem</button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">© {{ date('Y') }} PT Camperniq Solusi Intermedia</small>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>