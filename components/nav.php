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
    transition: background 0.3s ease;
}

.navbar-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.menu-toggle {
    font-size: 24px;
    cursor: pointer;
    color: var(--text-primary);
    transition: color 0.2s ease;
}

.menu-toggle:hover {
    color: var(--accent);
}

.navbar-title {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.navbar h1 {
    font-size: 16px;
    font-weight: 700;   
    margin: 0;
    color: var(--text-primary);
}

.navbar-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 400;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.nav-icon {
    font-size: 18px;
    cursor: pointer;
    color: var(--text-primary);
    transition: color 0.2s ease;
    width: 20px;
    text-align: center;
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
}

.theme-toggle:hover {
    color: var(--accent);
}

.nav-profile {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
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

.profile-dropdown-toggle {
    position: relative;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.dropdown-arrow {
    font-size: 10px;
    color: var(--text-muted);
    transition: transform 0.2s ease;
    margin-left: 4px;
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

.semester-dropdown-toggle {
    position: relative;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.semester-dropdown-arrow {
    font-size: 10px;
    color: var(--text-muted);
    transition: transform 0.2s ease;
    margin-left: 4px;
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
</style>

<div class="navbar">
    <div class="navbar-left">
        <span id="menuToggle" class="menu-toggle">☰</span>
        <div class="navbar-title">
            <h1>FINANCE DASHBOARD</h1>
            <div class="semester-dropdown-toggle">
                <span class="navbar-subtitle" id="semesterDisplay">Finance / 2024-2025 1st Semester</span>
                <span class="semester-dropdown-arrow">▼</span>
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
        <span class="nav-icon">🔔</span>
        <span id="themeToggle" class="theme-toggle">🌙</span>
        <div class="profile-dropdown-toggle">
            <div class="nav-profile">
                <div class="nav-profile-avatar">SA</div>
                <div class="nav-profile-text">
                    <p class="nav-profile-name">Sample Admin</p>
                    <p class="nav-profile-role">Admin</p>
                </div>
            </div>
            <span class="dropdown-arrow">▼</span>
            <div class="profile-dropdown-menu">
                <a href="#">Profile</a>
                <a href="#">Settings</a>
                <a href="#">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileDropdownToggle = document.querySelector('.profile-dropdown-toggle');
    const profileDropdownMenu = document.querySelector('.profile-dropdown-menu');

    if (profileDropdownToggle) {
        profileDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdownMenu.classList.toggle('active');
        });
    }

    // Semester dropdown functionality
    const semesterDropdownToggle = document.querySelector('.semester-dropdown-toggle');
    const semesterDropdownMenu = document.getElementById('semesterDropdown');
    const semesterDisplay = document.getElementById('semesterDisplay');
    const semesterLinks = semesterDropdownMenu.querySelectorAll('a');

    if (semesterDropdownToggle) {
        semesterDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            semesterDropdownMenu.classList.toggle('active');
        });
    }

    semesterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Update the display text
            semesterDisplay.textContent = this.textContent;
            // Mark as active
            semesterLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            // Close dropdown
            semesterDropdownMenu.classList.remove('active');
            // You can add logic here to fetch data for the selected semester
            console.log('Selected semester:', this.dataset.semester);
        });
    });

    // Set initial active state
    semesterLinks[0].classList.add('active');

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.profile-dropdown-toggle')) {
            profileDropdownMenu.classList.remove('active');
        }
        if (!e.target.closest('.semester-dropdown-toggle')) {
            semesterDropdownMenu.classList.remove('active');
        }
    });

    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    
    // Check for saved theme preference or default to dark mode
    const currentTheme = localStorage.getItem('theme') || 'dark-mode';
    html.classList.add(currentTheme);
    updateThemeIcon(currentTheme);

    function updateThemeIcon(theme) {
        if (theme === 'dark-mode') {
            themeToggle.textContent = '🌙';
        } else {
            themeToggle.textContent = '☀️';
        }
    }

    themeToggle.addEventListener('click', function() {
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
});
</script>
