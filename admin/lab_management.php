<?php
require_once 'includes/header.php';

// Ensure lab_pcs table exists
$conn->query("CREATE TABLE IF NOT EXISTS lab_pcs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lab VARCHAR(10) NOT NULL,
    pc_number INT NOT NULL,
    status ENUM('available','used') NOT NULL DEFAULT 'available',
    UNIQUE KEY (lab, pc_number)
) ENGINE=InnoDB");

// Labs
$labs = ['524', '526', '530'];
$selected_lab = isset($_GET['lab']) && in_array($_GET['lab'], $labs) ? $_GET['lab'] : $labs[0];
$status_filter = isset($_GET['status']) && in_array($_GET['status'], ['available','used']) ? $_GET['status'] : '';

// Ensure all 50 PCs for all labs exist in lab_pcs
foreach ($labs as $lab) {
    foreach (range(1, 50) as $pc_num) {
        $conn->query("INSERT IGNORE INTO lab_pcs (lab, pc_number, status) VALUES ('{$lab}', {$pc_num}, 'available')");
    }
}

// Handle status update
if (isset($_POST['update_status'])) {
    $lab = sanitize_input($_POST['lab']);
    $pcs = isset($_POST['pcs']) ? $_POST['pcs'] : [];
    $status = sanitize_input($_POST['update_status']);
    if ($status === 'set_all_available') {
        // Set all PCs in the lab to available
        $conn->query("UPDATE lab_pcs SET status='available' WHERE lab='{$lab}'");
        echo "<script>alert('All PCs set as available!'); window.location.href='lab_management.php?lab={$lab}';</script>";
    } else {
        if (empty($pcs)) {
            echo "<script>alert('Please select at least one PC.'); window.location.href='lab_management.php?lab={$lab}';</script>";
            exit;
        }
        foreach ($pcs as $pc) {
            $pc_num = intval($pc);
            if (!$conn->query("INSERT INTO lab_pcs (lab, pc_number, status) VALUES ('{$lab}', {$pc_num}, '{$status}') ON DUPLICATE KEY UPDATE status='{$status}'")) {
                echo "Error updating PC{$pc_num}: " . $conn->error . "<br>";
            }
        }
        echo "<script>alert('PC status updated!'); window.location.href='lab_management.php?lab={$lab}';</script>";
    }
}

// Fetch PC statuses
$pc_statuses = [];
$res = $conn->query("SELECT pc_number, status FROM lab_pcs WHERE lab='{$selected_lab}'");
while ($row = $res->fetch_assoc()) {
    $pc_statuses[$row['pc_number']] = $row['status'];
}

// Filtered PCs
$pcs = range(1, 50);
if ($status_filter) {
    $pcs = array_filter($pcs, function($pc) use ($pc_statuses, $status_filter) {
        return (isset($pc_statuses[$pc]) ? $pc_statuses[$pc] : 'available') === $status_filter;
    });
}
?>
<div class="container mt-4">
    <h2>Computer Control</h2>
    <form method="GET" class="mb-3 d-flex gap-2 align-items-center">
        <label class="form-label mb-0">Lab:</label>
        <select name="lab" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach ($labs as $lab): ?>
                <option value="<?= $lab ?>" <?= $selected_lab == $lab ? 'selected' : '' ?>>Lab <?= $lab ?></option>
            <?php endforeach; ?>
        </select>
        <label class="form-label mb-0 ms-3">Filter:</label>
        <button type="submit" name="status" value="available" class="btn btn-success btn-sm <?= $status_filter=='available'?'fw-bold':'' ?>">Available</button>
        <button type="submit" name="status" value="used" class="btn btn-danger btn-sm <?= $status_filter=='used'?'fw-bold':'' ?>">Used</button>
        <a href="lab_management.php?lab=<?= $selected_lab ?>" class="btn btn-secondary btn-sm">Show All</a>
    </form>
    <!-- Set all PCs as available button -->
    <form method="POST" style="display:inline; margin-bottom: 1rem;">
        <input type="hidden" name="lab" value="<?= $selected_lab ?>">
        <button type="submit" name="update_status" value="set_all_available" class="btn btn-primary btn-sm ms-2">Set All PCs as Available</button>
    </form>
    <form method="POST">
        <input type="hidden" name="lab" value="<?= $selected_lab ?>">
        <div class="row">
            <?php foreach (range(1, 50) as $pc):
                $status = isset($pc_statuses[$pc]) ? $pc_statuses[$pc] : 'available';
                if ($status_filter && $status !== $status_filter) continue;
            ?>
            <div class="col-2 mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="pcs[]" value="<?= $pc ?>" id="pc<?= $pc ?>">
                    <label class="form-check-label" for="pc<?= $pc ?>">
                        PC<?= $pc ?>
                        <span class="badge bg-<?= $status=='available'?'success':'danger' ?> ms-1"><?= ucfirst($status) ?></span>
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-3">
            <button type="submit" name="update_status" value="available" class="btn btn-success">Set as Available</button>
            <button type="submit" name="update_status" value="used" class="btn btn-danger">Set as Used</button>
        </div>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?> 