<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $full_name = $_POST['full_name'] ?? '';
        $contact_number = $_POST['contact_number'] ?? '';

        if (empty($full_name) || empty($contact_number)) {
            $message = 'All fields are required.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, contact_number = ? WHERE id = ?");
            try {
                $stmt->execute([$full_name, $contact_number, $user['id']]);
                $message = 'Profile updated successfully.';
                $user['full_name'] = $full_name;
                $user['contact_number'] = $contact_number;
                $_SESSION['user'] = $user;
            } catch (PDOException $e) {
                $message = 'Update failed: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $new_password = $_POST['new_password'] ?? '';
        if (empty($new_password)) {
            $message = 'New password is required.';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            try {
                $stmt->execute([$hashed_password, $user['id']]);
                $message = 'Password changed successfully.';
            } catch (PDOException $e) {
                $message = 'Password change failed: ' . $e->getMessage();
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
    <title>Profile - SCMIRT Library</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="search.php">Search Books</a>
        <a href="#">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="profile-container">
        <h2>My Profile</h2>
        <?php if ($message): ?>
            <p style="color: <?php echo strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        <form method="POST">
            <div class="profile-field">
                <div class="profile-label">Full Name:</div>
                <input type="text" class="profile-input" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
            </div>
            <div class="profile-field">
                <div class="profile-label">Email:</div>
                <input type="email" class="profile-input" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>
            <div class="profile-field">
                <div class="profile-label">Contact Number:</div>
                <input type="tel" class="profile-input" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>">
            </div>
            <div class="profile-field">
                <div class="profile-label">Role:</div>
                <input type="text" class="profile-input" value="<?php echo htmlspecialchars($user['role']); ?>" readonly>
            </div>
            <button type="submit" name="update">Update Profile</button>
        </form>
        <form method="POST">
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit" name="change_password">Change Password</button>
        </form>
    </div>
</body>
</html>