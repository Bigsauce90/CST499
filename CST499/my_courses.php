<?php

require 'db.php';
require 'functions.php';

need_login();

$studentId = $_SESSION['student_id'];

$stmt = $conn->prepare(
    "SELECT
        e.enrollment_id,
        o.offering_id,
        c.course_code,
        c.course_name,
        s.semester_name,
        s.year
     FROM enrollments e
     JOIN offerings o
        ON e.offering_id = o.offering_id
     JOIN courses c
        ON o.course_id = c.course_id
     JOIN semesters s
        ON o.semester_id = s.semester_id
     WHERE e.student_id = ?
     ORDER BY c.course_code"
);

$stmt->bind_param("i", $studentId);

$stmt->execute();

$courses = $stmt->get_result();

page_top('My Courses');

?>

<h1>My Courses</h1>

<p>
    <a href="dashboard.php">Back to Dashboard</a>
</p>

<p>
    <a href="courses.php">Add More Courses</a>
</p>


<?php if (isset($_SESSION['message'])): ?>

    <p>
        <?php
        echo clean($_SESSION['message']);
        unset($_SESSION['message']);
        ?>
    </p>

<?php endif; ?>


<?php if ($courses->num_rows > 0): ?>

<table border="1" cellpadding="10">

    <tr>
        <th>Course</th>
        <th>Name</th>
        <th>Semester</th>
        <th>Year</th>
        <th>Action</th>
    </tr>


    <?php while ($course = $courses->fetch_assoc()): ?>

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

                <form method="post" action="drop_course.php">

                    <input
                        type="hidden"
                        name="enrollment_id"
                        value="<?php echo $course['enrollment_id']; ?>"
                    >

                    <button type="submit">
                        Drop Course
                    </button>

                </form>

            </td>

        </tr>

    <?php endwhile; ?>

</table>


<?php else: ?>

    <p>You are not enrolled in any courses.</p>

<?php endif; ?>


<?php

$stmt->close();

page_bottom();

?>