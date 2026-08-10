<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';
is_admin();

// Fetch admin details
$username = $_SESSION['username'];
$query = "SELECT * FROM admins WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | CCS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <style>
        body {
            padding-top: 60px;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 250px;
            background-color: #343a40;
            padding-top: 20px;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .nav-link {
            color: #fff;
            padding: 10px 20px;
        }
        .nav-link:hover {
            background-color: #495057;
            color: #fff;
        }
        .nav-link.active {
            background-color: #0d6efd;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <div class="top-navbar bg-primary text-white p-3 d-flex justify-content-between">
        <div class="top-brand">CCS Admin Panel</div>
        <div>Welcome, <?= htmlspecialchars($admin['username']) ?>!</div>
        <form action="logout.php" method="POST">
            <button type="submit" class="btn btn-danger">Log Out</button>
        </form>
    </div>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="sit_in_request.php">
                    <i class="fas fa-users"></i> Sit-in Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_sit_in_records.php">
                    <i class="fas fa-list"></i> View Records
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_reservations.php">
                    <i class="fas fa-calendar-check"></i> Reservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="student_information.php">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="lab_management.php">
                    <i class="fas fa-laptop-code"></i> Lab Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="top_users.php">
                    <i class="fas fa-trophy"></i> Top Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="announcements.php">
                    <i class="fas fa-bullhorn"></i> Announcements
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content"> 