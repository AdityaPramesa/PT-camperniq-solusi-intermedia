<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI-MONI PT Camperniq Solusi Intermedia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card login-card border-0 p-4 bg-white">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary mb-1">🏢 SI-MONI</h3>
                    <p class="text-muted small">PT Camperniq Solusi Intermedia</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        ⚠️ {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show small" role="alert">
                        ✅ {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Masuk ke Sistem</button>
                </form>

                <div class="text-center mt-4">
                    <span class="text-muted small">© {{ date('Y') }} PT Camperniq Solusi Intermedia</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>