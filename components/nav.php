<style>
:root {
    --bg-primary: #0b1f33;
    --bg-secondary: #fff;
    --text-primary: #fff;
    --text-secondary: #0b1f33;
    --text-muted: #9fb3c8;
    --accent: #f59e0b;
    --border-color: rgba(255, 255, 255, 0.1);
    --card-bg: #f0f0f0;
}

body.dark-mode {
    --bg-primary: #0b1f33;
    --bg-secondary: #fff;
    --text-primary: #fff;
    --text-secondary: #0b1f33;
    --text-muted: #9fb3c8;
    --accent: #f59e0b;
    --border-color: rgba(255, 255, 255, 0.1);
    --card-bg: #f0f0f0;
}

body.light-mode {
    --bg-primary: #f5f5f5;
    --bg-secondary: #fff;
    --text-primary: #0b1f33;
    --text-secondary: #fff;
    --text-muted: #6b7280;
    --accent: #f59e0b;
    --border-color: rgba(0, 0, 0, 0.1);
    --card-bg: #e5e7eb;
}

.navbar {
    height: 65px;
    background: var(--bg-primary);
    color: var(--text-primary);
    padding: 0 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: left 0.3s ease;
    position: fixed;
    top: 0;
    right: 0;
    left: 240px;
    z-index: 99;
}

.navbar-left {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 1;
    justify-content: flex-start;
}

.menu-toggle {
    font-size: 24px;
    cursor: pointer;
    color: var(--text-primary);
    transition: color 0.2s ease;
    background: none;
    border: none;
    padding: 0;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.menu-toggle:hover {
    color: var(--accent);
}

.navbar-title-container {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex-shrink: 0;
}

.navbar-title-container h1 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary);
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.navbar-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 400;
    white-space: nowrap;
}

.semester-dropdown-wrapper {
    position: relative;
    display: inline-block;
    flex-shrink: 0;
}

.semester-dropdown-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    background: none;
    border: none;
    padding: 0;
    font-size: inherit;
}

.semester-dropdown-arrow {
    font-size: 10px;
    color: var(--text-muted);
    transition: transform 0.2s ease;
    margin-left: 4px;
    pointer-events: none;
}

.semester-dropdown-toggle:hover .semester-dropdown-arrow {
    color: var(--text-primary);
    transform: translateY(2px);
}

.semester-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: var(--bg-secondary);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    margin-top: 10px;
    overflow: hidden;
    display: none;
    z-index: 100;
}

.semester-dropdown-menu.active {
    display: block;
}

.semester-dropdown-menu a {
    display: block;
    padding: 12px 16px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: background 0.2s ease;
    border-bottom: 1px solid var(--border-color);
}

.semester-dropdown-menu a:last-child {
    border-bottom: none;
}

.semester-dropdown-menu a:hover {
    background: var(--accent);
    color: #fff;
}

.semester-dropdown-menu a.active {
    background: var(--accent);
    color: #fff;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-shrink: 0;
}

.nav-icon {
    font-size: 18px;
    cursor: pointer;
    color: var(--text-primary);
    transition: color 0.2s ease;
    width: 20px;
    text-align: center;
    background: none;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.nav-icon:hover {
    color: var(--accent);
}

.theme-toggle {
    font-size: 18px;
    cursor: pointer;
    color: var(--text-primary);
    transition: color 0.2s ease;
    width: 20px;
    text-align: center;
    background: none;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.theme-toggle:hover {
    color: var(--accent);
}

.nav-profile {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.nav-profile-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 13px;
    flex-shrink: 0;
}

.nav-profile-text {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.nav-profile-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    white-space: nowrap;
}

.nav-profile-role {
    font-size: 11px;
    color: var(--text-muted);
    margin: 0;
    white-space: nowrap;
}

.profile-dropdown-wrapper {
    position: relative;
    display: inline-block;
}

.profile-dropdown-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    background: none;
    border: none;
    padding: 0;
}

.dropdown-arrow {
    font-size: 10px;
    color: var(--text-muted);
    transition: transform 0.2s ease;
    margin-left: 4px;
    pointer-events: none;
}

.profile-dropdown-toggle:hover .dropdown-arrow {
    color: var(--text-primary);
    transform: translateY(2px);
}

.profile-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--bg-secondary);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 150px;
    margin-top: 10px;
    overflow: hidden;
    display: none;
    z-index: 1000;
}

.profile-dropdown-menu.active {
    display: block;
}

.profile-dropdown-menu a {
    display: block;
    padding: 12px 16px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: background 0.2s ease;
    border-bottom: 1px solid var(--border-color);
}

.profile-dropdown-menu a:last-child {
    border-bottom: none;
}

.profile-dropdown-menu a:hover {
    background: var(--accent);
    color: #fff;
}
</style>

<div class="navbar">
    <div class="navbar-left">
        <button id="menuToggle" class="menu-toggle">☰</button>
        <div class="navbar-title-container">
            <h1>FINANCE DASHBOARD</h1>
            <div class="semester-dropdown-wrapper">
                <button class="semester-dropdown-toggle" id="semesterToggle">
                    <span class="navbar-subtitle" id="semesterDisplay">Finance / 2024-2025 1st Semester</span>
                    <span class="semester-dropdown-arrow">▼</span>
                </button>
                <div class="semester-dropdown-menu" id="semesterDropdown">
                    <a href="#" data-semester="2024-2025-1st">Finance / 2024-2025 1st Semester</a>
                    <a href="#" data-semester="2024-2025-2nd">Finance / 2024-2025 2nd Semester</a>
                    <a href="#" data-semester="2023-2024-1st">Finance / 2023-2024 1st Semester</a>
                    <a href="#" data-semester="2023-2024-2nd">Finance / 2023-2024 2nd Semester</a>
                    <a href="#" data-semester="2022-2023-1st">Finance / 2022-2023 1st Semester</a>
                    <a href="#" data-semester="2022-2023-2nd">Finance / 2022-2023 2nd Semester</a>
                </div>
            </div>
        </div>
    </div>

    <div class="nav-right">
        <button class="nav-icon">🔔</button>
        <button id="themeToggle" class="theme-toggle">🌙</button>
        <div class="profile-dropdown-wrapper">
            <button class="profile-dropdown-toggle" id="profileToggle">
                <div class="nav-profile">
                    <div class="nav-profile-avatar">SA</div>
                    <div class="nav-profile-text">
                        <p class="nav-profile-name">Sample Admin</p>
                        <p class="nav-profile-role">Admin</p>
                    </div>
                </div>
                <span class="dropdown-arrow">▼</span>
            </button>
            <div class="profile-dropdown-menu" id="profileDropdown">
                <a href="#">Profile</a>
                <a href="#">Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown functionality
    const profileToggle = document.getElementById('profileToggle');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileToggle) {
        profileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });
    }

    // Semester dropdown functionality
    const semesterToggle = document.getElementById('semesterToggle');
    const semesterDropdown = document.getElementById('semesterDropdown');
    const semesterDisplay = document.getElementById('semesterDisplay');
    const semesterLinks = semesterDropdown.querySelectorAll('a');

    if (semesterToggle) {
        semesterToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            semesterDropdown.classList.toggle('active');
        });
    }

    semesterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            semesterDisplay.textContent = this.textContent;
            semesterLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            semesterDropdown.classList.remove('active');
            
            // You can add logic here to load different semester data
            console.log('Selected semester:', this.dataset.semester);
        });
    });

    // Set initial active state
    if (semesterLinks.length > 0) {
        semesterLinks[0].classList.add('active');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (profileToggle && !profileToggle.contains(e.target)) {
            profileDropdown.classList.remove('active');
        }
        if (semesterToggle && !semesterToggle.contains(e.target)) {
            semesterDropdown.classList.remove('active');
        }
    });

    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    
    const currentTheme = localStorage.getItem('theme') || 'dark-mode';
    html.classList.add(currentTheme);
    updateThemeIcon(currentTheme);

    function updateThemeIcon(theme) {
        if (themeToggle) {
            themeToggle.textContent = theme === 'dark-mode' ? '🌙' : '☀️';
        }
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const isDarkMode = html.classList.contains('dark-mode');
            
            if (isDarkMode) {
                html.classList.remove('dark-mode');
                html.classList.add('light-mode');
                localStorage.setItem('theme', 'light-mode');
                updateThemeIcon('light-mode');
            } else {
                html.classList.remove('light-mode');
                html.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark-mode');
                updateThemeIcon('dark-mode');
            }
        });
    }

    // Menu toggle functionality
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    const navbar = document.querySelector('.navbar');
    const mainContent = document.querySelector('.main');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (sidebar) {
                sidebar.classList.toggle('closed');
                
                if (sidebar.classList.contains('closed')) {
                    if (mainContent) {
                        mainContent.style.marginLeft = '0';
                        mainContent.classList.add('full');
                    }
                    if (navbar) navbar.style.left = '0';
                } else {
                    if (mainContent) {
                        mainContent.style.marginLeft = '240px';
                        mainContent.classList.remove('full');
                    }
                    if (navbar) navbar.style.left = '240px';
                }
            }
        });
    }

    // Initialize positions
    if (sidebar && navbar && mainContent) {
        if (sidebar.classList.contains('closed')) {
            navbar.style.left = '0';
            mainContent.style.marginLeft = '0';
            mainContent.classList.add('full');
        } else {
            navbar.style.left = '240px';
            mainContent.style.marginLeft = '240px';
        }
    }
});
</script>