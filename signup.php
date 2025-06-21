<?php
require_once 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($full_name) || empty($email) || empty($password) || empty($contact_number) || empty($role)) {
        $message = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, contact_number, role) VALUES (?, ?, ?, ?, ?)");
            try {
                $stmt->execute([$full_name, $email, $hashed_password, $contact_number, $role]);
                $message = 'Sign up successful! Please log in.';
                header('Location: login.php');
                exit;
            } catch (PDOException $e) {
                $message = 'Sign up failed: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCMIRT Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="search.php">Search Books</a>
        <a href="profile.php">Profile</a>
        <a href="login.php">Login</a>
        <a href="#">Sign Up</a>
    </div>

    <div class="container">
        <h2>SCMIRT Library Sign Up</h2>
        <?php if ($message): ?>
            <p style="color: <?php echo strpos($message, 'successful') !== false ? 'green' : 'red'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="tel" name="contact_number" placeholder="Contact Number" required>
            <input type="email" name="email" placeholder="Email ID" required>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="admin">Admin</option>
            </select>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Sign Up</button>
        </form>
        <a href="login.php">Already have an account? Login</a>
    </div>
</body>
</html>