<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function clean($text)
{
    return htmlspecialchars(
        $text,
        ENT_QUOTES,
        'UTF-8'
    );
}

function logged_in()
{
    return isset($_SESSION['student_id']);
}

function current_user_id()
{
    return isset($_SESSION['user_id_number'])
        ? (int)$_SESSION['user_id_number']
        : 0;
}

function need_login()
{
    if (!logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function page_top($title)
{
    $message = $_SESSION['message'] ?? '';

    unset($_SESSION['message']);
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>
            <?php echo clean($title); ?>
        </title>

        <link rel="stylesheet" href="style.css">
    </head>

    <body>

    <header>
        <h2>CST499 Course Enrollment System</h2>

        <nav>
            <a href="index.php">Home</a>

            <?php if (logged_in()): ?>

                <a href="courses.php">Courses</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>

            <?php else: ?>

                <a href="register.php">Register</a>
                <a href="login.php">Login</a>

            <?php endif; ?>
        </nav>
    </header>

    <main>

        <?php if ($message !== ''): ?>

            <p class="message">
                <?php echo clean($message); ?>
            </p>

        <?php endif; ?>

    <?php
}

function page_bottom()
{
    ?>

    </main>
    </body>
    </html>

    <?php
}