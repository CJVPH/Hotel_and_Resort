<?php
require_once 'config/database.php';
require_once 'config/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'All fields are required!';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long!';
    } elseif (strlen($full_name) < 2) {
        $error = 'Full name must be at least 2 characters long!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } else {
        $conn = getDBConnection();
        
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
                // Redirect to login page with success message
                header('Location: login.php?registered=1');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Paradise Hotel & Resort</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2C3E50 0%, #34495E 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        .auth-container {
            width: 100%;
            max-width: 500px;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-header h1 {
            color: #2C3E50;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .auth-header h1 i {
            color: #C9A961;
            margin-right: 0.5rem;
        }
        
        .auth-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            color: #2C3E50;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-group label i {
            color: #C9A961;
            margin-right: 0.5rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #C9A961;
            box-shadow: 0 0 0 3px rgba(201, 169, 97, 0.1);
        }
        
        .form-group input.error {
            border-color: #dc3545;
        }
        
        .form-group .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #C9A961 0%, #8B7355 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(201, 169, 97, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201, 169, 97, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .auth-footer {
            text-align: center;
            color: #666;
        }
        
        .auth-footer p {
            margin: 0.5rem 0;
        }
        
        .auth-footer a {
            color: #C9A961;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .auth-footer a:hover {
            color: #8B7355;
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: 2rem;
            }
            
            .auth-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fas fa-user-plus"></i> Register</h1>
                <p>Create your account to book rooms</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="registerForm">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="full_name" placeholder="Enter your full name" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    <div class="error-message">Please enter your full name</div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user-circle"></i> Username</label>
                    <input type="text" name="username" placeholder="Choose a username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <div class="error-message">Username must be at least 3 characters</div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="error-message">Please enter a valid email address</div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="Create a password (min. 6 characters)" required>
                    <div class="password-strength" id="passwordStrength"></div>
                    <div class="error-message">Password must be at least 6 characters</div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                    <div class="error-message">Passwords do not match</div>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p><a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Form validation
        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        
        // Password strength checker
        const passwordInput = document.querySelector('input[name="password"]');
        const strengthIndicator = document.getElementById('passwordStrength');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let message = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (password.length === 0) {
                message = '';
            } else if (strength < 2) {
                message = '<span class="strength-weak">Weak password</span>';
            } else if (strength < 4) {
                message = '<span class="strength-medium">Medium password</span>';
            } else {
                message = '<span class="strength-strong">Strong password</span>';
            }
            
            strengthIndicator.innerHTML = message;
        });
        
        // Real-time validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(msg => {
                msg.style.display = 'none';
            });
            document.querySelectorAll('input').forEach(input => {
                input.classList.remove('error');
            });
            
            // Validate full name
            const fullName = document.querySelector('input[name="full_name"]');
            if (fullName.value.trim().length < 2) {
                showError(fullName, 'Please enter your full name');
                isValid = false;
            }
            
            // Validate username
            const username = document.querySelector('input[name="username"]');
            if (username.value.trim().length < 3) {
                showError(username, 'Username must be at least 3 characters');
                isValid = false;
            }
            
            // Validate email
            const email = document.querySelector('input[name="email"]');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                showError(email, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Validate password
            const password = document.querySelector('input[name="password"]');
            if (password.value.length < 6) {
                showError(password, 'Password must be at least 6 characters');
                isValid = false;
            }
            
            // Validate password confirmation
            const confirmPassword = document.querySelector('input[name="confirm_password"]');
            if (password.value !== confirmPassword.value) {
                showError(confirmPassword, 'Passwords do not match');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        function showError(input, message) {
            input.classList.add('error');
            const errorMsg = input.parentNode.querySelector('.error-message');
            errorMsg.textContent = message;
            errorMsg.style.display = 'block';
        }
    </script>
</body>
</html>