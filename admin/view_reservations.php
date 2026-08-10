<?php
require_once 'includes/header.php';

// Handle reservation status update
if (isset($_POST['update_status'])) {
    $reservation_id = sanitize_input($_POST['reservation_id']);
    $status = sanitize_input($_POST['status']);
    $admin_notes = sanitize_input($_POST['admin_notes']);

    $query = "UPDATE reservations SET status = ?, admin_notes = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $status, $admin_notes, $reservation_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Reservation status updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating reservation status!');</script>";
    }
}

// Handle date filter
$start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : date('Y-m-d', strtotime('+7 days'));
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Build query based on filters
$query = "SELECT r.*, u.firstname, u.lastname, u.course 
          FROM reservations r 
          JOIN users u ON r.user_id = u.id 
          WHERE r.date BETWEEN ? AND ?";
$params = [$start_date, $end_date];
$types = "ss";

if ($status_filter) {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY r.date ASC, r.time_slot ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$reservations = $stmt->get_result();

// Calculate statistics
$total_reservations = $reservations->num_rows;
$pending_count = 0;
$approved_count = 0;
$rejected_count = 0;

$reservations->data_seek(0);
while ($reservation = $reservations->fetch_assoc()) {
    switch ($reservation['status']) {
        case 'pending':
            $pending_count++;
            break;
        case 'approved':
            $approved_count++;
            break;
        case 'rejected':
            $rejected_count++;
            break;
    }
}
$reservations->data_seek(0);

// Handle Approve/Reject actions
if (isset($_POST['approve_reservation'])) {
    $reservation_id = sanitize_input($_POST['reservation_id']);
    // Get reservation details
    $query = "SELECT * FROM reservations WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();
    if ($reservation && $reservation['status'] === 'pending') {
        // Insert into sit_in_records
        $insert = "INSERT INTO sit_in_records (user_id, date, time_in, purpose, lab) VALUES (?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($insert);
        $stmt2->bind_param("issss", $reservation['user_id'], $reservation['date'], $reservation['time_slot'], $reservation['purpose'], $reservation['lab']);
        if ($stmt2->execute()) {
            // Update reservation status to approved
            $update = "UPDATE reservations SET status = 'approved' WHERE id = ?";
            $stmt3 = $conn->prepare($update);
            $stmt3->bind_param("i", $reservation_id);
            $stmt3->execute();
            echo "<script>alert('Reservation approved and moved to Sit-in Management!');</script>";
        } else {
            echo "<script>alert('Error approving reservation!');</script>";
        }
    }
}
if (isset($_POST['reject_reservation'])) {
    $reservation_id = sanitize_input($_POST['reservation_id']);
    $update = "UPDATE reservations SET status = 'rejected' WHERE id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("i", $reservation_id);
    if ($stmt->execute()) {
        echo "<script>alert('Reservation rejected!');</script>";
    } else {
        echo "<script>alert('Error rejecting reservation!');</script>";
    }
}

// Fetch pending reservations
$query = "SELECT r.*, u.idno, u.firstname, u.lastname, u.course FROM reservations r JOIN users u ON r.user_id = u.id WHERE r.status = 'pending' ORDER BY r.date DESC, r.time_slot DESC";
$result = $conn->query($query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reservations Management</h2>
        <div>
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Reservations</h5>
                    <h2 class="card-text"><?= $total_reservations ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2 class="card-text"><?= $pending_count ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Approved</h5>
                    <h2 class="card-text"><?= $approved_count ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Rejected</h5>
                    <h2 class="card-text"><?= $rejected_count ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservations Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Reservations List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Lab</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($reservation = $reservations->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($reservation['date'])) ?></td>
                            <td><?= date('h:i A', strtotime($reservation['time_slot'])) ?></td>
                            <td><?= htmlspecialchars($reservation['firstname'] . ' ' . $reservation['lastname']) ?></td>
                            <td><?= htmlspecialchars($reservation['course']) ?></td>
                            <td><?= htmlspecialchars($reservation['lab']) ?></td>
                            <td><?= htmlspecialchars($reservation['purpose']) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $reservation['status'] === 'approved' ? 'success' : 
                                    ($reservation['status'] === 'rejected' ? 'danger' : 'warning') 
                                ?>">
                                    <?= ucfirst($reservation['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#updateStatusModal<?= $reservation['id'] ?>">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                            </td>
                        </tr>

                        <!-- Update Status Modal -->
                        <div class="modal fade" id="updateStatusModal<?= $reservation['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Reservation Status</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="pending" <?= $reservation['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="approved" <?= $reservation['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                                    <option value="rejected" <?= $reservation['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Admin Notes</label>
                                                <textarea name="admin_notes" class="form-control" rows="3"><?= htmlspecialchars($reservation['admin_notes'] ?? '') ?></textarea>
                                            </div>
                                            
                                            <div class="text-end">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 