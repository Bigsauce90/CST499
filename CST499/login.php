<?php

require 'db.php';
require 'functions.php';

if (logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$message = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($userId == '' || $password == '') {

        $message = 'Please enter your User ID and password.';

    } else {

        $stmt = $conn->prepare(
            "SELECT student_id, user_id, password_hash, name
             FROM students
             WHERE user_id = ?"
        );

        $stmt->bind_param("s", $userId);

        $stmt->execute();

        $result = $stmt->get_result();

        $student = $result->fetch_assoc();

        $stmt->close();

        if (
            $student &&
            password_verify(
                $password,
                $student['password_hash']
            )
        ) {

            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['user_id'] = $student['user_id'];
            $_SESSION['name'] = $student['name'];

            header('Location: dashboard.php');
            exit;

        } else {

            $message = 'Incorrect User ID or password.';
        }
    }
}

page_top('Login');

?>

<h1>Student Login</h1>

<?php if ($message != ''): ?>

    <p class="error">
        <?php echo clean($message); ?>
    </p>

<?php endif; ?>

<form method="post">

    <label>User ID</label>

    <input
        type="text"
        name="user_id"
        required
    >

    <label>Password</label>

    <input
        type="password"
        name="password"
        required
    >

    <button type="submit">
        Login
    </button>

</form>

<?php page_bottom(); ?>