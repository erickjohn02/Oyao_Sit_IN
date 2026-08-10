<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user details including remaining sessions
$query = "SELECT id, firstname, lastname, email, profile_pic, course, yearlevel, address, remaining_sessions FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $fullname = $user['firstname'] . " " . $user['lastname'];
    $email = $user['email'];
    $address = $user['address'];
    $course = $user['course'];
    $yearlevel = $user['yearlevel'];
    $profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default.png';
    $remainingSessions = isset($user['remaining_sessions']) ? (int)$user['remaining_sessions'] : 0;
} else {
    header("Location: login.php");
    exit();
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Remaining Sessions</title>
    <style>
        /* Global Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: url('./images/UCMain.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Styling */
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

        /* Main Container */
        .main-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 20px;
            height: 100%;
        }

        /* Sidebar - Left */
        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            margin-bottom: 15px;
        }

        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .username, .info {
            font-size: 16px;
            font-weight: 350;
            color: #333;
            text-align: left;
            width: 100%;
            padding-left: 10px;
            padding-bottom: 10px;
        }

        /* Sessions Container */
        .sessions-container {
            flex: 1;
            background: rgba(255, 255, 255, 0.7);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .sessions-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .sessions-count {
            font-size: 72px;
            font-weight: bold;
            color: #ff4b5c;
            margin: 20px 0;
        }

        .sessions-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }

        .warning-message {
            color: #ff4b5c;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="dashboard-title">
            <a href="dashboard.php">Dashboard</a>
        </div>
        <div class="nav-links">
            <a href="edit.php">Edit Profile</a>
            <a href="view_sessions.php">View Remaining Sessions</a>
            <form action="logout.php" method="POST" style="display:inline;">
                <button class="logout-btn" type="submit">Log out</button>
            </form>
        </div>
    </div>
    
    <div class="main-container">
        <div class="sidebar">
            <div class="profile-pic">
                <img src="./images/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
            </div>
            <div class="username"><strong>Name:</strong> <?php echo htmlspecialchars($fullname); ?></div>
            <div class="info"><strong>Course:</strong> <?php echo htmlspecialchars($course); ?></div>
            <div class="info"><strong>Year:</strong> <?php echo htmlspecialchars($yearlevel); ?></div>
            <div class="info"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></div>
            <div class="info"><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></div>
        </div>

        <div class="sessions-container">
            <h2 class="sessions-title">Remaining Sessions</h2>
            <div class="sessions-count"><?php echo $remainingSessions; ?></div>
            <p class="sessions-message">
                You have <?php echo $remainingSessions; ?> session<?php echo $remainingSessions != 1 ? 's' : ''; ?> remaining.
            </p>
            <?php if ($remainingSessions <= 2): ?>
            <p class="warning-message">
                Warning: You have <?php echo $remainingSessions; ?> session<?php echo $remainingSessions != 1 ? 's' : ''; ?> left. 
                Please contact the administrator if you need more sessions.
            </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 