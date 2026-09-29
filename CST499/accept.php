<?php
require 'db.php';
require 'functions.php';

global $conn;

need_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['waitlist_id'])) {
        $waitlistId = $_POST['waitlist_id'];
        $stmt = $conn->prepare('update waitlist set status = "accepted" where id = ? and user_id = ?');
        $stmt->bind_param('ii', $waitlistId, $userId);
        $stmt->execute();
        header('Location: dashboard.php');
        exit;
    }

$userId = current_user_id();
$waitlistId = (int) $_POST['waitlist_id'];

$find = $conn->prepare('select offering_id from waitlist where id = ? and user_id = ? and status = "offered"');

$find->bind_param('ii', $waitlistId, $userId);
$find->execute();

$offer = $find->get_result()->fetch_assoc();

if (!$offer){
    $_SESSION['message'] = 'No offer found for this waitlist entry.';
    header('Location: dashboard.php');
    exit;
}

$insert = $conn->prepare('insert into enrollments (user_id, offering_id) values (?, ?)');
$insert->bind_param('ii', $userId, $offer['offering_id']);
$insert->execute();

$delete = $conn->prepare('delete from waitlist where id = ?');
$delete->bind_param('i', $waitlistId);
$delete->execute();

$_SESSION['message'] = 'You have been accepted into the course.';
header('Location: dashboard.php');
exit;
}
