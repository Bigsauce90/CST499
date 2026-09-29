<?php

require 'db.php';
require 'functions.php';

need_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: courses.php');
    exit;
}

$studentId = $_SESSION['student_id'];
$offeringId = intval($_POST['offering_id'] ?? 0);

if ($offeringId <= 0) {
    $_SESSION['message'] = 'Invalid course.';
    header('Location: courses.php');
    exit;
}


$stmt = $conn->prepare(
    "SELECT enrollment_id
     FROM enrollments
     WHERE student_id = ?
     AND offering_id = ?"
);

$stmt->bind_param("ii", $studentId, $offeringId);

$stmt->execute();

$alreadyEnrolled =
    $stmt->get_result()->fetch_assoc();

$stmt->close();

if ($alreadyEnrolled) {

    $_SESSION['message'] =
        'You are already enrolled in this course.';

    header('Location: courses.php');
    exit;
}


$stmt = $conn->prepare(
    "SELECT waitlist_id
     FROM waitlist
     WHERE student_id = ?
     AND offering_id = ?"
);

$stmt->bind_param("ii", $studentId, $offeringId);

$stmt->execute();

$alreadyWaiting =
    $stmt->get_result()->fetch_assoc();

$stmt->close();

if ($alreadyWaiting) {

    $_SESSION['message'] =
        'You are already on the waitlist.';

    header('Location: courses.php');
    exit;
}


$stmt = $conn->prepare(
    "SELECT capacity, seats_taken
     FROM offerings
     WHERE offering_id = ?"
);

$stmt->bind_param("i", $offeringId);

$stmt->execute();

$offering =
    $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$offering) {

    $_SESSION['message'] =
        'Course offering was not found.';

    header('Location: courses.php');
    exit;
}


if ($offering['seats_taken'] < $offering['capacity']) {

    $_SESSION['message'] =
        'A seat is available. You can enroll instead.';

    header('Location: courses.php');
    exit;
}


$stmt = $conn->prepare(
    "INSERT INTO waitlist
     (student_id, offering_id)
     VALUES (?, ?)"
);

$stmt->bind_param(
    "ii",
    $studentId,
    $offeringId
);

$stmt->execute();

$stmt->close();

$_SESSION['message'] =
    'You were added to the waitlist.';

header('Location: courses.php');
exit;