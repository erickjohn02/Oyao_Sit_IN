<?php
require_once 'includes/header.php';

// Handle date filtering
$start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : date('Y-m-d');

// Fetch sit-in records with date filter
$query = "SELECT s.*, u.idno, u.firstname, u.lastname, u.course 
          FROM sit_in_records s 
          JOIN users u ON s.user_id = u.id 
          WHERE s.date BETWEEN ? AND ?
          ORDER BY s.date DESC, s.time_in DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

// Calculate statistics
$total_sit_ins = 0;
$total_hours = 0;
$records = [];
while($row = $result->fetch_assoc()) {
    $records[] = $row;
    $total_sit_ins++;
    
    if($row['time_out']) {
        $time_in = strtotime($row['time_in']);
        $time_out = strtotime($row['time_out']);
        $duration = ($time_out - $time_in) / 3600; // Convert to hours
        $total_hours += $duration;
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sit-in Records</h2>
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
            <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Sit-ins</h5>
                    <h2 class="mb-0"><?= $total_sit_ins ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Hours</h5>
                    <h2 class="mb-0"><?= number_format($total_hours, 1) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Sit-in Records Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Purpose</th>
                            <th>Lab</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($records as $row): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($row['date'])) ?></td>
                            <td><?= htmlspecialchars($row['idno']) ?></td>
                            <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td><?= htmlspecialchars($row['purpose']) ?></td>
                            <td><?= htmlspecialchars($row['lab']) ?></td>
                            <td><?= date('h:i A', strtotime($row['time_in'])) ?></td>
                            <td><?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-' ?></td>
                            <td>
                                <?php
                                if($row['time_out']) {
                                    $time_in = strtotime($row['time_in']);
                                    $time_out = strtotime($row['time_out']);
                                    $duration = ($time_out - $time_in) / 3600;
                                    echo number_format($duration, 1) . ' hours';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if($row['time_out']): ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 