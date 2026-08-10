<?php
require_once 'includes/header.php';

// Handle student status update
if (isset($_POST['update_status'])) {
    $user_id = sanitize_input($_POST['user_id']);
    $status = sanitize_input($_POST['status']);
    $remaining_sessions = sanitize_input($_POST['remaining_sessions']);

    $query = "UPDATE users SET status = ?, remaining_sessions = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $status, $remaining_sessions, $user_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Student status updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating student status!');</script>";
    }
}

// Handle search and filters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$course_filter = isset($_GET['course']) ? sanitize_input($_GET['course']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Build query based on filters
$query = "SELECT * FROM users WHERE role = 'student'";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (idno LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR email LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= "ssss";
}

if ($course_filter) {
    $query .= " AND course = ?";
    $params[] = $course_filter;
    $types .= "s";
}

if ($status_filter) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY lastname ASC, firstname ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();

// Get unique courses for filter
$courses_query = "SELECT DISTINCT course FROM users WHERE role = 'student' ORDER BY course";
$courses = $conn->query($courses_query);

// Calculate statistics
$total_students = $students->num_rows;
$active_students = 0;
$inactive_students = 0;
$total_sessions = 0;

$students->data_seek(0);
while ($student = $students->fetch_assoc()) {
    if ($student['status'] === 'active') {
        $active_students++;
    } else {
        $inactive_students++;
    }
    $total_sessions += $student['remaining_sessions'];
}
$students->data_seek(0);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Student Information</h2>
        <div>
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search students..." value="<?= htmlspecialchars($search) ?>">
                <select name="course" class="form-select">
                    <option value="">All Courses</option>
                    <?php while($course = $courses->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($course['course']) ?>" <?= $course_filter === $course['course'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course['course']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <h2 class="card-text"><?= $total_students ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Students</h5>
                    <h2 class="card-text"><?= $active_students ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Remaining Sessions</h5>
                    <h2 class="card-text"><?= $total_sessions ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Students List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Remaining Sessions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['idno']) ?></td>
                            <td><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td><?= htmlspecialchars($student['course']) ?></td>
                            <td>
                                <span class="badge bg-<?= $student['status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($student['status']) ?>
                                </span>
                            </td>
                            <td><?= $student['remaining_sessions'] ?></td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#updateStudentModal<?= $student['id'] ?>">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                            </td>
                        </tr>

                        <!-- Update Student Modal -->
                        <div class="modal fade" id="updateStudentModal<?= $student['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Student Status</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?= $student['id'] ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="active" <?= $student['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                    <option value="inactive" <?= $student['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Remaining Sessions</label>
                                                <input type="number" name="remaining_sessions" class="form-control" 
                                                       value="<?= $student['remaining_sessions'] ?>" required min="0">
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