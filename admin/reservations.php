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

// Handle date and status filtering
$start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : date('Y-m-d', strtotime('+7 days'));
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Build query based on filters
$query = "SELECT r.*, u.idno, u.firstname, u.lastname, u.course 
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
$result = $stmt->get_result();

// Calculate statistics
$total_reservations = 0;
$pending_count = 0;
$approved_count = 0;
$rejected_count = 0;

$reservations = [];
while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
    $total_reservations++;
    
    switch ($row['status']) {
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
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reservations</h2>
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
            <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Reservations</h5>
                    <h2 class="mb-0"><?= $total_reservations ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2 class="mb-0"><?= $pending_count ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Approved</h5>
                    <h2 class="mb-0"><?= $approved_count ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Rejected</h5>
                    <h2 class="mb-0"><?= $rejected_count ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservations Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Lab</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $row): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($row['date'])) ?></td>
                            <td><?= date('h:i A', strtotime($row['time_slot'])) ?></td>
                            <td><?= htmlspecialchars($row['idno']) ?></td>
                            <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td><?= htmlspecialchars($row['lab']) ?></td>
                            <td><?= htmlspecialchars($row['purpose']) ?></td>
                            <td>
                                <?php
                                $status_class = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger'
                                ][$row['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $status_class ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#updateStatusModal<?= $row['id'] ?>">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                            </td>
                        </tr>

                        <!-- Update Status Modal -->
                        <div class="modal fade" id="updateStatusModal<?= $row['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Reservation Status</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="reservation_id" value="<?= $row['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="approved" <?= $row['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                                    <option value="rejected" <?= $row['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Admin Notes</label>
                                                <textarea name="admin_notes" class="form-control" rows="3"><?= htmlspecialchars($row['admin_notes'] ?? '') ?></textarea>
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
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 