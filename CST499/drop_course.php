<?php

require 'db.php';
require 'functions.php';

need_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: my_courses.php');
    exit;
}


$studentId = $_SESSION['student_id'];

$enrollmentId =
    intval($_POST['enrollment_id'] ?? 0);


if ($enrollmentId <= 0) {

    $_SESSION['message'] =
        'Invalid course.';

    header('Location: my_courses.php');
    exit;
}


$stmt = $conn->prepare(
    "SELECT
        e.offering_id,
        c.course_code,
        c.course_name
     FROM enrollments e
     JOIN offerings o
        ON e.offering_id = o.offering_id
     JOIN courses c
        ON o.course_id = c.course_id
     WHERE e.enrollment_id = ?
     AND e.student_id = ?"
);

$stmt->bind_param(
    "ii",
    $enrollmentId,
    $studentId
);

$stmt->execute();

$course =
    $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$course) {

    $_SESSION['message'] =
        'Course could not be found.';

    header('Location: my_courses.php');
    exit;
}


$offeringId = $course['offering_id'];


$stmt = $conn->prepare(
    "DELETE FROM enrollments
     WHERE enrollment_id = ?
     AND student_id = ?"
);

$stmt->bind_param(
    "ii",
    $enrollmentId,
    $studentId
);

$stmt->execute();

$stmt->close();


$stmt = $conn->prepare(
    "SELECT
        waitlist_id,
        student_id
     FROM waitlist
     WHERE offering_id = ?
     ORDER BY joined_at ASC
     LIMIT 1"
);

$stmt->bind_param(
    "i",
    $offeringId
);

$stmt->execute();

$waitingStudent =
    $stmt->get_result()->fetch_assoc();

$stmt->close();


if ($waitingStudent) {

    $waitingStudentId =
        $waitingStudent['student_id'];

    $waitlistId =
        $waitingStudent['waitlist_id'];


    $stmt = $conn->prepare(
        "DELETE FROM waitlist
         WHERE waitlist_id = ?"
    );

    $stmt->bind_param(
        "i",
        $waitlistId
    );

    $stmt->execute();

    $stmt->close();


    $message =
        'A seat has opened in '
        . $course['course_code']
        . ' - '
        . $course['course_name']
        . '. The seat is reserved for you.';


    $stmt = $conn->prepare(
        "INSERT INTO notifications
        (
            student_id,
            offering_id,
            notification_type,
            message,
            is_read,
            used
        )
        VALUES (?, ?, 'seat_offer', ?, 0, 0)"
    );

    $stmt->bind_param(
        "iis",
        $waitingStudentId,
        $offeringId,
        $message
    );

    $stmt->execute();

    $stmt->close();


    /*
       Do not lower seats_taken here.

       The seat is being held for
       the waiting student.
    */

} else {

    $stmt = $conn->prepare(
        "UPDATE offerings
         SET seats_taken =
             GREATEST(seats_taken - 1, 0)
         WHERE offering_id = ?"
    );

    $stmt->bind_param(
        "i",
        $offeringId
    );

    $stmt->execute();

    $stmt->close();
}


$_SESSION['message'] =
    'Course dropped successfully.';


header('Location: my_courses.php');
exit;
