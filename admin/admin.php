<?php
require_once 'includes/header.php';

// Handle sit-in submission
if (isset($_POST['sit_in'])) {
    $user_id = sanitize_input($_POST['user_id']);
    $purpose = sanitize_input($_POST['purpose']);
    $lab = sanitize_input($_POST['lab']);
    $date = date("Y-m-d");
    $time_in = date("H:i:s");

    // Check if user has remaining sessions
    $check_sessions = "SELECT remaining_sessions FROM users WHERE id = ?";
    $stmt = $conn->prepare($check_sessions);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['remaining_sessions'] > 0) {
        // Insert sit-in record
        $query = "INSERT INTO sit_in_records (user_id, date, time_in, purpose, lab) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issss", $user_id, $date, $time_in, $purpose, $lab);
        
        if ($stmt->execute()) {
            // Update remaining sessions
            $update_sessions = "UPDATE users SET remaining_sessions = remaining_sessions - 1 WHERE id = ?";
            $stmt = $conn->prepare($update_sessions);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            echo "<script>alert('Sit-in recorded successfully!');</script>";
        } else {
            echo "<script>alert('Error recording sit-in!');</script>";
        }
    } else {
        echo "<script>alert('No remaining sessions available!');</script>";
    }
}

// Handle sit-out
if (isset($_POST['logout_sit_in'])) {
    $sit_id = sanitize_input($_POST['sit_id']);
    $time_out = date("H:i:s");

    $query = "UPDATE sit_in_records SET time_out = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $time_out, $sit_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Sit-out recorded successfully!');</script>";
    } else {
        echo "<script>alert('Error recording sit-out!');</script>";
    }
}

// Fetch current sit-ins
$query = "SELECT s.*, u.idno, u.firstname, u.lastname, u.course 
          FROM sit_in_records s 
          JOIN users u ON s.user_id = u.id 
          WHERE s.time_out IS NULL 
          ORDER BY s.date DESC, s.time_in DESC";
$result = $conn->query($query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Current Sit-ins</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSitInModal">
            <i class="fas fa-plus"></i> New Sit-in
        </button>
    </div>

    <!-- Current Sit-ins Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Purpose</th>
                            <th>Lab</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($row['date'])) ?></td>
                            <td><?= date('h:i A', strtotime($row['time_in'])) ?></td>
                            <td><?= htmlspecialchars($row['idno']) ?></td>
                            <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td><?= htmlspecialchars($row['purpose']) ?></td>
                            <td><?= htmlspecialchars($row['lab']) ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="sit_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="logout_sit_in" class="btn btn-danger btn-sm">
                                        <i class="fas fa-sign-out-alt"></i> Log Out
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New Sit-in Modal -->
<div class="modal fade" id="newSitInModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Sit-in</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php
                            $users = $conn->query("SELECT id, idno, firstname, lastname, course, remaining_sessions 
                                                 FROM users 
                                                 WHERE role = 'student' AND status = 'active' 
                                                 ORDER BY lastname");
                            while($user = $users->fetch_assoc()):
                            ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['idno'] . ' - ' . $user['lastname'] . ', ' . $user['firstname'] . 
                                                   ' (' . $user['course'] . ') - ' . $user['remaining_sessions'] . ' sessions left') ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purpose</label>
                        <select name="purpose" class="form-select" required>
                            <option value="C Programming">C Programming</option>
                            <option value="Database Management">Database Management</option>
                            <option value="Web Development">Web Development</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lab</label>
                        <select name="lab" class="form-select" required>
                            <?php
                            $labs = $conn->query("SELECT name FROM labs WHERE status = 'available' ORDER BY name");
                            while($lab = $labs->fetch_assoc()):
                            ?>
                            <option value="<?= htmlspecialchars($lab['name']) ?>">
                                <?= htmlspecialchars($lab['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="sit_in" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 