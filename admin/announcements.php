<?php
require_once 'includes/header.php';

// Handle delete announcement
if (isset($_POST['delete_announcement'])) {
    $announcement_id = intval($_POST['announcement_id']);
    $query = "DELETE FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $announcement_id);
    if ($stmt->execute()) {
        echo "<script>alert('Announcement deleted successfully!'); window.location.href='announcements.php';</script>";
    } else {
        echo "<script>alert('Error deleting announcement!');</script>";
    }
}

// Handle new announcement submission
if (isset($_POST['add_announcement'])) {
    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);
    if (!empty($title) && !empty($content)) {
        $query = "INSERT INTO announcements (title, content) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $title, $content);
        if ($stmt->execute()) {
            echo "<script>alert('Announcement added successfully!'); window.location.href='announcements.php';</script>";
        } else {
            echo "<script>alert('Error adding announcement!');</script>";
        }
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}

// Fetch all announcements (most recent first)
$query = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Announcements</h2>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Create New Announcement</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="4" required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" name="add_announcement" class="btn btn-primary">Add Announcement</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Announcements</h5>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <?php while($row = $result->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <strong><?= htmlspecialchars($row['title']) ?> | <?= date('Y-M-d', strtotime($row['created_at'])) ?></strong><br>
                        <?= nl2br(htmlspecialchars($row['content'])) ?>
                    </div>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                        <input type="hidden" name="announcement_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete_announcement" class="btn btn-danger btn-sm ms-3">Delete</button>
                    </form>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?> 