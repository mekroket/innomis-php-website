<aside class="sidebar">
    <div class="brand">
        <img src="assets/img/ana logo.png" alt="Logo" class="logo-img">
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span class="nav-text">Üyeler</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="etkinlik.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'etkinlik.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span class="nav-text">Etkinlikler</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="kurul.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kurul.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i>
                <span class="nav-text">Yönetim Kurulu</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="finans.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'finans.php' ? 'active' : ''; ?>">
                <i class="fas fa-wallet"></i>
                <span class="nav-text">Finans</span>
            </a>
        </li>
    </ul>
</aside>

<style>
.sidebar {
    width: 280px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: var(--card-bg);
    border-right: 1px solid rgba(255,255,255,0.1);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    z-index: 1000;
}

.brand {
    margin-bottom: 2rem;
}

.logo-img {
    height: 40px;
    width: auto;
}

.nav-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 0.5rem;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.8rem 1rem;
    color: var(--text);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: rgba(255,255,255,0.1);
    color: var(--accent);
}

.nav-link.active {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.nav-link i {
    font-size: 1.2rem;
    margin-right: 1rem;
    width: 24px;
    text-align: center;
}

.nav-text {
    font-size: 0.95rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .sidebar.show {
        transform: translateX(0);
    }
}
</style> 