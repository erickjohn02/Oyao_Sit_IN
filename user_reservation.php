<?php
date_default_timezone_set('Asia/Manila');
session_start();
include 'db_connect.php';
include 'admin/includes/functions.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
// Fetch user details
$query = "SELECT id, idno, firstname, lastname, remaining_sessions FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $idno = $user['idno'];
    $fullname = $user['firstname'] . ' ' . $user['lastname'];
    $remainingSessions = $user['remaining_sessions'];
} else {
    header("Location: login.php");
    exit();
}
$stmt->close();

// Handle reservation submission
if (isset($_POST['reserve'])) {
    $purpose = sanitize_input($_POST['purpose']);
    $lab = sanitize_input($_POST['lab']);
    $pc = sanitize_input($_POST['pc']);
    $date = sanitize_input($_POST['date']);
    $time_in = sanitize_input($_POST['time_in']);

    // Prevent reservation for a past time
    $now = new DateTime();
    $reservation_dt = new DateTime($date . ' ' . $time_in);
    if ($reservation_dt < $now) {
        echo "<script>alert('You cannot reserve for a past time.'); window.location.href='user_reservation.php';</script>";
        exit();
    }
    // Insert into reservations table
    $query = "INSERT INTO reservations (user_id, date, time_slot, lab, purpose, status, admin_notes) VALUES (?, ?, ?, ?, ?, 'pending', ?)";
    $stmt = $conn->prepare($query);
    $admin_notes = 'PC: PC' . $pc;
    $stmt->bind_param("isssss", $user_id, $date, $time_in, $lab, $purpose, $admin_notes);
    if ($stmt->execute()) {
        echo "<script>alert('Reservation submitted and waiting for approval.'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error submitting reservation!');</script>";
    }
}

// Labs and PCs
$labs = ['524', '526', '530'];
$selected_lab = isset($_POST['lab']) ? $_POST['lab'] : $labs[0];

// Fetch available PCs for the selected lab
$available_pcs = [];
$res = $conn->query("SELECT pc_number, status FROM lab_pcs WHERE lab='{$selected_lab}'");
while ($row = $res->fetch_assoc()) {
    if ($row['status'] === 'available') {
        $available_pcs[] = $row['pc_number'];
    }
}
// If there are no records for this lab in lab_pcs, show all PCs
$has_lab_pcs = $conn->query("SELECT COUNT(*) as cnt FROM lab_pcs WHERE lab='{$selected_lab}'")->fetch_assoc()['cnt'];
if ($has_lab_pcs == 0) {
    $available_pcs = range(1, 50);
}

// Get selected date and time from POST or use today's date and current time as default
$selected_date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$selected_time = isset($_POST['time_in']) ? $_POST['time_in'] : date('H:i');

// Fetch already reserved PCs for the selected lab, date, and time
$reserved_pcs = [];
$res = $conn->query("SELECT admin_notes FROM reservations WHERE lab='{$selected_lab}' AND date='{$selected_date}' AND time_slot='{$selected_time}' AND status IN ('pending','approved')");
while ($row = $res->fetch_assoc()) {
    if (preg_match('/PC: PC(\d+)/', $row['admin_notes'], $matches)) {
        $reserved_pcs[] = (int)$matches[1];
    }
}

// Remove reserved PCs from available_pcs
$available_pcs = array_diff($available_pcs, $reserved_pcs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve a Computer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Reserve a Computer</h2>
    <form method="POST" class="card p-4 mt-3">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">ID Number</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($idno) ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Student Name</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($fullname) ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Remaining Sessions</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($remainingSessions) ?>" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Purpose</label>
                <select name="purpose" class="form-select" required>
                    <option value="C Programming">C Programming</option>
                    <option value="Java Programming">Java Programming</option>
                    <option value="Python Programming">Python Programming</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Laboratory</label>
                <select name="lab" class="form-select" required onchange="this.form.submit()">
                    <?php foreach ($labs as $lab): ?>
                        <option value="<?= $lab ?>" <?= $selected_lab == $lab ? 'selected' : '' ?>>Lab <?= $lab ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">PC Number</label>
                <select name="pc" class="form-select" required <?= empty($available_pcs) ? 'disabled' : '' ?>">
                    <?php if (empty($available_pcs)): ?>
                        <option value="">No available PCs</option>
                    <?php else: ?>
                        <?php foreach ($available_pcs as $pc): ?>
                            <option value="<?= $pc ?>">PC<?= $pc ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" required value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Time In</label>
                <input type="time" name="time_in" class="form-control" required value="<?= date('H:i') ?>" min="<?= ($selected_date == date('Y-m-d')) ? date('H:i') : '' ?>">
            </div>
        </div>
        <div class="text-end">
            <button type="submit" name="reserve" class="btn btn-primary">Reserve</button>
        </div>
    </form>
</div>
</body>
</html> 