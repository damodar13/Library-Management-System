<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$stmt = $pdo->prepare("SELECT * FROM fines WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user['id']]);
$fines = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_fine = array_sum(array_column($fines, 'amount'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Fines</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <!-- Same navbar as other pages -->
        <a href="index.php">Home</a>
        <a href="#">Dashboard</a>
        <a href="search.php">Search Books</a>
        <a href="profile.php">Profile</a>
        <a href="#">My Library</a>
        <a href="student_dashboard">Account</a>
        <a href="logout.php">Logout</a></table>
    </div>

    <div class="container">
        <h2>Pending Fines</h2>
        <?php if (!empty($fines)): ?>
            <table>
                <tr>
                    <th>Amount</th>
                    <th>Reason</th>
                    <th>Date Issued</th>
                </tr>
                <?php foreach ($fines as $fine): ?>
                <tr>
                    <td>₹<?php echo htmlspecialchars($fine['amount'] ?? 0.00); ?></td>
                    <td><?php echo htmlspecialchars($fine['reason'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($fine['issued_date'] ?? 'Unknown date'); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align: right;">
                        Total: ₹<?php echo number_format($total_fine, 2); ?>
                    </td>
                </tr>
            </table>
        <?php else: ?>
            <p>No pending fines.</p>
        <?php endif; ?>
    </div>
</body>
</html>