<?php
date_default_timezone_set('Asia/Manila');
require_once 'includes/header.php';

// Handle sit-in request submission
if (isset($_POST['submit_request'])) {
    $user_id = sanitize_input($_POST['user_id']);
    $purpose = sanitize_input($_POST['purpose']);
    $lab = sanitize_input($_POST['lab']);
    $pc = sanitize_input($_POST['pc']);
    $date = date('Y-m-d');
    $time_in = date('H:i:s');

    // Check if user has remaining sessions
    $check_sessions = "SELECT remaining_sessions FROM users WHERE id = ?";
    $stmt = $conn->prepare($check_sessions);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['remaining_sessions'] > 0) {
        // Insert directly into sit_in_records
        $query = "INSERT INTO sit_in_records (user_id, date, time_in, purpose, lab, pc) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssss", $user_id, $date, $time_in, $purpose, $lab, $pc);
        if ($stmt->execute()) {
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

    // Get the user_id and remaining sessions from the sit-in record
    $get_user = "SELECT s.user_id, u.remaining_sessions 
                 FROM sit_in_records s 
                 JOIN users u ON s.user_id = u.id 
                 WHERE s.id = ?";
    $stmt = $conn->prepare($get_user);
    $stmt->bind_param("i", $sit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sit_in = $result->fetch_assoc();

    if ($sit_in) {
        // Update sit-in record with time out
        $query = "UPDATE sit_in_records SET time_out = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $time_out, $sit_id);
        
        if ($stmt->execute()) {
            // Decrement remaining sessions
            $update_sessions = "UPDATE users SET remaining_sessions = remaining_sessions - 1 WHERE id = ?";
            $stmt = $conn->prepare($update_sessions);
            $stmt->bind_param("i", $sit_in['user_id']);
            $stmt->execute();

            // Check if remaining sessions is now 0
            if ($sit_in['remaining_sessions'] <= 1) {
                // Delete user account
                $delete_user = "DELETE FROM users WHERE id = ?";
                $stmt = $conn->prepare($delete_user);
                $stmt->bind_param("i", $sit_in['user_id']);
                $stmt->execute();
                
                echo "<script>alert('Sit-out recorded successfully! User account has been deleted due to no remaining sessions.');</script>";
            } else {
                echo "<script>alert('Sit-out recorded successfully!');</script>";
            }
        } else {
            echo "<script>alert('Error recording sit-out!');</script>";
        }
    }
}

// Fetch pending sit-in requests (active sit-ins)
$query = "SELECT s.*, u.idno, u.firstname, u.lastname, u.course, u.remaining_sessions 
          FROM sit_in_records s 
          JOIN users u ON s.user_id = u.id 
          WHERE s.time_out IS NULL 
          ORDER BY s.date DESC, s.time_in DESC";
$result = $conn->query($query);

// Fetch available PCs for the selected lab
function get_available_pcs($conn, $lab) {
    $pcs = range(1, 50);
    $available_pcs = [];
    $res = $conn->query("SELECT pc_number, status FROM lab_pcs WHERE lab='{$lab}'");
    $statuses = [];
    while ($row = $res->fetch_assoc()) {
        $statuses[$row['pc_number']] = $row['status'];
    }
    foreach ($pcs as $pc) {
        if (!isset($statuses[$pc]) || $statuses[$pc] === 'available') {
            $available_pcs[] = $pc;
        }
    }
    return $available_pcs;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sit-ins</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRequestModal">
            <i class="fas fa-plus"></i> Sit-in
        </button>
    </div>

    <!-- Sit-in Requests Table -->
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
                            <th>Remaining Sessions</th>
                            <th>Status</th>
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
                            <td><?= htmlspecialchars($row['remaining_sessions']) ?></td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
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

<!-- New Request Modal -->
<div class="modal fade" id="newRequestModal" tabindex="-1">
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
                            <option value="Java Programming">Java Programming</option>
                            <option value="Python Programming">Python Programming</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lab</label>
                        <select name="lab" class="form-select" required>
                            <option value="524">524</option>
                            <option value="526">526</option>
                            <option value="530">530</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">PC</label>
                        <select name="pc" class="form-select" required>
                            <?php
                            $selected_lab = isset($_POST['lab']) ? $_POST['lab'] : '524';
                            $available_pcs = get_available_pcs($conn, $selected_lab);
                            foreach ($available_pcs as $pc): ?>
                                <option value="<?= $pc ?>">PC<?= $pc ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="submit_request" class="btn btn-primary">Sit-in</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 