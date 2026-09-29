<?php

require 'functions.php';

page_top(title:'Home');
 
?>

<h1>Online Course Enrollment System</h1>

<p>
    This basic project allows students to register,
    log in, view courses, enroll, and join a waiting list.
</p>

<?php if (logged_in()): ?>

    <a class='button' href='courses.php'>
        View Courses
    </a>

<?php else: ?>

    <a class='button' href='register.php'>
        Create a new Profile
    </a>

<?php endif; ?>

<?php page_bottom(); ?>