<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php'; // Database

$username = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
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
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            background: url('./images/UCMain.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            width: 100%;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.7);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            margin-top: 250px;
        }

        .profile-pic {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #eee;
            font-size: 45px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            overflow: hidden;
            margin: 0 auto 15px auto;
        }

        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        #profile-input {
            display: none;
        }
        .text-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Edit Profile</h2>
        <form action="upload_profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="idno" value="<?php echo htmlspecialchars($user['idno']); ?>">

            <!-- Profile Picture -->
            <div class="text-center">
                <?php 
                    $profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default.png';
                ?>
                <label for="profile-input" class="profile-pic">
                    <img id="profile-preview" src="images/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" onerror="this.onerror=null; this.src='images/default.png';">
                </label>
                <input type="file" name="profile_pic" id="profile-input" accept="image/*" onchange="previewImage(event)">
            </div>

            <!-- Other Profile Fields -->
            <div class="mb-3">
                <label>Last Name</label>
                <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
            </div>

            <div class="mb-3">
                <label>First Name</label>
                <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Middle Name</label>
                <input type="text" name="middlename" class="form-control" value="<?php echo htmlspecialchars($user['middlename']); ?>">
            </div>

            <div class="mb-3">
                <label>Course</label>
                <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($user['course']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Year Level</label>
                <input type="text" name="yearlevel" class="form-control" value="<?php echo htmlspecialchars($user['yearlevel']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Address</label>
                <input type="address" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save</button>
            <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Back</a>
        </form>
    </div>

    <!-- JavaScript for Live Profile Picture Preview -->
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('profile-preview');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
