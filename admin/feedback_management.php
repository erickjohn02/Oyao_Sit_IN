<?php
require_once 'includes/header.php';

// Handle feedback response
if (isset($_POST['respond_to_feedback'])) {
    $feedback_id = sanitize_input($_POST['feedback_id']);
    $admin_response = sanitize_input($_POST['admin_response']);
    $status = sanitize_input($_POST['status']);

    $query = "UPDATE feedback SET admin_response = ?, status = ?, responded_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $admin_response, $status, $feedback_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Response submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error submitting response!');</script>";
    }
}

// Handle date filter
$start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : date('Y-m-d');
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$type_filter = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';

// Build query based on filters
$query = "SELECT f.*, u.firstname, u.lastname, u.course 
          FROM feedback f 
          JOIN users u ON f.user_id = u.id 
          WHERE f.submitted_at BETWEEN ? AND ?";
$params = [$start_date, $end_date];
$types = "ss";

if ($status_filter) {
    $query .= " AND f.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($type_filter) {
    $query .= " AND f.type = ?";
    $params[] = $type_filter;
    $types .= "s";
}

$query .= " ORDER BY f.submitted_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$feedback = $stmt->get_result();

// Calculate statistics
$total_feedback = $feedback->num_rows;
$pending_count = 0;
$responded_count = 0;
$resolved_count = 0;

$feedback->data_seek(0);
while ($item = $feedback->fetch_assoc()) {
    switch ($item['status']) {
        case 'pending':
            $pending_count++;
            break;
        case 'responded':
            $responded_count++;
            break;
        case 'resolved':
            $resolved_count++;
            break;
    }
}
$feedback->data_seek(0);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Feedback Management</h2>
        <div>
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="responded" <?= $status_filter === 'responded' ? 'selected' : '' ?>>Responded</option>
                    <option value="resolved" <?= $status_filter === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                </select>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="lab" <?= $type_filter === 'lab' ? 'selected' : '' ?>>Lab</option>
                    <option value="service" <?= $type_filter === 'service' ? 'selected' : '' ?>>Service</option>
                    <option value="other" <?= $type_filter === 'other' ? 'selected' : '' ?>>Other</option>
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
                    <h5 class="card-title">Total Feedback</h5>
                    <h2 class="card-text"><?= $total_feedback ?></h2>
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
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Responded</h5>
                    <h2 class="card-text"><?= $responded_count ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Resolved</h5>
                    <h2 class="card-text"><?= $resolved_count ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Student Feedback</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $feedback->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('M d, Y h:i A', strtotime($item['submitted_at'])) ?></td>
                            <td><?= htmlspecialchars($item['firstname'] . ' ' . $item['lastname']) ?></td>
                            <td><?= htmlspecialchars($item['course']) ?></td>
                            <td><?= ucfirst($item['type']) ?></td>
                            <td><?= htmlspecialchars($item['subject']) ?></td>
                            <td>
                                <button type="button" class="btn btn-link btn-sm p-0" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewMessageModal<?= $item['id'] ?>">
                                    View Message
                                </button>
                            </td>
                            <td>
                                <span class="badge bg-<?= 
                                    $item['status'] === 'resolved' ? 'success' : 
                                    ($item['status'] === 'responded' ? 'info' : 'warning') 
                                ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#respondModal<?= $item['id'] ?>">
                                    <i class="fas fa-reply"></i> Respond
                                </button>
                            </td>
                        </tr>

                        <!-- View Message Modal -->
                        <div class="modal fade" id="viewMessageModal<?= $item['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Feedback Message</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Message</label>
                                            <div class="p-3 bg-light rounded">
                                                <?= nl2br(htmlspecialchars($item['message'])) ?>
                                            </div>
                                        </div>
                                        <?php if ($item['admin_response']): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Admin Response</label>
                                            <div class="p-3 bg-light rounded">
                                                <?= nl2br(htmlspecialchars($item['admin_response'])) ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Respond Modal -->
                        <div class="modal fade" id="respondModal<?= $item['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Respond to Feedback</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="feedback_id" value="<?= $item['id'] ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="responded" <?= $item['status'] === 'responded' ? 'selected' : '' ?>>Responded</option>
                                                    <option value="resolved" <?= $item['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Response</label>
                                                <textarea name="admin_response" class="form-control" rows="4" required><?= htmlspecialchars($item['admin_response'] ?? '') ?></textarea>
                                            </div>
                                            
                                            <div class="text-end">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="respond_to_feedback" class="btn btn-primary">Submit Response</button>
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