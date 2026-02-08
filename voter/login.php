<?php
require_once '../config.php';

if (isset($_SESSION['voter_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    
    // Check if voter exists
    $check_query = "SELECT * FROM voters WHERE name = ? AND email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $voter = $result->fetch_assoc();
        
        if ($voter['is_used']) {
            $error = "Akun ini sudah digunakan untuk login sebelumnya.";
        } else {
            // Mark as used
            $update_query = "UPDATE voters SET is_used = TRUE, last_login = NOW() WHERE id = ?";
            $stmt2 = $conn->prepare($update_query);
            $stmt2->bind_param("i", $voter['id']);
            $stmt2->execute();
            
            // Set session
            $_SESSION['voter_id'] = $voter['id'];
            $_SESSION['voter_name'] = $voter['name'];
            $_SESSION['voter_email'] = $voter['email'];
            $_SESSION['voter_token'] = $voter['token'];
            $_SESSION['has_voted'] = $voter['has_voted'];
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_type, user_id, action, ip_address) VALUES ('voter', ?, 'login', ?)";
            $stmt3 = $conn->prepare($log_query);
            $stmt3->bind_param("is", $voter['id'], $_SERVER['REMOTE_ADDR']);
            $stmt3->execute();
            
            header('Location: dashboard.php');
            exit();
        }
    } else {
        $error = "Nama atau email tidak terdaftar sebagai pemilih.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Peserta - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <img src="../assets/pmr.jpg" alt="PMR Logo">
            <h1>Login Peserta Pemilihan</h1>
            <p>Masukkan data diri Anda untuk melanjutkan</p>
        </div>
        
        <div class="login-card">
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Masukkan nama sesuai daftar pemilih">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="email@example.com">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-login-submit">
                        <i class="fas fa-sign-in-alt"></i> LOGIN SEKARANG
                    </button>
                </div>
            </form>
            
            <div class="login-info">
                <h3><i class="fas fa-info-circle"></i> Informasi Penting:</h3>
                <ul>
                    <li><i class="fas fa-check"></i> Hanya anggota PMR terdaftar yang dapat login</li>
                    <li><i class="fas fa-check"></i> Satu akun hanya bisa login satu kali</li>
                    <li><i class="fas fa-check"></i> Setelah login, Anda akan mendapatkan token khusus</li>
                    <li><i class="fas fa-check"></i> Token digunakan untuk melakukan pemilihan</li>
                </ul>
            </div>
            
            <div class="login-footer">
                <a href="../index.php"><i class="fas fa-home"></i> Kembali ke Beranda</a>
                <a href="#" class="help-link"><i class="fas fa-question-circle"></i> Butuh Bantuan?</a>
            </div>
        </div>
    </div>
    
    <script>
        // Form validation
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (name.length < 3) {
                e.preventDefault();
                alert('Nama harus minimal 3 karakter');
                return false;
            }
            
            if (!email.includes('@') || !email.includes('.')) {
                e.preventDefault();
                alert('Email tidak valid');
                return false;
            }
        });
    </script>
</body>
</html>
