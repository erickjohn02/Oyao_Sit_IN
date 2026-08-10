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
    
    // If remaining sessions is 0, prevent access and show an alert
    if ($remainingSessions <= 0) {
        echo "<script>alert('Your account has no remaining sessions. Please contact the administrator.'); window.location.href='logout.php';</script>";
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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

        /* Main Layout */
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

        /* Content - Center */
        .content-container {
            flex: 1;
            background: rgba(255, 255, 255, 0.7);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            height: 250px;
            overflow: hidden;
            width: 200px;
        }

        h3 {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Rules - Right */
        .rules-container {
            width: 350px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.7);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            height: 450px;
            overflow: hidden;
        }

        /* Scrollable content */
        .announcement-content, .rules-content {
            max-height: 290px;
            overflow-y: auto;
            padding-right: 10px;
        }

        /* Custom scrollbar */
        .announcement-content::-webkit-scrollbar, 
        .rules-content::-webkit-scrollbar {
            width: 6px;
        }

        .announcement-content::-webkit-scrollbar-thumb, 
        .rules-content::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
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
            <a href="notifications.php">Notifications</a>
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
            <div class="info"><strong>Remaining Session:</strong> <?php echo $remainingSessions > 0 ? htmlspecialchars($remainingSessions) : 'N/A'; ?></div>
        </div>

        <div class="content-container">
            <h3>Announcements</h3>
            <div class="announcement-content">
                <?php
                // Fetch announcements from the database
                $announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 10");
                if ($announcements && $announcements->num_rows > 0):
                    while($row = $announcements->fetch_assoc()): ?>
                        <p><strong><?= htmlspecialchars($row['title']) ?> | <?= date('Y-M-d', strtotime($row['created_at'])) ?></strong><br><?= nl2br(htmlspecialchars($row['content'])) ?></p><br>
                    <?php endwhile;
                else:
                    echo '<p>No announcements at this time.</p>';
                endif;
                ?>
            </div>
        </div>

        <div class="rules-container">
    <h3 style="text-align: left;">Rules and Regulations</h3>
    <p style="text-align: center; font-weight: bold;">
        University of Cebu<br>
        COLLEGE OF INFORMATION & COMPUTER STUDIES
    </p>
    <br>
    <div class="rules-content">
        <p><strong>LABORATORY RULES AND REGULATIONS</strong></p>
        <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
        <br>
        <ol>
            <li>1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans, and other personal equipment must be switched off.</li>
            <li>2. Games are not allowed inside the lab. This includes computer-related games, card games, and other games that may disturb the operation of the lab.</li>
            <li>3. Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing software are strictly prohibited.</li>
            <li>4. Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</li>
            <li>5. Deleting computer files and changing the set-up of the computer is a major offense.</li>
            <li>6. Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</li>
            <li>7. Observe proper decorum while inside the laboratory.
                <ul>
                    <li>Do not get inside the lab unless the instructor is present.</li>
                    <li>All bags, knapsacks, and the likes must be deposited at the counter.</li>
                    <li>Follow the seating arrangement of your instructor.</li>
                    <li>At the end of class, all software programs must be closed.</li>
                    <li>Return all chairs to their proper places after using.</li>
                </ul>
            </li>
            <li>8. Chewing a gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.</li>
            <li>9. Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</li>
            <li>10. Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</li>
            <li>11. For serious offenses, the lab personnel may call the Civil Security Office (CSU) for assistance.</li>
            <li>12. Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant, or instructor immediately.</li>
        </ol>
        <br>
        <p><strong>DISCIPLINARY ACTION</strong></p>
        <ul>
            <li><strong>First Offense</strong> - The Head or the Dean or OIC recommends to the Guidance Center for a suspension from classes for each offender.</li>
            <li><strong>Second and Subsequent Offenses</strong> - A recommendation for a heavier sanction will be endorsed to the Guidance Center.</li>
        </ul>
    </div>
</div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
