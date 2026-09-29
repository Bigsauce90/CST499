<?php

require 'db.php';
require 'functions.php';

need_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: courses.php');
    exit;
}


$studentId = $_SESSION['student_id'];

$offeringId =
    intval($_POST['offering_id'] ?? 0);


if ($offeringId <= 0) {

    $_SESSION['message'] =
        'Invalid course.';

    header('Location: courses.php');
    exit;
}


$stmt = $conn->prepare(
    "SELECT enrollment_id
     FROM enrollments
     WHERE student_id = ?
     AND offering_id = ?"
);

$stmt->bind_param(
    "ii",
    $studentId,
    $offeringId
);

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
    "SELECT notification_id
     FROM notifications
     WHERE student_id = ?
     AND offering_id = ?
     AND notification_type = 'seat_offer'
     AND used = 0
     LIMIT 1"
);

$stmt->bind_param(
    "ii",
    $studentId,
    $offeringId
);

$stmt->execute();

$seatOffer =
    $stmt->get_result()->fetch_assoc();

$stmt->close();


if ($seatOffer) {

    $stmt = $conn->prepare(
        "INSERT INTO enrollments
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


    $stmt = $conn->prepare(
        "UPDATE notifications
         SET used = 1,
             is_read = 1
         WHERE notification_id = ?"
    );

    $stmt->bind_param(
        "i",
        $seatOffer['notification_id']
    );

    $stmt->execute();

    $stmt->close();


    $_SESSION['message'] =
        'You successfully enrolled in your reserved seat.';

    header('Location: my_courses.php');
    exit;
}


$stmt = $conn->prepare(
    "SELECT capacity, seats_taken
     FROM offerings
     WHERE offering_id = ?"
);

$stmt->bind_param(
    "i",
    $offeringId
);

$stmt->execute();

$offering =
    $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$offering) {

    $_SESSION['message'] =
        'Course could not be found.';

    header('Location: courses.php');
    exit;
}


if (
    $offering['seats_taken']
    >=
    $offering['capacity']
) {

    $_SESSION['message'] =
        'This course is full. Please join the waitlist.';

    header('Location: courses.php');
    exit;
}


$stmt = $conn->prepare(
    "INSERT INTO enrollments
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


$stmt = $conn->prepare(
    "UPDATE offerings
     SET seats_taken = seats_taken + 1
     WHERE offering_id = ?"
);

$stmt->bind_param(
    "i",
    $offeringId
);

$stmt->execute();

$stmt->close();


$_SESSION['message'] =
    'You successfully enrolled in the course.';


header('Location: my_courses.php');
exit;
