<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access - Paradise Hotel & Resort</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        font-family: 'Montserrat', sans-serif;
        background: linear-gradient(135deg, #2C3E50 0%, #34495E 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 2rem;
    }

    .access-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 3rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.2);
        text-align: center;
        max-width: 500px;
        width: 100%;
    }

    .logo {
        color: #2C3E50;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .logo i {
        color: #C9A961;
    }

    .subtitle {
        color: #666;
        font-size: 1.2rem;
        margin-bottom: 3rem;
    }

    .access-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1.2rem 2rem;
        border-radius: 15px;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-admin {
        background: linear-gradient(135deg, #C9A961 0%, #8B7355 100%);
        color: white;
        box-shadow: 0 10px 30px rgba(201, 169, 97, 0.3);
    }

    .btn-admin:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(201, 169, 97, 0.4);
    }

    .btn-website {
        background: linear-gradient(135deg, #2C3E50 0%, #34495E 100%);
        color: white;
        box-shadow: 0 10px 30px rgba(44, 62, 80, 0.3);
    }

    .btn-website:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(44, 62, 80, 0.4);
    }

    .credentials {
        background: #f8f9fa;
        border: 2px solid #C9A961;
        border-radius: 15px;
        padding: 1.5rem;
        margin: 2rem 0;
        text-align: left;
    }

    .credentials h3 {
        color: #2C3E50;
        margin-bottom: 1rem;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .credentials h3 i {
        color: #C9A961;
    }

    .credential-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .credential-item:last-child {
        border-bottom: none;
    }

    .credential-label {
        font-weight: 600;
        color: #2C3E50;
    }

    .credential-value {
        font-family: monospace;
        background: #e9ecef;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        color: #495057;
    }

    .auto-redirect {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
        padding: 1rem;
        border-radius: 10px;
        margin-top: 2rem;
        font-size: 0.9rem;
    }

    .countdown {
        font-weight: 700;
        color: #C9A961;
    }

    @media (max-width: 480px) {
        .access-container {
            padding: 2rem;
        }
        
        .logo {
            font-size: 2rem;
        }
        
        .btn {
            padding: 1rem 1.5rem;
            font-size: 1rem;
        }
    }
    </style>
</head>
<body>
    <div class="access-container">
        <div class="logo">
            <i class="fas fa-hotel"></i>
            Paradise Hotel & Resort
        </div>
        <div class="subtitle">Administration Access</div>

        <div class="credentials">
            <h3><i class="fas fa-key"></i> Admin Credentials</h3>
            <div class="credential-item">
                <span class="credential-label">Username:</span>
                <span class="credential-value">admin</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Password:</span>
                <span class="credential-value">admin123</span>
            </div>
        </div>

        <div class="access-buttons">
            <a href="admin/login.php" class="btn btn-admin">
                <i class="fas fa-shield-alt"></i>
                Go to Admin Login
            </a>
            
            <a href="index.php" class="btn btn-website">
                <i class="fas fa-globe"></i>
                View Main Website
            </a>
        </div>

        <div class="auto-redirect">
            <i class="fas fa-info-circle"></i>
            Auto-redirecting to admin login in <span class="countdown" id="countdown">10</span> seconds...
            <br><small>Click anywhere to cancel auto-redirect</small>
        </div>
    </div>

    <script>
    let countdown = 10;
    let redirectTimer;
    let cancelled = false;

    function updateCountdown() {
        document.getElementById('countdown').textContent = countdown;
        countdown--;
        
        if (countdown < 0 && !cancelled) {
            window.location.href = 'admin/login.php';
        }
    }

    // Start countdown
    redirectTimer = setInterval(updateCountdown, 1000);

    // Cancel redirect on any click
    document.addEventListener('click', function() {
        cancelled = true;
        clearInterval(redirectTimer);
        document.querySelector('.auto-redirect').style.display = 'none';
    });

    // Cancel redirect on any key press
    document.addEventListener('keydown', function() {
        cancelled = true;
        clearInterval(redirectTimer);
        document.querySelector('.auto-redirect').style.display = 'none';
    });
    </script>
</body>
</html>