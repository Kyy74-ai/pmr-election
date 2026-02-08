<?php
require_once '../config.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get statistics
$stats = [];

// Total voters
$query = "SELECT COUNT(*) as total FROM voters";
$result = $conn->query($query);
$stats['total_voters'] = $result->fetch_assoc()['total'];

// Voters who have voted
$query = "SELECT COUNT(*) as voted FROM voters WHERE has_voted = TRUE";
$result = $conn->query($query);
$stats['voted'] = $result->fetch_assoc()['voted'];

// Total candidates
$query = "SELECT COUNT(*) as total FROM candidates";
$result = $conn->query($query);
$stats['total_candidates'] = $result->fetch_assoc()['total'];

// Vote percentage
$stats['vote_percentage'] = $stats['total_voters'] > 0 ? 
    round(($stats['voted'] / $stats['total_voters']) * 100, 2) : 0;

// Recent votes
$query = "SELECT v.name as voter_name, c.name as candidate_name, votes.voted_at 
          FROM votes 
          JOIN voters v ON votes.voter_id = v.id 
          JOIN candidates c ON votes.candidate_id = c.id 
          ORDER BY votes.voted_at DESC LIMIT 10";
$recent_votes = $conn->query($query);

// Candidate votes
$query = "SELECT c.name, c.candidate_number, c.vote_count 
          FROM candidates c 
          ORDER BY c.vote_count DESC";
$candidate_votes = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/pmr.jpg" alt="PMR">
            <div class="admin-info">
                <h3><?php echo $_SESSION['admin_name']; ?></h3>
                <p class="admin-role"><?php echo $_SESSION['admin_role']; ?></p>
                <p class="admin-school"><?php echo SCHOOL_NAME; ?></p>
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <ul>
                <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="candidates.php"><i class="fas fa-users"></i> Data Calon</a></li>
                <li><a href="voters.php"><i class="fas fa-user-friends"></i> Data Pemilih</a></li>
                <li><a href="tokens.php"><i class="fas fa-key"></i> Manajemen Token</a></li>
                <li><a href="votes.php"><i class="fas fa-poll"></i> Hasil Pemilihan</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
                <li class="menu-divider"></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <p><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i:s'); ?></p>
            <p class="version">Admin Panel v2.0</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="breadcrumb">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
                <p>Overview sistem pemilihan ketua PMR</p>
            </div>
            <div class="top-bar-actions">
                <button class="btn-refresh" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <div class="user-menu">
                    <span class="username"><?php echo $_SESSION['admin_username']; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_voters']; ?></h3>
                    <p>Total Pemilih</p>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['voted']; ?></h3>
                    <p>Sudah Memilih</p>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_candidates']; ?></h3>
                    <p>Calon Ketua</p>
                </div>
            </div>
            
            <div class="stat-card stat-danger">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['vote_percentage']; ?>%</h3>
                    <p>Partisipasi</p>
                </div>
            </div>
        </div>
        
        <!-- Charts and Data -->
        <div class="dashboard-content">
            <!-- Vote Progress -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Progress Pemilihan</h3>
                </div>
                <div class="card-body">
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Partisipasi: <?php echo $stats['vote_percentage']; ?>%</span>
                            <span><?php echo $stats['voted']; ?> dari <?php echo $stats['total_voters']; ?> pemilih</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $stats['vote_percentage']; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="vote-stats">
                        <div class="vote-stat">
                            <span class="stat-label">Belum Memilih:</span>
                            <span class="stat-value"><?php echo $stats['total_voters'] - $stats['voted']; ?></span>
                        </div>
                        <div class="vote-stat">
                            <span class="stat-label">Token Terpakai:</span>
                            <span class="stat-value"><?php echo $stats['voted']; ?></span>
                        </div>
                        <div class="vote-stat">
                            <span class="stat-label">Token Tersedia:</span>
                            <span class="stat-value"><?php echo $stats['total_voters'] - $stats['voted']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Candidate Votes -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-poll"></i> Perolehan Suara</h3>
                </div>
                <div class="card-body">
                    <canvas id="votesChart"></canvas>
                    <div class="candidate-results">
                        <?php while($candidate = $candidate_votes->fetch_assoc()): ?>
                        <div class="candidate-result-item">
                            <div class="candidate-name">
                                <strong>No. <?php echo $candidate['candidate_number']; ?></strong>
                                <?php echo $candidate['name']; ?>
                            </div>
                            <div class="candidate-votes">
                                <span class="vote-count"><?php echo $candidate['vote_count']; ?> suara</span>
                                <?php if ($stats['voted'] > 0): ?>
                                <span class="vote-percentage">
                                    <?php echo round(($candidate['vote_count'] / $stats['voted']) * 100, 1); ?>%
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Votes -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Pemilihan Terakhir</h3>
                </div>
                <div class="card-body">
                    <div class="recent-votes-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Pemilih</th>
                                    <th>Memilih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($vote = $recent_votes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('H:i', strtotime($vote['voted_at'])); ?></td>
                                    <td><?php echo $vote['voter_name']; ?></td>
                                    <td><?php echo $vote['candidate_name']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions-grid">
                        <a href="candidates.php?action=add" class="quick-action">
                            <i class="fas fa-user-plus"></i>
                            <span>Tambah Calon</span>
                        </a>
                        <a href="voters.php?action=import" class="quick-action">
                            <i class="fas fa-file-import"></i>
                            <span>Import Pemilih</span>
                        </a>
                        <a href="tokens.php?action=generate" class="quick-action">
                            <i class="fas fa-key"></i>
                            <span>Generate Token</span>
                        </a>
                        <a href="votes.php?action=export" class="quick-action">
                            <i class="fas fa-file-export"></i>
                            <span>Export Hasil</span>
                        </a>
                        <a href="settings.php" class="quick-action">
                            <i class="fas fa-cog"></i>
                            <span>Pengaturan</span>
                        </a>
                        <a href="logout.php" class="quick-action">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="dashboard-footer">
            <p>© 2026 <?php echo SCHOOL_NAME; ?> - Sistem Pemilihan Ketua PMR</p>
            <p class="system-status">
                <i class="fas fa-circle text-success"></i> Sistem Berjalan Normal
                | Terakhir update: <?php echo date('H:i:s'); ?>
            </p>
        </footer>
    </div>
    
    <script>
        // Chart.js for votes visualization
        const ctx = document.getElementById('votesChart').getContext('2d');
        
        // Get candidate data from PHP
        <?php
        $candidate_votes->data_seek(0);
        $candidateNames = [];
        $candidateVotes = [];
        $candidateColors = ['#dc2626', '#2563eb', '#16a34a', '#d97706', '#7c3aed'];
        
        while($candidate = $candidate_votes->fetch_assoc()) {
            $candidateNames[] = 'No. ' . $candidate['candidate_number'];
            $candidateVotes[] = $candidate['vote_count'];
        }
        ?>
        
        const votesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($candidateNames); ?>,
                datasets: [{
                    label: 'Jumlah Suara',
                    data: <?php echo json_encode($candidateVotes); ?>,
                    backgroundColor: <?php echo json_encode(array_slice($candidateColors, 0, count($candidateNames))); ?>,
                    borderColor: '#1e293b',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        // Auto refresh every 30 seconds
        setInterval(() => {
            fetch('api/get_stats.php')
                .then(response => response.json())
                .then(data => {
                    // Update stats if needed
                    console.log('Stats updated:', data);
                })
                .catch(error => console.error('Error:', error));
        }, 30000);
        
        // User menu dropdown
        document.querySelector('.user-menu').addEventListener('click', function() {
            this.classList.toggle('active');
        });
    </script>
</body>
</html>
