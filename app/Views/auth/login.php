<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nautilus Dive Shop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            /* Ocean-themed gradient background */
            background: linear-gradient(135deg, #0066cc 0%, #004d99 50%, #003366 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated wave effect */
        body::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" fill="%23ffffff"/><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" fill="%23ffffff"/><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="%23ffffff"/></svg>') repeat-x;
            background-size: 1200px 120px;
            opacity: 0.1;
            animation: wave 10s linear infinite;
        }
        
        @keyframes wave {
            0% { background-position-x: 0; }
            100% { background-position-x: 1200px; }
        }
        
        .login-card {
            max-width: 450px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        
        .logo-area {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-area i {
            font-size: 4rem;
            color: #fff;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .logo-area h1 {
            color: #fff;
            font-size: 2.5rem;
            margin-top: 1rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .logo-area p {
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        .card {
            border: none;
            border-radius: 1rem;
        }
        
        .sso-buttons {
            display: grid;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        .btn-sso {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            background: white;
            color: #495057;
            font-weight: 500;
            transition: all 0.2s;
            border-radius: 0.5rem;
        }
        
        .btn-sso:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn-sso i {
            font-size: 1.25rem;
        }
        
        .btn-google:hover { border-color: #4285f4; color: #4285f4; }
        .btn-microsoft:hover { border-color: #00a4ef; color: #00a4ef; }
        .btn-github:hover { border-color: #333; color: #333; }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        
        .divider span {
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-area">
            <i class="bi bi-water"></i>
            <h1>Nautilus</h1>
            <p class="text-white">Dive Shop Management System</p>
        </div>
        
        <div class="card shadow-lg">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Sign In</h2>
                
                <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['flash_success']) ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>
                
                <form method="POST" action="<?= url('/store/login') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <?php if (isset($_GET['redirect']) || isset($_POST['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? $_POST['redirect']) ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="admin@nautilus.com" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </div>
                </form>
                
                <?php
                // Check if SSO is enabled
                /* SSO Disabled by Request
                $ssoEnabled = $_ENV['SSO_ENABLED'] ?? 'true';
                if ($ssoEnabled === 'true' || $ssoEnabled === true):
                ?>
                
                <div class="divider">
                    <span>OR CONTINUE WITH</span>
                </div>
                
                <div class="sso-buttons">
                    <a href="/store/auth/sso/google" class="btn btn-sso btn-google">
                        <i class="bi bi-google"></i>
                        <span>Sign in with Google</span>
                    </a>
                    
                    <a href="/store/auth/sso/microsoft" class="btn btn-sso btn-microsoft">
                        <i class="bi bi-microsoft"></i>
                        <span>Sign in with Microsoft</span>
                    </a>
                    
                    <a href="/store/auth/sso/github" class="btn btn-sso btn-github">
                        <i class="bi bi-github"></i>
                        <span>Sign in with GitHub</span>
                    </a>
                </div>
                
                <?php endif; */ ?>
                
                <hr class="my-4">
                
                <?php if (($_ENV['APP_ENV'] ?? 'local') !== 'production'): ?>
                <div class="text-center text-muted">
                    <small>
                        <strong>Default Admin Credentials:</strong><br>
                        Email: admin@nautilus.local<br>
                        Password: password<br>
                        <em class="text-warning">⚠️ Change password after first login!</em>
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <small class="text-white">
                &copy; 2025 Nautilus Dive Shop. All rights reserved.
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
