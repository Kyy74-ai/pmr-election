<?php
require_once '../config.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    // Check admin credentials
    $query = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // Verify password (using password_verify for hashed passwords)
        if (password_verify($password, $admin['password'])) {
            // Update last login
            $update_query = "UPDATE admins SET last_login = NOW() WHERE id = ?";
            $stmt2 = $conn->prepare($update_query);
            $stmt2->bind_param("i", $admin['id']);
            $stmt2->execute();
            
            // Set session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_name'] = $admin['fullname'];
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_type, user_id, action, ip_address) VALUES ('admin', ?, 'login', ?)";
            $stmt3 = $conn->prepare($log_query);
            $stmt3->bind_param("is", $admin['id'], $_SERVER['REMOTE_ADDR']);
            $stmt3->execute();
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .admin-login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .admin-login-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .admin-login-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid white;
            margin-bottom: 15px;
        }
        
        .admin-login-form {
            padding: 30px;
        }
        
        .admin-role-badge {
            display: inline-block;
            background: #fbbf24;
            color: #78350f;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body class="admin-login">
    <div class="admin-login-card">
        <div class="admin-login-header">
            <img src="../assets/pmr.jpg" alt="PMR Admin">
            <h1><i class="fas fa-lock"></i> ADMIN PANEL</h1>
            <p><?php echo SITE_NAME; ?></p>
            <p><?php echo SCHOOL_NAME; ?></p>
            <div class="admin-role-badge">
                <i class="fas fa-user-shield"></i> Pembina & Penanggung Jawab
            </div>
        </div>
        
        <div class="admin-login-form">
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="adminLoginForm">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user-cog"></i> Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required
                           placeholder="Masukkan username admin"
                           autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-key"></i> Password</label>
                    <div class="password-input">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               placeholder="Password admin"
                               autocomplete="off">
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-admin-login">
                        <i class="fas fa-sign-in-alt"></i> LOGIN SEBAGAI ADMIN
                    </button>
                </div>
            </form>
            
            <div class="admin-login-info">
                <h3><i class="fas fa-shield-alt"></i> Keamanan Sistem:</h3>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Akses terbatas untuk pembina PMR</li>
                    <li><i class="fas fa-check-circle"></i> Semua aktivitas tercatat (log)</li>
                    <li><i class="fas fa-check-circle"></i> Deteksi IP address</li>
                    <li><i class="fas fa-check-circle"></i> Session timeout otomatis</li>
                </ul>
                
                <div class="emergency-contact">
                    <h4><i class="fas fa-phone"></i> Kontak Darurat:</h4>
                    <p>Super Admin: 0812-3456-7890</p>
                </div>
            </div>
            
            <div class="admin-login-footer">
                <a href="../index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
                <span class="version">v2.0.2026</span>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.classList.remove('fa-eye');
                toggleBtn.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleBtn.classList.remove('fa-eye-slash');
                toggleBtn.classList.add('fa-eye');
            }
        }
        
        // Auto logout after 30 minutes
        setTimeout(function() {
            window.location.href = '../index.php';
        }, 30 * 60 * 1000);
        
        // Form validation
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (username.length < 3) {
                e.preventDefault();
                alert('Username minimal 3 karakter');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter');
                return false;
            }
            
            // Show loading
            const submitBtn = document.querySelector('.btn-admin-login');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> MENGOTENTIKASI...';
            submitBtn.disabled = true;
            
            return true;
        });
    </script>
</body>
</html>
