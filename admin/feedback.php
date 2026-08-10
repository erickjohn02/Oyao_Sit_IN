<?php
require_once 'includes/header.php';

// Get all feedback with user details
$query = "SELECT f.*, u.firstname, u.lastname, u.course 
          FROM feedback f 
          JOIN users u ON f.user_id = u.id 
          ORDER BY f.submitted_at DESC";
$result = $conn->query($query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Feedback Management</h2>
    </div>

    <!-- Feedback Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="feedbackTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Type</th>
                            <th>Feedback</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td><?= ucfirst($row['type']) ?></td>
                            <td><?= htmlspecialchars(substr($row['feedback_text'], 0, 50)) . '...' ?></td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'resolved' ? 'success' : ($row['status'] == 'responded' ? 'info' : 'warning') ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y h:i A', strtotime($row['submitted_at'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" onclick="viewFeedback(<?= $row['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if($row['status'] != 'resolved'): ?>
                                <button type="button" class="btn btn-sm btn-primary" onclick="respondToFeedback(<?= $row['id'] ?>)">
                                    <i class="fas fa-reply"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-success" onclick="resolveFeedback(<?= $row['id'] ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Feedback Modal -->
<div class="modal fade" id="viewFeedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Feedback Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="feedbackDetails"></div>
            </div>
        </div>
    </div>
</div>

<!-- Respond to Feedback Modal -->
<div class="modal fade" id="respondFeedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Respond to Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="respondFeedbackForm">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label class="form-label">Response</label>
                        <textarea class="form-control" name="admin_response" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitResponse()">Submit Response</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#feedbackTable').DataTable({
        order: [[5, 'desc']]
    });
});

function viewFeedback(id) {
    $.get('ajax/get_feedback.php', {id: id}, function(response) {
        $('#feedbackDetails').html(response);
        $('#viewFeedbackModal').modal('show');
    });
}

function respondToFeedback(id) {
    $.get('ajax/get_feedback.php', {id: id}, function(response) {
        const feedback = JSON.parse(response);
        $('#respondFeedbackForm [name="id"]').val(feedback.id);
        $('#respondFeedbackForm [name="admin_response"]').val(feedback.admin_response || '');
        $('#respondFeedbackModal').modal('show');
    });
}

function submitResponse() {
    $.post('ajax/respond_to_feedback.php', $('#respondFeedbackForm').serialize(), function(response) {
        if(response.success) {
            location.reload();
        } else {
            alert('Error submitting response: ' + response.message);
        }
    });
}

function resolveFeedback(id) {
    if(confirm('Are you sure you want to mark this feedback as resolved?')) {
        $.post('ajax/resolve_feedback.php', {id: id}, function(response) {
            if(response.success) {
                location.reload();
            } else {
                alert('Error resolving feedback: ' + response.message);
            }
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?> 