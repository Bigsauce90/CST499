<?php

require 'db.php';
require 'functions.php';

if (logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$message = '';

$userId = '';
$name = '';
$phone = '';
$email = '';
$program = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $program = trim($_POST['program'] ?? '');

    if (
        $userId == ''
        || $password == ''
        || $name == ''
        || $phone == ''
        || $email == ''
        || $program == ''
    ) {
        $message = 'Please complete every field.';
    }

    elseif (strlen($userId) < 4 || strlen($userId) > 30) {
        $message = 'The User ID must have 4 to 30 characters.';
    }

    elseif (strlen($password) < 8) {
        $message = 'The password must have at least 8 characters.';
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    }

    else {

        $check = $conn->prepare(
            "SELECT student_id
             FROM students
             WHERE user_id = ?
             OR email = ?"
        );

        $check->bind_param(
            "ss",
            $userId,
            $email
        );

        $check->execute();

        $result = $check->get_result();

        $existingUser = $result->fetch_assoc();

        $check->close();

        if ($existingUser) {

            $message = 'That User ID or email is already being used.';

        } else {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $insert = $conn->prepare(
                "INSERT INTO students
                (user_id, password_hash, name, phone, email, program)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $insert->bind_param(
                "ssssss",
                $userId,
                $passwordHash,
                $name,
                $phone,
                $email,
                $program
            );

            $insert->execute();

            $insert->close();

            $_SESSION['message'] =
                'Your account was created. You can now log in.';

            header('Location: login.php');
            exit;
        }
    }
}

page_top('Register');

?>

<h1>Student Registration</h1>

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
        value="<?php echo clean($userId); ?>"
        required
    >

    <label>Password</label>

    <input
        type="password"
        name="password"
        minlength="8"
        required
    >

    <label>Full Name</label>

    <input
        type="text"
        name="name"
        value="<?php echo clean($name); ?>"
        required
    >

    <label>Phone</label>

    <input
        type="text"
        name="phone"
        value="<?php echo clean($phone); ?>"
        required
    >

    <label>Email</label>

    <input
        type="email"
        name="email"
        value="<?php echo clean($email); ?>"
        required
    >

    <label>Program</label>

    <input
        type="text"
        name="program"
        value="<?php echo clean($program); ?>"
        required
    >

    <button type="submit">
        Register
    </button>

</form>

<?php page_bottom(); ?>