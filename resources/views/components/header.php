<?php
use App\Application\Helpers\AuthHelper;

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAuthenticated = AuthHelper::isAuthenticated();
$currentUser = AuthHelper::getCurrentUser();
$userInitial = AuthHelper::getUserInitial();
$userDisplayName = AuthHelper::getUserDisplayName();
$isAdmin = AuthHelper::isAdmin();
?>

<header class="main-header">
    <div class="header-container">
        <div class="logo-nav">
            <img src="https://img.icons8.com/color/48/000000/graduation-cap--v2.png" alt="Logo" class="logo-img">
            <a href="/" class="logo-title">Cerita Mahasiswa</a>
        </div>
        
        <div class="nav-section">
            <nav class="main-nav">
                <a href="/" class="nav-link <?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>">Beranda</a>
                <a href="/stories" class="nav-link <?= ($currentPage ?? '') === 'stories' ? 'active' : '' ?>">Cerita</a>
                <a href="/about" class="nav-link <?= ($currentPage ?? '') === 'about' ? 'active' : '' ?>">Tentang</a>
            </nav>
            
            <!-- Authentication Section -->
            <div class="auth-section">
                <?php if (!$isAuthenticated): ?>
                    <!-- Login Button (shown when not logged in) -->
                    <a href="/login" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                <?php else: ?>
                    <!-- User Profile Dropdown (shown when logged in) -->
                    <div class="user-profile">
                        <button class="profile-btn" onclick="toggleProfileDropdown()">
                            <div class="profile-avatar">
                                <span class="user-initial"><?= htmlspecialchars($userInitial) ?></span>
                            </div>
                            <span class="user-name"><?= htmlspecialchars($userDisplayName) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        <div class="dropdown-menu" id="profileDropdown">
                            <a href="/user/profile" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                Profile Setting
                            </a>
                            <a href="/user" class="dropdown-item">
                                <i class="fas fa-book"></i>
                                Cerita Saya
                            </a>
                            <?php if ($isAdmin): ?>
                                <div class="dropdown-divider"></div>
                                <a href="/admin" class="dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    Admin Panel
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const profile = document.querySelector('.user-profile');
    const dropdown = document.getElementById('profileDropdown');
    
    if (profile && !profile.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>
