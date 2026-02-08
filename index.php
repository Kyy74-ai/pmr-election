<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo SITE_NAME; ?> - <?php echo SCHOOL_NAME; ?></title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <!-- Header -->
  <header class="header">
    <div class="container">
      <div class="logo-container">
        <img src="assets/pmr.jpg" alt="Logo PMR" class="logo">
        <div class="logo-text">
          <h1><?php echo SITE_NAME; ?></h1>
          <p><?php echo SCHOOL_NAME; ?></p>
          <p class="year">Tahun 2026</p>
        </div>
      </div>
      <nav class="main-nav">
        <ul>
          <li><a href="#home" class="active"><i class="fas fa-home"></i> Beranda</a></li>
          <li><a href="#about"><i class="fas fa-info-circle"></i> Tentang PMR</a></li>
          <li><a href="#candidates"><i class="fas fa-users"></i> Calon Ketua</a></li>
          <li><a href="#contact"><i class="fas fa-phone"></i> Kontak</a></li>
          <li><a href="voter/login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login Peserta</a></li>
          <li><a href="admin/login.php" class="btn-admin"><i class="fas fa-lock"></i> Login Admin</a></li>
        </ul>
      </nav>
      <div class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section id="home" class="hero">
    <div class="container">
      <div class="hero-content">
        <h2>PEMILIHAN KETUA PMR WIRA</h2>
        <h3>PERIODE 2026-2027</h3>
        <p class="hero-subtitle">"Memilih Pemimpin, Mewujudkan Perubahan"</p>
        <div class="hero-buttons">
          <a href="voter/login.php" class="btn-primary">
            <i class="fas fa-vote-yea"></i> IKUT MEMILIH
          </a>
          <a href="#candidates" class="btn-secondary">
            <i class="fas fa-user-tie"></i> LIHAT CALON
          </a>
        </div>
        <div class="countdown-container">
          <h4>Waktu Pemilihan:</h4>
          <div id="countdown" class="countdown">
            <div class="countdown-item">
              <span id="days">00</span>
              <p>Hari</p>
            </div>
            <div class="countdown-item">
              <span id="hours">00</span>
              <p>Jam</p>
            </div>
            <div class="countdown-item">
              <span id="minutes">00</span>
              <p>Menit</p>
            </div>
            <div class="countdown-item">
              <span id="seconds">00</span>
              <p>Detik</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="about">
    <div class="container">
      <h2 class="section-title">Tentang PMR Wira</h2>
      <div class="about-grid">
        <div class="about-card">
          <i class="fas fa-heartbeat"></i>
          <h3>Visi PMR</h3>
          <p>Menjadi organisasi kemanusiaan yang unggul dalam bidang kesehatan dan mampu memberikan kontribusi positif
            bagi sekolah dan masyarakat.</p>
        </div>
        <div class="about-card">
          <i class="fas fa-hands-helping"></i>
          <h3>Misi PMR</h3>
          <p>Membentuk generasi muda yang peduli, terampil dalam pertolongan pertama, dan berkarakter kemanusiaan yang
            kuat.</p>
        </div>
        <div class="about-card">
          <i class="fas fa-award"></i>
          <h3>Nilai Inti</h3>
          <p>Kemanusiaan, Kesukarelaan, Kenetralan, Kemandirian, Kesatuan, Kesemestaan.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Candidates Preview -->
  <section id="candidates" class="candidates-preview">
    <div class="container">
      <h2 class="section-title">Calon Ketua PMR</h2>
      <p class="section-subtitle">Kenali visi, misi, dan program kerja para calon</p>

      <div class="candidates-grid">
        <?php
                $candidates_query = "SELECT * FROM candidates ORDER BY candidate_number";
                $result = $conn->query($candidates_query);
                
                if ($result->num_rows > 0) {
                    while($candidate = $result->fetch_assoc()) {
                ?>
        <div class="candidate-card">
          <div class="candidate-number">No. <?php echo $candidate['candidate_number']; ?></div>
          <div class="candidate-image">
            <img src="assets/<?php echo $candidate['photo']; ?>" alt="<?php echo $candidate['name']; ?>">
          </div>
          <div class="candidate-info">
            <h3><?php echo $candidate['name']; ?></h3>
            <p class="candidate-class"><?php echo $candidate['kelas']; ?></p>
            <p class="candidate-major"><?php echo $candidate['jurusan']; ?></p>
            <div class="candidate-preview">
              <h4>Visi Singkat:</h4>
              <p><?php echo substr($candidate['visi'], 0, 150) . '...'; ?></p>
            </div>
            <a href="voter/login.php" class="btn-vote">Lihat Detail & Vote</a>
          </div>
        </div>
        <?php
                    }
                }
                ?>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="contact">
    <div class="container">
      <h2 class="section-title">Kontak & Informasi</h2>
      <div class="contact-grid">
        <div class="contact-info">
          <h3><i class="fas fa-school"></i> SMKN 1 Pringgabaya</h3>
          <p><i class="fas fa-map-marker-alt"></i> Jl. Pendidikan No. 123, Pringgabaya</p>
          <p><i class="fas fa-phone"></i> (0370) 123456</p>
          <p><i class="fas fa-envelope"></i> pmr@smkn1pringgabaya.sch.id</p>

          <h3 style="margin-top: 30px;"><i class="fas fa-clock"></i> Waktu Pemilihan</h3>
          <p><strong>Tanggal:</strong> 15-20 Maret 2026</p>
          <p><strong>Waktu:</strong> 08:00 - 15:00 WITA</p>
          <p><strong>Tempat:</strong> Laboratorium Komputer & Ruang PMR</p>
        </div>

        <div class="quick-links">
          <h3><i class="fas fa-link"></i> Tautan Cepat</h3>
          <ul>
            <li><a href="voter/login.php"><i class="fas fa-sign-in-alt"></i> Login Peserta</a></li>
            <li><a href="admin/login.php"><i class="fas fa-lock"></i> Login Admin</a></li>
            <li><a href="#"><i class="fas fa-file-pdf"></i> Panduan Pemilihan</a></li>
            <li><a href="#"><i class="fas fa-question-circle"></i> FAQ</a></li>
            <li><a href="#"><i class="fas fa-exclamation-triangle"></i> Syarat & Ketentuan</a></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-logo">
          <img src="assets/pmr.jpg" alt="PMR Logo">
          <div>
            <h3>PMR Wira SMKN 1 Pringgabaya</h3>
            <p>Palang Merah Remaja - Unit Wira</p>
          </div>
        </div>
        <div class="footer-links">
          <p>&copy; 2026 - Sistem Pemilihan Ketua PMR</p>
          <p>Developed with ❤️ for PMR Community</p>
          <p class="footer-note">Hak suara hanya diberikan kepada anggota PMR terdaftar</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- JavaScript -->
  <script src="js/script.js"></script>
  <script>
  // Countdown Timer
  const electionDate = new Date('March 15, 2026 08:00:00').getTime();

  function updateCountdown() {
    const now = new Date().getTime();
    const distance = electionDate - now;

    if (distance < 0) {
      document.getElementById('countdown').innerHTML = "<h3>Pemilihan Sedang Berlangsung!</h3>";
      return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById('days').innerHTML = days.toString().padStart(2, '0');
    document.getElementById('hours').innerHTML = hours.toString().padStart(2, '0');
    document.getElementById('minutes').innerHTML = minutes.toString().padStart(2, '0');
    document.getElementById('seconds').innerHTML = seconds.toString().padStart(2, '0');
  }

  setInterval(updateCountdown, 1000);
  updateCountdown();

  // Mobile Menu
  document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
    document.querySelector('.main-nav').classList.toggle('active');
  });

  // Smooth Scroll
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth'
        });
      }
    });
  });
  </script>
</body>

</html>