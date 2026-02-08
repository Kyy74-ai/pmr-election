<?php
require_once '../config.php';

// Check if voter is logged in
if (!isset($_SESSION['voter_id'])) {
    header('Location: login.php');
    exit();
}

// Check if already voted
if ($_SESSION['has_voted']) {
    header('Location: already_voted.php');
    exit();
}

$error = '';
$success = '';

// Get candidates
$candidates_query = "SELECT * FROM candidates ORDER BY candidate_number";
$candidates_result = $conn->query($candidates_query);

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vote'])) {
    $token = sanitize($_POST['token']);
    $candidate_id = intval($_POST['candidate_id']);
    
    // Verify token
    if ($token !== $_SESSION['voter_token']) {
        $error = "Token tidak valid!";
    } else {
        // Check if token already used
        $token_check = "SELECT * FROM votes WHERE token_used = ?";
        $stmt = $conn->prepare($token_check);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Token ini sudah digunakan untuk memilih!";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert vote
                $vote_query = "INSERT INTO votes (voter_id, candidate_id, token_used, ip_address) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($vote_query);
                $stmt->bind_param("iiss", $_SESSION['voter_id'], $candidate_id, $token, $_SERVER['REMOTE_ADDR']);
                $stmt->execute();
                
                // Update voter status
                $update_voter = "UPDATE voters SET has_voted = TRUE WHERE id = ?";
                $stmt2 = $conn->prepare($update_voter);
                $stmt2->bind_param("i", $_SESSION['voter_id']);
                $stmt2->execute();
                
                // Update candidate vote count
                $update_candidate = "UPDATE candidates SET vote_count = vote_count + 1 WHERE id = ?";
                $stmt3 = $conn->prepare($update_candidate);
                $stmt3->bind_param("i", $candidate_id);
                $stmt3->execute();
                
                // Log token usage
                $token_log = "INSERT INTO token_logs (token, voter_id, action, ip_address) VALUES (?, ?, 'vote', ?)";
                $stmt4 = $conn->prepare($token_log);
                $stmt4->bind_param("sis", $token, $_SESSION['voter_id'], $_SERVER['REMOTE_ADDR']);
                $stmt4->execute();
                
                // Commit transaction
                $conn->commit();
                
                // Update session
                $_SESSION['has_voted'] = true;
                
                $success = "Selamat! Suara Anda telah tercatat.";
                
                // Redirect after 3 seconds
                header("refresh:3;url=vote_success.php");
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Terjadi kesalahan sistem: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemilihan - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/voting.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="voting-page">
    <!-- Header -->
    <header class="voting-header">
        <div class="container">
            <div class="voting-logo">
                <img src="../assets/pmr.jpg" alt="PMR">
                <div>
                    <h1>PEMILIHAN KETUA PMR</h1>
                    <p>SMKN 1 Pringgabaya - 2026</p>
                </div>
            </div>
            <div class="voter-info">
                <p><i class="fas fa-user"></i> <?php echo $_SESSION['voter_name']; ?></p>
                <p><i class="fas fa-key"></i> Token: <span class="token-display"><?php echo $_SESSION['voter_token']; ?></span></p>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <p>Anda akan diarahkan ke halaman konfirmasi...</p>
        </div>
        <?php endif; ?>
        
        <div class="voting-instructions">
            <h2><i class="fas fa-graduation-cap"></i> Panduan Pemilihan</h2>
            <ol>
                <li>Pilih salah satu calon ketua PMR di bawah ini</li>
                <li>Klik tombol "Lihat Detail" untuk melihat visi, misi, dan program kerja</li>
                <li>Masukkan token Anda untuk konfirmasi pemilihan</li>
                <li>Klik "SUBMIT VOTE" untuk mengirimkan pilihan Anda</li>
                <li><strong>PERHATIAN:</strong> Anda hanya bisa memilih SATU kali dan tidak dapat mengubah pilihan</li>
            </ol>
        </div>
        
        <div class="candidates-list">
            <?php while($candidate = $candidates_result->fetch_assoc()): ?>
            <div class="candidate-vote-card">
                <div class="candidate-header">
                    <div class="candidate-number-badge">No. <?php echo $candidate['candidate_number']; ?></div>
                    <div class="candidate-photo">
                        <img src="../assets/<?php echo $candidate['photo']; ?>" alt="<?php echo $candidate['name']; ?>">
                    </div>
                    <div class="candidate-basic-info">
                        <h3><?php echo $candidate['name']; ?></h3>
                        <p class="class-info"><?php echo $candidate['kelas']; ?> - <?php echo $candidate['jurusan']; ?></p>
                    </div>
                </div>
                
                <div class="candidate-details">
                    <div class="detail-section">
                        <h4><i class="fas fa-eye"></i> Visi</h4>
                        <p><?php echo nl2br($candidate['visi']); ?></p>
                    </div>
                    
                    <div class="detail-section">
                        <h4><i class="fas fa-bullseye"></i> Misi</h4>
                        <p><?php echo nl2br($candidate['misi']); ?></p>
                    </div>
                    
                    <?php if (!empty($candidate['program'])): ?>
                    <div class="detail-section">
                        <h4><i class="fas fa-tasks"></i> Program Unggulan</h4>
                        <p><?php echo nl2br($candidate['program']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="vote-action">
                    <form method="POST" action="" class="vote-form" onsubmit="return confirmVote()">
                        <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                        
                        <div class="token-input-group">
                            <label for="token_<?php echo $candidate['id']; ?>">
                                <i class="fas fa-key"></i> Masukkan Token Anda:
                            </label>
                            <input type="text" 
                                   name="token" 
                                   id="token_<?php echo $candidate['id']; ?>"
                                   placeholder="Token 12 digit" 
                                   maxlength="12"
                                   required
                                   pattern="[A-Za-z0-9]{12}"
                                   title="Token harus 12 karakter alfanumerik">
                            <button type="submit" name="vote" class="btn-vote-submit">
                                <i class="fas fa-vote-yea"></i> PILIH KANDIDAT INI
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div class="voting-warning">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="warning-content">
                <h3>PERINGATAN PENTING!</h3>
                <p>• Pemilihan hanya dapat dilakukan SATU KALI</p>
                <p>• Token hanya berlaku untuk SATU KALI pemilihan</p>
                <p>• Setelah submit, pilihan TIDAK DAPAT diubah</p>
                <p>• Pemilihan dilakukan dengan penuh tanggung jawab</p>
            </div>
        </div>
    </div>
    
    <script>
        function confirmVote() {
            const candidateName = document.querySelector('input[name="candidate_id"]:checked')?.closest('.candidate-vote-card')?.querySelector('h3')?.textContent;
            const token = document.querySelector('input[name="token"]').value;
            
            if (!candidateName) {
                alert('Silakan pilih kandidat terlebih dahulu!');
                return false;
            }
            
            if (!token || token.length !== 12) {
                alert('Token harus 12 karakter!');
                return false;
            }
            
            const confirmation = confirm(`Apakah Anda yakin memilih:\n\n${candidateName}\n\nDengan token: ${token}\n\nPilihan TIDAK DAPAT diubah setelah submit!`);
            
            if (!confirmation) {
                return false;
            }
            
            // Show loading
            const submitBtn = document.querySelector('.btn-vote-submit');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> MENGIRIM...';
                submitBtn.disabled = true;
            }
            
            return true;
        }
        
        // Token input validation
        document.querySelectorAll('input[name="token"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            });
        });
        
        // Auto-focus on token input when candidate is selected
        document.querySelectorAll('.vote-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const tokenInput = this.querySelector('input[name="token"]');
                if (!tokenInput.value) {
                    e.preventDefault();
                    tokenInput.focus();
                    alert('Silakan masukkan token terlebih dahulu!');
                }
            });
        });
    </script>
</body>
</html>
