<?php
require_once 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($email) || empty($password) || empty($role)) {
        $message = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            if ($role === 'student') {
                header('Location: student_dashboard.php');
            } else {
                header('Location: admin_dashboard.php');
            }
            exit;
        } else {
            $message = 'Invalid email, password, or role.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCMIRT Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="search.php">Search Books</a>
        <a href="profile.php">Profile</a>
        <a href="#">Login</a>
        <a href="signup.php">Sign Up</a>
    </div>

    <div class="container">
        <h2>SCMIRT Library Login</h2>
        <?php if ($message): ?>
            <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="role">Login as:</label>
            <select id="role" name="role">
                <option value="student">Student</option>
                <option value="admin">Admin</option>
            </select>
            <input type="text" name="email" placeholder="Email ID" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="signup.php" class="signup-link">Don't have an account? Sign Up</a>
    </div>
</body>
</html>