<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
// Fetch user details
$query = "SELECT id, firstname, lastname, email, profile_pic, course, yearlevel, address, remaining_sessions FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $fullname = $user['firstname'] . " " . $user['lastname'];
    $profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default.png';
} else {
    header("Location: login.php");
    exit();
}
$stmt->close();

// Fetch all reservation notifications for this user
$notifications = [];
$query = "SELECT * FROM reservations WHERE user_id = ? ORDER BY date DESC, time_slot DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: url('./images/UCMain.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }
        .header {
            width: 100%;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.2);
        }
        .dashboard-title a {
            text-decoration: none;
            color: #333;
            font-size: 20px;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 8px;
            transition: 0.3s;
            background: rgba(255, 255, 255, 0.3);
        }
        .dashboard-title a:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        .nav-links {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .nav-links a, .logout-btn {
            text-decoration: none;
            color: #333;
            font-size: 16px;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            transition: 0.3s;
            background: rgba(255, 255, 255, 0.3);
        }
        .nav-links a:hover, .logout-btn:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        .logout-btn {
            background-color: #ff4b5c;
            color: white;
            border: none;
            cursor: pointer;
        }
        .container {
            margin-top: 40px;
            max-width: 700px;
            background: rgba(255,255,255,0.85);
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.2);
            padding: 30px 30px 20px 30px;
        }
        .notif-card {
            border-left: 6px solid #007bff;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 18px;
            padding: 18px 18px 12px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .notif-status {
            font-weight: bold;
            text-transform: uppercase;
        }
        .notif-status.pending { color: #ffc107; }
        .notif-status.approved { color: #28a745; }
        .notif-status.rejected { color: #dc3545; }
        .notif-date {
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="dashboard-title" style="display: flex; align-items: center; gap: 10px;">
            <a href="dashboard.php">Dashboard</a>
        </div>
        <div class="nav-links" style="align-items: center;">
            <a href="edit.php">Edit Profile</a>
            <a href="view_sessions.php">View Remaining Sessions</a>
            <a href="user_reservation.php">Reserve a Computer</a>
            <a href="sit_in_history.php">Sit-in History</a>
            <a href="notifications.php" class="fw-bold">Notifications</a>
            <form action="logout.php" method="POST" style="display:inline;">
                <button class="logout-btn" type="submit">Log out</button>
            </form>
        </div>
    </div>
    <div class="container mt-4">
        <h2 class="mb-4">Notifications</h2>
        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">You have no notifications yet.</div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notif-card">
                    <div><strong>Lab:</strong> <?= htmlspecialchars($notif['lab']) ?> | <strong>Purpose:</strong> <?= htmlspecialchars($notif['purpose']) ?></div>
                    <div class="notif-date">Date: <?= date('M d, Y', strtotime($notif['date'])) ?> | Time: <?= date('h:i A', strtotime($notif['time_slot'])) ?></div>
                    <div class="notif-status <?= strtolower($notif['status']) ?>">Status: <?= ucfirst($notif['status']) ?></div>
                    <?php if (!empty($notif['admin_notes'])): ?>
                        <div><small><?= htmlspecialchars($notif['admin_notes']) ?></small></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html> 