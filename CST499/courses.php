<?php

require 'db.php';
require 'functions.php';

need_login();

$studentId = $_SESSION['student_id'];


$sql = "
    SELECT
        o.offering_id,
        o.capacity,
        o.seats_taken,
        c.course_code,
        c.course_name,
        s.semester_name,
        s.year
    FROM offerings o
    JOIN courses c
        ON o.course_id = c.course_id
    JOIN semesters s
        ON o.semester_id = s.semester_id
    ORDER BY s.year, c.course_code
";

$courses = $conn->query($sql);

page_top('Courses');

?>

<h1>Available Courses</h1>

<p>
    <a href="dashboard.php">Back to Dashboard</a>
</p>


<?php if (isset($_SESSION['message'])): ?>

    <p>
        <?php
        echo clean($_SESSION['message']);
        unset($_SESSION['message']);
        ?>
    </p>

<?php endif; ?>


<table border="1" cellpadding="10">

    <tr>
        <th>Course</th>
        <th>Name</th>
        <th>Semester</th>
        <th>Year</th>
        <th>Seats</th>
        <th>Action</th>
    </tr>


    <?php while ($course = $courses->fetch_assoc()): ?>

        <?php

        $offeringId = $course['offering_id'];

        $availableSeats =
            $course['capacity'] - $course['seats_taken'];


        $checkEnrollment = $conn->prepare(
            "SELECT enrollment_id
             FROM enrollments
             WHERE student_id = ?
             AND offering_id = ?"
        );

        $checkEnrollment->bind_param(
            "ii",
            $studentId,
            $offeringId
        );

        $checkEnrollment->execute();

        $enrolled =
            $checkEnrollment
            ->get_result()
            ->fetch_assoc();

        $checkEnrollment->close();


        $checkWaitlist = $conn->prepare(
            "SELECT waitlist_id
             FROM waitlist
             WHERE student_id = ?
             AND offering_id = ?"
        );

        $checkWaitlist->bind_param(
            "ii",
            $studentId,
            $offeringId
        );

        $checkWaitlist->execute();

        $waiting =
            $checkWaitlist
            ->get_result()
            ->fetch_assoc();

        $checkWaitlist->close();


        $checkNotification = $conn->prepare(
            "SELECT notification_id
             FROM notifications
             WHERE student_id = ?
             AND offering_id = ?
             AND notification_type = 'seat_offer'
             AND used = 0
             LIMIT 1"
        );

        $checkNotification->bind_param(
            "ii",
            $studentId,
            $offeringId
        );

        $checkNotification->execute();

        $reservedSeat =
            $checkNotification
            ->get_result()
            ->fetch_assoc();

        $checkNotification->close();

        ?>


        <tr>

            <td>
                <?php echo clean($course['course_code']); ?>
            </td>


            <td>
                <?php echo clean($course['course_name']); ?>
            </td>


            <td>
                <?php echo clean($course['semester_name']); ?>
            </td>


            <td>
                <?php echo clean($course['year']); ?>
            </td>


            <td>

                <?php

                if ($availableSeats > 0) {

                    echo $availableSeats . ' available';

                } else {

                    echo 'Full';
                }

                ?>

            </td>


            <td>


                <?php if ($enrolled): ?>

                    Enrolled


                <?php elseif ($reservedSeat): ?>

                    <form method="post" action="enroll.php">

                        <input
                            type="hidden"
                            name="offering_id"
                            value="<?php echo $offeringId; ?>"
                        >

                        <button type="submit">
                            Enroll - Seat Available
                        </button>

                    </form>


                <?php elseif ($availableSeats > 0): ?>

                    <form method="post" action="enroll.php">

                        <input
                            type="hidden"
                            name="offering_id"
                            value="<?php echo $offeringId; ?>"
                        >

                        <button type="submit">
                            Enroll
                        </button>

                    </form>


                <?php elseif ($waiting): ?>

                    On Waitlist


                <?php else: ?>

                    <form method="post" action="waitlist.php">

                        <input
                            type="hidden"
                            name="offering_id"
                            value="<?php echo $offeringId; ?>"
                        >

                        <button type="submit">
                            Join Waitlist
                        </button>

                    </form>

                <?php endif; ?>

            </td>

        </tr>


    <?php endwhile; ?>


</table>


<?php page_bottom(); ?>
