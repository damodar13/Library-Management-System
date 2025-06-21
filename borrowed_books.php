<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$stmt = $pdo->prepare("SELECT bb.*, b.title FROM borrowed_books bb JOIN books b ON bb.book_id = b.id WHERE bb.user_id = ? AND bb.return_date IS NULL");
$stmt->execute([$user['id']]);
$borrowed_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrowed Books</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="student_dashboard.php">Dashboard</a>
        <a href="search.php">Search Books</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>Borrowed Books</h2>
        <?php if (!empty($borrowed_books)): ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                </tr>
                <?php foreach ($borrowed_books as $book): ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['borrow_date']); ?></td>
                    <td><?php echo htmlspecialchars($book['due_date']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No books currently borrowed.</p>
        <?php endif; ?>
    </div>
</body>
</html>