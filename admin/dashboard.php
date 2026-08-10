<?php
require_once 'includes/header.php';

// Get total students
$total_students = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'];

// Get total labs
$total_labs = $conn->query("SELECT COUNT(*) as count FROM labs")->fetch_assoc()['count'];

// Get available labs
$available_labs = $conn->query("SELECT COUNT(*) as count FROM labs WHERE status = 'available'")->fetch_assoc()['count'];

// Get active sit-ins
$active_sitins = $conn->query("SELECT COUNT(*) as count FROM sit_in_records WHERE time_out IS NULL")->fetch_assoc()['count'];

// Get pending reservations
$pending_reservations = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'")->fetch_assoc()['count'];

// Get pending feedback
$pending_feedback = $conn->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'pending'")->fetch_assoc()['count'];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard</h2>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_students ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Available Labs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $available_labs ?> / <?= $total_labs ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-laptop fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Sit-ins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $active_sitins ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $pending_reservations + $pending_feedback ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Sit-ins</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Lab</th>
                                    <th>Time In</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent_sitins = $conn->query("SELECT s.*, u.firstname, u.lastname 
                                                             FROM sit_in_records s 
                                                             JOIN users u ON s.user_id = u.id 
                                                             WHERE s.time_out IS NULL 
                                                             ORDER BY s.date DESC, s.time_in DESC 
                                                             LIMIT 5");
                                while($sit_in = $recent_sitins->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($sit_in['firstname'] . ' ' . $sit_in['lastname']) ?></td>
                                    <td><?= htmlspecialchars($sit_in['lab']) ?></td>
                                    <td><?= date('h:i A', strtotime($sit_in['time_in'])) ?></td>
                                    <td><span class="badge bg-success">Active</span></td>
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
                    <h6 class="m-0 font-weight-bold text-primary">Pending Requests</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Student</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $pending_requests = $conn->query("SELECT 'Reservation' as type, r.date as created_at, u.firstname, u.lastname 
                                                                FROM reservations r 
                                                                JOIN users u ON r.user_id = u.id 
                                                                WHERE r.status = 'pending' 
                                                                UNION ALL 
                                                                SELECT 'Feedback' as type, f.submitted_at as created_at, u.firstname, u.lastname 
                                                                FROM feedback f 
                                                                JOIN users u ON f.user_id = u.id 
                                                                WHERE f.status = 'pending' 
                                                                ORDER BY created_at DESC 
                                                                LIMIT 5");
                                while($request = $pending_requests->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['type']) ?></td>
                                    <td><?= htmlspecialchars($request['firstname'] . ' ' . $request['lastname']) ?></td>
                                    <td><?= date('M d, Y', strtotime($request['created_at'])) ?></td>
                                    <td><span class="badge bg-warning">Pending</span></td>
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