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

// Fetch all sit-in records for this user
$sitins = [];
$query = "SELECT * FROM sit_in_records WHERE user_id = ? ORDER BY date DESC, time_in DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $sitins[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-in History</title>
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
            max-width: 800px;
            background: rgba(255,255,255,0.85);
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.2);
            padding: 30px 30px 20px 30px;
        }
        .sit-card {
            border-left: 6px solid #28a745;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 18px;
            padding: 18px 18px 12px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .sit-date {
            font-size: 14px;
            color: #888;
        }
        .sit-label {
            font-weight: bold;
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
            <a href="sit_in_history.php" class="fw-bold">Sit-in History</a>
            <a href="notifications.php">Notifications</a>
            <form action="logout.php" method="POST" style="display:inline;">
                <button class="logout-btn" type="submit">Log out</button>
            </form>
        </div>
    </div>
    <div class="container mt-4">
        <h2 class="mb-4">Sit-in History</h2>
        <?php if (empty($sitins)): ?>
            <div class="alert alert-info">You have no sit-in records yet.</div>
        <?php else: ?>
            <?php foreach ($sitins as $sit): ?>
                <div class="sit-card">
                    <div><span class="sit-label">Lab:</span> <?= htmlspecialchars($sit['lab']) ?> | <span class="sit-label">Purpose:</span> <?= htmlspecialchars($sit['purpose']) ?></div>
                    <div class="sit-date">Date: <?= date('M d, Y', strtotime($sit['date'])) ?> | Time In: <?= htmlspecialchars($sit['time_in']) ?><?php if (!empty($sit['time_out'])): ?> | Time Out: <?= htmlspecialchars($sit['time_out']) ?><?php endif; ?></div>
                    <?php if (!empty($sit['pc'])): ?>
                        <div><span class="sit-label">PC:</span> <?= htmlspecialchars($sit['pc']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html> 