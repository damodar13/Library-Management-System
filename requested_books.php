<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$stmt = $pdo->prepare("SELECT rb.*, b.title FROM requested_books rb JOIN books b ON rb.book_id = b.id WHERE rb.user_id = ?");
$stmt->execute([$user['id']]);
$requested_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Requested Books</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <!-- Same navbar as above -->
    </div>

    <div class="container">
        <h2>Requested Books</h2>
        <?php if (!empty($requested_books)): ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Request Date</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($requested_books as $request): ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['title']); ?></td>
                    <td><?php echo htmlspecialchars($request['request_date']); ?></td>
                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No books currently requested.</p>
        <?php endif; ?>
    </div>
</body>
</html>