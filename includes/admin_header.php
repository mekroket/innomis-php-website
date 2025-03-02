<?php
// Admin girişi kontrolü
if (!isset($_SESSION['admin'])) {
    header("Location: adminlogin.php");
    exit();
}

// Admin bilgilerini al
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT name, image, role FROM authorities WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Rol ismini Türkçeleştir
$role_names = [
    'admin' => 'Yönetici',
    'moderator' => 'Moderatör',
    'editor' => 'Editör'
];
$role_display = $role_names[$admin['role']] ?? $admin['role'];
?>

<header class="header">
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title"><?php echo $page_title; ?></h1>
        </div>
        <div class="header-right">
            <div class="admin-profile">
                <div class="admin-info">
                    <div class="admin-details">
                        <span class="welcome-text">Hoş geldiniz</span>
                        <span class="admin-name"><?php echo htmlspecialchars($admin['name']); ?></span>
                    </div>
                    <span class="admin-role"><?php echo htmlspecialchars($role_display); ?></span>
                </div>
                <img src="<?php echo htmlspecialchars($admin['image']); ?>" alt="Admin" class="admin-avatar">
            </div>
            <div class="admin-dropdown">
                <a href="adminlogin.php?logout=1" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                </a>
            </div>
        </div>
    </div>
</header>

<style>
.header {
    background: var(--card-bg);
    padding: 0.5rem 2rem;
    position: fixed;
    top: 0;
    right: 0;
    left: 280px;
    z-index: 100;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: 70px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 100%;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.admin-profile:hover {
    background: rgba(59, 130, 246, 0.1);
}

.admin-info {
    display: flex;
    align-items: flex-end;
    flex-direction: column;
}

.admin-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.welcome-text {
    color: var(--text-muted);
    font-size: 0.75rem;
    margin-bottom: -2px;
}

.admin-name {
    color: var(--text);
    font-weight: 600;
    font-size: 0.95rem;
}

.admin-role {
    color: var(--accent);
    font-size: 0.8rem;
    font-weight: 500;
    padding: 2px 8px;
    background: rgba(59, 130, 246, 0.2);
    border-radius: 4px;
    margin-top: 2px;
}

.admin-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--accent);
}

.header-right {
    position: relative;
}

.admin-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--card-bg);
    border-radius: 8px;
    padding: 0.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    display: none;
    min-width: 160px;
    z-index: 1000;
}

.admin-profile:hover + .admin-dropdown,
.admin-dropdown:hover {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1rem;
    color: var(--text);
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s ease;
    cursor: pointer;
}

.dropdown-item:hover {
    background: rgba(59, 130, 246, 0.1);
    color: var(--accent);
}

.admin-dropdown::before {
    content: '';
    position: absolute;
    top: -8px;
    right: 20px;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid var(--card-bg);
}

@media (max-width: 768px) {
    .header {
        left: 0;
        padding: 0.5rem 1rem;
    }

    .welcome-text {
        display: none;
    }

    .admin-info {
        display: none;
    }

    .admin-profile {
        padding: 0.3rem;
    }

    .admin-avatar {
        width: 35px;
        height: 35px;
    }
}
</style> 