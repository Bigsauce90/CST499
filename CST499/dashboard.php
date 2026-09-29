<?php

require 'db.php';
require 'functions.php';

need_login();

$studentId = $_SESSION['student_id'];

$stmt = $conn->prepare(
    "SELECT student_id, user_id, name, phone, email, program
     FROM students
     WHERE student_id = ?"
);

$stmt->bind_param(
    "i",
    $studentId
);

$stmt->execute();

$result = $stmt->get_result();

$student = $result->fetch_assoc();

$stmt->close();

if (!$student) {
    session_destroy();

    header('Location: login.php');
    exit;
}

$notifications = null;

$notificationsStmt = $conn->prepare(
    "SELECT message FROM notifications WHERE student_id = ? ORDER BY created_at DESC"
);

if ($notificationsStmt) {
    $notificationsStmt->bind_param("i", $studentId);
    $notificationsStmt->execute();
    $notifications = $notificationsStmt->get_result();
    $notificationsStmt->close();
}

page_top('Dashboard');

?>

<h1>Student Dashboard</h1>

<?php if ($notifications->num_rows > 0): ?>

    <div class="notifications">

        <h2>Notifications</h2>

        <?php while (
            $notification =
            $notifications->fetch_assoc()
        ): ?>

            <p>

                <strong>Course Update:</strong>

                <?php
                echo clean(
                    $notification['message']
                );
                ?>

                <br>

                <a href="courses.php">
                    View Course
                </a>

            </p>

        <?php endwhile; ?>

    </div>

<?php endif; ?>

<h2>Welcome, <?php echo clean($student['name']); ?></h2>

<div class="profile">

    <p>
        <strong>Student ID:</strong>
        <?php echo clean($student['student_id']); ?>
    </p>

    <p>
        <strong>User ID:</strong>
        <?php echo clean($student['user_id']); ?>
    </p>

    <p>
        <strong>Name:</strong>
        <?php echo clean($student['name']); ?>
    </p>

    <p>
        <strong>Phone:</strong>
        <?php echo clean($student['phone']); ?>
    </p>

    <p>
        <strong>Email:</strong>
        <?php echo clean($student['email']); ?>
    </p>

    <p>
        <strong>Program:</strong>
        <?php echo clean($student['program']); ?>
    </p>

</div>

<h2>Course Options</h2>

<p>
    <a href="courses.php">View Available Courses</a>
</p>

<p>
    <a href="my_courses.php">View My Courses</a>
</p>

<p>
    <a href="logout.php">Logout</a>
</p>

<?php page_bottom(); ?>