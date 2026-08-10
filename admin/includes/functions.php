<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_admin() {
    if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }
}

function get_lab_status_badge($status) {
    $badge_class = '';
    switch ($status) {
        case 'available':
            $badge_class = 'success';
            break;
        case 'maintenance':
            $badge_class = 'warning';
            break;
        case 'unavailable':
            $badge_class = 'danger';
            break;
        default:
            $badge_class = 'secondary';
    }
    return "<span class='badge bg-{$badge_class}'>" . ucfirst($status) . "</span>";
}

function get_feedback_status_badge($status) {
    $badge_class = '';
    switch ($status) {
        case 'pending':
            $badge_class = 'warning';
            break;
        case 'responded':
            $badge_class = 'info';
            break;
        case 'resolved':
            $badge_class = 'success';
            break;
        default:
            $badge_class = 'secondary';
    }
    return "<span class='badge bg-{$badge_class}'>" . ucfirst($status) . "</span>";
}

function format_datetime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

function format_date($date) {
    return date('M d, Y', strtotime($date));
}

function format_time($time) {
    return date('h:i A', strtotime($time));
}

function calculate_duration($time_in, $time_out = null) {
    if (!$time_out) {
        $time_out = date('Y-m-d H:i:s');
    }
    $duration = strtotime($time_out) - strtotime($time_in);
    $hours = floor($duration / 3600);
    $minutes = floor(($duration % 3600) / 60);
    return sprintf("%02d:%02d", $hours, $minutes);
}
?> 