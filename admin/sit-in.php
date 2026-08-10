<?php
require_once 'includes/header.php';

// Handle sit-in submission
if (isset($_POST['sit_in'])) {
    $idno = sanitize_input($_POST['idno']);
    $name = sanitize_input($_POST['name']);
    $purpose = sanitize_input($_POST['purpose']);
    $lab = sanitize_input($_POST['lab']);

    // Fetch remaining sessions
    $query = "SELECT remaining_sessions FROM users WHERE idno = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $idno);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['remaining_sessions'] > 0) {
        $session = $user['remaining_sessions'] - 1;
        $time_in = date("H:i:s");
        $date = date("Y-m-d");

        // Insert sit-in record
        $query = "INSERT INTO sit_in_records (idno, name, purpose, lab, session, time_in, date) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $idno, $name, $purpose, $lab, $session, $time_in, $date);
        
        if ($stmt->execute()) {
            // Update remaining sessions
            $query = "UPDATE users SET remaining_sessions = ? WHERE idno = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $session, $idno);
            $stmt->execute();
            
            echo "<script>alert('Sit-in recorded successfully!');</script>";
        } else {
            echo "<script>alert('Error recording sit-in!');</script>";
        }
    } else {
        echo "<script>alert('Student has no remaining sessions!');</script>";
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
$query = "SELECT * FROM sit_in_records WHERE time_out IS NULL ORDER BY time_in DESC";
$current_sit_ins = $conn->query($query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sit-in Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchStudentModal">
            <i class="fas fa-plus"></i> New Sit-in
        </button>
    </div>

    <!-- Current Sit-ins Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Current Sit-ins</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Purpose</th>
                            <th>Lab</th>
                            <th>Time In</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($sit_in = $current_sit_ins->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($sit_in['idno']) ?></td>
                            <td><?= htmlspecialchars($sit_in['name']) ?></td>
                            <td><?= htmlspecialchars($sit_in['purpose']) ?></td>
                            <td><?= htmlspecialchars($sit_in['lab']) ?></td>
                            <td><?= htmlspecialchars($sit_in['time_in']) ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="sit_id" value="<?= htmlspecialchars($sit_in['id']) ?>">
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

<!-- Search Student Modal -->
<div class="modal fade" id="searchStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Enter ID Number or Name">
                </div>
                <button class="btn btn-primary" onclick="searchStudent()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sit-in Form Modal -->
<div class="modal fade" id="sitInFormModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sit-in Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="sitInForm">
                    <div class="mb-3">
                        <label class="form-label">ID Number</label>
                        <input type="text" id="idno" name="idno" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student Name</label>
                        <input type="text" id="studentName" name="name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purpose</label>
                        <select name="purpose" class="form-select" required>
                            <option value="">Select Purpose</option>
                            <option value="C Programming">C Programming</option>
                            <option value="Database Management">Database Management</option>
                            <option value="Web Development">Web Development</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lab</label>
                        <input type="text" name="lab" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remaining Sessions</label>
                        <input type="text" id="remainingSessions" class="form-control" readonly>
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

<script>
function searchStudent() {
    const searchInput = document.getElementById('searchInput').value;
    
    // Make AJAX call to search student
    fetch('search_student.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'search=' + encodeURIComponent(searchInput)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate the sit-in form
            document.getElementById('idno').value = data.student.idno;
            document.getElementById('studentName').value = data.student.name;
            document.getElementById('remainingSessions').value = data.student.remaining_sessions;
            
            // Close search modal and open sit-in form
            bootstrap.Modal.getInstance(document.getElementById('searchStudentModal')).hide();
            new bootstrap.Modal(document.getElementById('sitInFormModal')).show();
        } else {
            alert('Student not found!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error searching for student!');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?> 