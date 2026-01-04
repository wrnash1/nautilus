<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Nautilus Dive Shop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            /* Ocean-themed gradient background matching login */
            background: linear-gradient(135deg, #0066cc 0%, #004d99 50%, #003366 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #fff;
            text-align: center;
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
        
        .error-container {
            max-width: 600px;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 0;
            text-shadow: 0 4px 10px rgba(0,0,0,0.3);
            background: linear-gradient(to bottom, #fff, #a2d2ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .error-message {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        .icon-container {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: float 4s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .btn-home {
            padding: 0.8rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
            background-color: #fff;
            color: #004d99;
            border: none;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
            background-color: #f0f8ff;
            color: #003366;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon-container">
            <i class="bi bi-compass"></i>
        </div>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        <p class="error-message">
            Oops! It looks like you've drifted into uncharted waters.<br>
            The page you're looking for doesn't exist or has been moved.
        </p>
        <a href="/" class="btn-home">
            <i class="bi bi-house-door-fill"></i> Return Home
        </a>
    </div>
</body>
</html>
