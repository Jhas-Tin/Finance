<?php
// sidebar.php
?>

<style>
.sidebar {
    width: 240px;
    height: 100vh;
    background: #0b1f33;
    color: #fff;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px;
    overflow-y: auto;
    z-index: 100;
    transition: transform 0.3s ease;

}
/* CLOSED STATE */
.sidebar.closed {
    transform: translateX(-240px);
}

.sidebar-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo-container {
    width: 70px;
    height: 70px;
    background: transparent;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.logo-container i {
    font-size: 20px;
    color: #0b1f33;
}

.logo-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.school-name {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.school-name h2 {
    font-size: 14px;
    font-weight: 700;
    margin: 0;
    color: #fff;
    line-height: 1.2;
    letter-spacing: 0.5px;
}

.school-name h3 {
    font-size: 10px;
    margin: 5px 0 0 0;
    color: #9fb3c8;
    font-weight: 400;
    letter-spacing: 0.5px;
}

.sidebar h3.menu-title {
    font-size: 12px;
    margin: 20px 0 15px;
    color: #9fb3c8;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    padding-left: 5px;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin-bottom: 5px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    border-radius: 8px;
    color: #cbd5e1;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s ease;
    font-weight: 500;
}

.sidebar-menu a i {
    width: 20px;
    text-align: center;
    font-size: 16px;
    color: #9fb3c8;
    transition: color 0.2s ease;
}

.sidebar-menu a.active,
.sidebar-menu a:hover {
    background: #f59e0b;
    color: #000;
}

.sidebar-menu a.active i,
.sidebar-menu a:hover i {
    color: #000;
}

/* Scrollbar styling for sidebar */
.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>

<div class="sidebar">
    <!-- Logo and School Name -->
    <div class="sidebar-header">
        <div class="logo-container">
            <img src="components/images/hcc.png" alt="Holy Cross College Logo" class="logo-image">
        </div>
        <div class="school-name">
            <h2>HOLY CROSS COLLEGE</h2>
            <h3>School Management System</h3>
        </div>
    </div>

    <!-- Main Navigation Menu -->
    <h3 class="menu-title">Main Menu</h3>
    <ul class="sidebar-menu">
        <li><a href="#">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a></li>
        <li><a href="#" class="active">
            <i class="fas fa-money-bill-wave"></i>
            Finance
        </a></li>
        <li><a href="#">
            <i class="fas fa-chalkboard-teacher"></i>
            Teachers
        </a></li>
        <li><a href="#">
            <i class="fas fa-user-graduate"></i>
            Students
        </a></li>
        <li><a href="#">
            <i class="fas fa-clipboard-check"></i>
            Attendance
        </a></li>
        <li><a href="#">
            <i class="fas fa-bullhorn"></i>
            Notice
        </a></li>
        <li><a href="#">
            <i class="fas fa-calendar-alt"></i>
            Calendar
        </a></li>
        <li><a href="#">
            <i class="fas fa-book"></i>
            Library
        </a></li>
        <li><a href="#">
            <i class="fas fa-comments"></i>
            Message
        </a></li>
    </ul>
</div>