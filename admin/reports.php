<?php
require_once 'includes/header.php';

// Get date range from request or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get sit-in statistics
$sit_in_stats = $conn->query("SELECT 
    COUNT(*) as total_sit_ins,
    COUNT(DISTINCT user_id) as unique_students,
    AVG(TIMESTAMPDIFF(MINUTE, time_in, IFNULL(time_out, NOW()))) as avg_duration
    FROM sit_in_records 
    WHERE date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc();

// Get lab usage statistics
$lab_stats = $conn->query("SELECT 
    lab,
    COUNT(*) as total_visits,
    COUNT(DISTINCT user_id) as unique_students,
    AVG(TIMESTAMPDIFF(MINUTE, time_in, IFNULL(time_out, NOW()))) as avg_duration
    FROM sit_in_records 
    WHERE date BETWEEN '$start_date' AND '$end_date'
    GROUP BY lab
    ORDER BY total_visits DESC");

// Get peak hours
$peak_hours = $conn->query("SELECT 
    HOUR(time_in) as hour,
    COUNT(*) as total_visits
    FROM sit_in_records 
    WHERE date BETWEEN '$start_date' AND '$end_date'
    GROUP BY HOUR(time_in)
    ORDER BY total_visits DESC");

// Fetch per purpose statistics
$purpose_stats = $conn->query("SELECT purpose, COUNT(*) as total FROM sit_in_records WHERE date BETWEEN '$start_date' AND '$end_date' GROUP BY purpose ORDER BY total DESC");
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reports</h2>
        <div>
            <form class="d-flex gap-2" method="get" action="">
                <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
            <form class="d-inline" method="get" action="generate_report_pdf.php">
                <input type="hidden" name="start_date" value="<?= $start_date ?>">
                <input type="hidden" name="end_date" value="<?= $end_date ?>">
                <button type="submit" class="btn btn-danger ms-2">Download PDF</button>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sit-ins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $sit_in_stats['total_sit_ins'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Average Duration</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= round($sit_in_stats['avg_duration'] / 60, 1) ?> hours
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lab Usage Statistics -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lab Usage Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Lab</th>
                                    <th>Total Visits</th>
                                    <th>Avg Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($lab = $lab_stats->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($lab['lab']) ?></td>
                                    <td><?= $lab['total_visits'] ?></td>
                                    <td><?= round($lab['avg_duration'] / 60, 1) ?> hours</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Per Purpose Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Purpose</th>
                                    <th>Total Sit-ins</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($purpose = $purpose_stats->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($purpose['purpose']) ?></td>
                                    <td><?= $purpose['total'] ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Peak Hours -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Peak Hours</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Hour</th>
                                    <th>Total Visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($hour = $peak_hours->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('h:i A', strtotime($hour['hour'] . ':00')) ?></td>
                                    <td><?= $hour['total_visits'] ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 