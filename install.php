<?php
// install.php - Installation Wizard
session_start();

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

if ($step == 1 && isset($_POST['proceed'])) {
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4.0') < 0) {
        $error = "PHP 7.4 atau lebih tinggi diperlukan. Versi saat ini: " . PHP_VERSION;
    } else {
        header('Location: install.php?step=2');
        exit();
    }
}

if ($step == 2 && isset($_POST['setup_db'])) {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];
    
    try {
        // Test connection
        $conn = new mysqli($db_host, $db_user, $db_pass);
        
        if ($conn->connect_error) {
            throw new Exception("Koneksi database gagal: " . $conn->connect_error);
        }
        
        // Create database if not exists
        $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->select_db($db_name);
        
        // Read and execute SQL file
        $sql = file_get_contents('database.sql');
        $queries = explode(';', $sql);
        
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $conn->query($query);
            }
        }
        
        // Create config file
        $config_content = "<?php
// Database Configuration
define('DB_HOST', '$db_host');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('DB_NAME', '$db_name');

// Site Configuration
define('SITE_NAME', 'Pemilihan Ketua PMR Wira');
define('SITE_URL', '" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/');
define('SCHOOL_NAME', 'SMKN 1 Pringgabaya');

// Security
define('SECRET_KEY', '" . bin2hex(random_bytes(32)) . "');
define('TOKEN_LENGTH', 12);

session_start();

// Create connection
try {
    \$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    \$conn->set_charset('utf8mb4');
    
    if (\$conn->connect_error) {
        throw new Exception('Connection failed: ' . \$conn->connect_error);
    }
} catch (Exception \$e) {
    die('Database Error: ' . \$e->getMessage());
}

// Functions
function generateToken(\$length = TOKEN_LENGTH) {
    \$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    \$token = '';
    for (\$i = 0; \$i < \$length; \$i++) {
        \$token .= \$characters[rand(0, strlen(\$characters) - 1)];
    }
    return \$token;
}

function sanitize(\$data) {
    global \$conn;
    return htmlspecialchars(stripslashes(trim(\$conn->real_escape_string(\$data))));
}

function isLoggedIn() {
    return isset(\$_SESSION['voter_id']) || isset(\$_SESSION['admin_id']);
}
?>";
        
        file_put_contents('config.php', $config_content);
        
        $success = "Database berhasil dibuat dan dikonfigurasi!";
        header('Location: install.php?step=3');
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($step == 3 && isset($_POST['create_admin'])) {
    $admin_user = $_POST['admin_user'];
    $admin_pass = $_POST['admin_pass'];
    $admin_name = $_POST['admin_name'];
    
    // Hash password
    $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
    
    require_once 'config.php';
    
    // Update admin credentials
    $stmt = $conn->prepare("UPDATE admins SET username = ?, password = ?, fullname = ? WHERE role = 'superadmin'");
    $stmt->bind_param("sss", $admin_user, $hashed_pass, $admin_name);
    
    if ($stmt->execute()) {
        $success = "Admin berhasil dibuat!";
        header('Location: install.php?step=4');
        exit();
    } else {
        $error = "Gagal membuat admin: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Wizard - PMR Election System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .install-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }
        
        .install-header {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .install-steps {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background: var(--gray-300);
            color: var(--gray-700);
        }
        
        .step.active {
            background: var(--primary-red);
            color: white;
        }
        
        .step.completed {
            background: var(--accent-green);
            color: white;
        }
        
        .install-body {
            padding: 40px;
        }
        
        .install-form {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--gray-700);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 1rem;
        }
        
        .btn-install {
            background: var(--primary-red);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        
        .install-info {
            background: var(--primary-light);
            padding: 20px;
            border-radius: var(--radius);
            margin: 30px 0;
        }
        
        .alert {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #0a0;
            border: 1px solid #cfc;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1><i class="fas fa-download"></i> Installation Wizard</h1>
            <p>PMR Election System - <?php echo SCHOOL_NAME; ?></p>
            <p class="version">Version 2.0.2026</p>
        </div>
        
        <div class="install-steps">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; echo $step > 1 ? ' completed' : ''; ?>">1</div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; echo $step > 2 ? ' completed' : ''; ?>">2</div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; echo $step > 3 ? ' completed' : ''; ?>">3</div>
            <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4</div>
        </div>
        
        <div class="install-body">
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
            <div class="install-info">
                <h3><i class="fas fa-info-circle"></i> Persyaratan Sistem:</h3>
                <ul>
                    <li>PHP 7.4 atau lebih tinggi</li>
                    <li>MySQL 5.7 atau lebih tinggi</li>
                    <li>Apache/Nginx dengan mod_rewrite</li>
                    <li>Akses write permission ke folder</li>
                    <li>Ekstensi PHP: mysqli, pdo_mysql, json, session</li>
                </ul>
            </div>
            
            <div class="system-check">
                <h3>Pemeriksaan Sistem:</h3>
                <table style="width: 100%;">
                    <tr>
                        <td>PHP Version:</td>
                        <td><strong><?php echo PHP_VERSION; ?></strong></td>
                        <td><?php echo version_compare(PHP_VERSION, '7.4.0') >= 0 ? '✅' : '❌'; ?></td>
                    </tr>
                    <tr>
                        <td>MySQLi Extension:</td>
                        <td><?php echo extension_loaded('mysqli') ? 'Tersedia' : 'Tidak Tersedia'; ?></td>
                        <td><?php echo extension_loaded('mysqli') ? '✅' : '❌'; ?></td>
                    </tr>
                    <tr>
                        <td>Write Permission:</td>
                        <td><?php echo is_writable('.') ? 'OK' : 'Tidak OK'; ?></td>
                        <td><?php echo is_writable('.') ? '✅' : '❌'; ?></td>
                    </tr>
                </table>
            </div>
            
            <form method="POST" class="install-form">
                <input type="hidden" name="proceed" value="1">
                <button type="submit" class="btn-install">
                    <i class="fas fa-arrow-right"></i> LANJUT KE STEP 2
                </button>
            </form>
            
            <?php elseif ($step == 2): ?>
            <h2>Step 2: Konfigurasi Database</h2>
            <form method="POST" class="install-form">
                <div class="form-group">
                    <label for="db_host">Database Host:</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Database Username:</label>
                    <input type="text" id="db_user" name="db_user" value="root" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Database Password:</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
                
                <div class="form-group">
                    <label for="db_name">Database Name:</label>
                    <input type="text" id="db_name" name="db_name" value="pmr_election_2026" required>
                </div>
                
                <button type="submit" name="setup_db" class="btn-install">
                    <i class="fas fa-database"></i> SETUP DATABASE
                </button>
            </form>
            
            <?php elseif ($step == 3): ?>
            <h2>Step 3: Buat Admin Superuser</h2>
            <form method="POST" class="install-form">
                <div class="form-group">
                    <label for="admin_name">Nama Lengkap Admin:</label>
                    <input type="text" id="admin_name" name="admin_name" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_user">Username Admin:</label>
                    <input type="text" id="admin_user" name="admin_user" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_pass">Password Admin:</label>
                    <input type="password" id="admin_pass" name="admin_pass" required minlength="8">
                </div>
                
                <button type="submit" name="create_admin" class="btn-install">
                    <i class="fas fa-user-shield"></i> BUAT ADMIN
                </button>
            </form>
            
            <?php elseif ($step == 4): ?>
            <div class="install-success">
                <div style="text-align: center; margin: 50px 0;">
                    <i class="fas fa-check-circle" style="font-size: 5rem; color: var(--accent-green);"></i>
                    <h2>Installation Complete! 🎉</h2>
                    <p>Sistem pemilihan PMR berhasil diinstal.</p>
                </div>
                
                <div class="install-info">
                    <h3><i class="fas fa-exclamation-triangle"></i> Tindakan Penting:</h3>
                    <ol>
                        <li>Hapus file <strong>install.php</strong> dari server</li>
                        <li>Ganti password default admin segera</li>
                        <li>Import data pemilih melalui panel admin</li>
                        <li>Generate token untuk semua pemilih</li>
                        <li>Test sistem sebelum digunakan secara live</li>
                    </ol>
                </div>
                
                <div class="quick-links">
                    <a href="index.php" class="btn-primary" style="display: block; text-align: center; margin-bottom: 10px;">
                        <i class="fas fa-home"></i> Ke Beranda
                    </a>
                    <a href="admin/login.php" class="btn-admin" style="display: block; text-align: center;">
                        <i class="fas fa-lock"></i> Ke Panel Admin
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Password strength check
        document.getElementById('admin_pass')?.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            const indicator = document.getElementById('password-strength') || 
                             document.createElement('div');
            indicator.id = 'password-strength';
            indicator.style.marginTop = '5px';
            indicator.style.fontSize = '0.9rem';
            
            if (this.value.length === 0) {
                indicator.innerHTML = '';
            } else if (strength < 2) {
                indicator.innerHTML = '❌ Password lemah';
                indicator.style.color = '#dc2626';
            } else if (strength < 3) {
                indicator.innerHTML = '⚠️ Password sedang';
                indicator.style.color = '#d97706';
            } else {
                indicator.innerHTML = '✅ Password kuat';
                indicator.style.color = '#16a34a';
            }
            
            if (!this.parentNode.querySelector('#password-strength')) {
                this.parentNode.appendChild(indicator);
            }
        });
        
        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
        }
    </script>
</body>
</html>
