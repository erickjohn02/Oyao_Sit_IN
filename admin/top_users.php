<?php
require_once 'includes/header.php';

// Handle date filter
$start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : date('Y-m-d');
$course_filter = isset($_GET['course']) ? sanitize_input($_GET['course']) : '';

// Get unique courses for filter
$courses_query = "SELECT DISTINCT course FROM users WHERE role = 'student' ORDER BY course";
$courses = $conn->query($courses_query);

// Build query for top users based on sit-in duration
$query = "SELECT 
            u.id,
            u.idno,
            u.firstname,
            u.lastname,
            u.course,
            COUNT(s.id) as total_sit_ins,
            SUM(TIMESTAMPDIFF(MINUTE, s.time_in, COALESCE(s.time_out, NOW()))) as total_minutes,
            u.remaining_sessions
          FROM users u
          LEFT JOIN sit_in_records s ON u.id = s.user_id 
          AND s.date BETWEEN ? AND ?
          WHERE u.role = 'student'";

$params = [$start_date, $end_date];
$types = "ss";

if ($course_filter) {
    $query .= " AND u.course = ?";
    $params[] = $course_filter;
    $types .= "s";
}

$query .= " GROUP BY u.id
            ORDER BY total_minutes DESC
            LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$top_users = $stmt->get_result();

// Calculate overall statistics
$stats_query = "SELECT 
    COUNT(DISTINCT u.id) as total_students,
    COUNT(DISTINCT CASE WHEN s.id IS NOT NULL THEN u.id END) as active_students,
    COUNT(s.id) as total_sit_ins,
    AVG(TIMESTAMPDIFF(MINUTE, s.time_in, COALESCE(s.time_out, NOW()))) as avg_duration
FROM users u
LEFT JOIN sit_in_records s ON u.id = s.user_id 
AND s.date BETWEEN ? AND ?
WHERE u.role = 'student'";

if ($course_filter) {
    $stats_query .= " AND u.course = ?";
}

$stmt = $conn->prepare($stats_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get most used labs
$labs_query = "SELECT 
    lab,
    COUNT(*) as usage_count,
    AVG(TIMESTAMPDIFF(MINUTE, time_in, COALESCE(time_out, NOW()))) as avg_duration
FROM sit_in_records
WHERE date BETWEEN ? AND ?
GROUP BY lab
ORDER BY usage_count DESC
LIMIT 5";

$stmt = $conn->prepare($labs_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$top_labs = $stmt->get_result();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Top Users & Usage Statistics</h2>
        <div>
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                <select name="course" class="form-select">
                    <option value="">All Courses</option>
                    <?php while($course = $courses->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($course['course']) ?>" <?= $course_filter === $course['course'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course['course']) ?>
                    </option>
                    <?php endwhile; ?>
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
                    <h5 class="card-title">Total Students</h5>
                    <h2 class="card-text"><?= $stats['total_students'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Students</h5>
                    <h2 class="card-text"><?= $stats['active_students'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Sit-ins</h5>
                    <h2 class="card-text"><?= $stats['total_sit_ins'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Avg. Duration</h5>
                    <h2 class="card-text"><?= round($stats['avg_duration'] / 60, 1) ?> hrs</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Users Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Users by Usage</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped datatable">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>ID Number</th>
                                    <th>Name</th>
                                    <th>Course</th>
                                    <th>Total Sit-ins</th>
                                    <th>Total Hours</th>
                                    <th>Remaining Sessions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                while($user = $top_users->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?= $rank++ ?></td>
                                    <td><?= htmlspecialchars($user['idno']) ?></td>
                                    <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                                    <td><?= htmlspecialchars($user['course']) ?></td>
                                    <td><?= $user['total_sit_ins'] ?></td>
                                    <td><?= round($user['total_minutes'] / 60, 1) ?></td>
                                    <td><?= $user['remaining_sessions'] ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Used Labs -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Most Used Labs</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php while($lab = $top_labs->fetch_assoc()): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-1"><?= htmlspecialchars($lab['lab']) ?></h6>
                                <span class="badge bg-primary rounded-pill"><?= $lab['usage_count'] ?> uses</span>
                            </div>
                            <small class="text-muted">
                                Avg. Duration: <?= round($lab['avg_duration'] / 60, 1) ?> hours
                            </small>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 