<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Fetch borrowed books
$stmt = $pdo->prepare("SELECT bb.*, b.title FROM borrowed_books bb JOIN books b ON bb.book_id = b.id WHERE bb.user_id = ? AND bb.return_date IS NULL");
$stmt->execute([$user['id']]);
$borrowed_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch fines
$stmt = $pdo->prepare("SELECT * FROM fines WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user['id']]);
$fines = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_fine = array_sum(array_column($fines, 'amount'));

// Fetch requested books
$stmt = $pdo->prepare("SELECT rb.*, b.title FROM requested_books rb JOIN books b ON rb.book_id = b.id WHERE rb.user_id = ?");
$stmt->execute([$user['id']]);
$requested_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="#">Dashboard</a>
        <a href="search.php">Search Books</a>
        <a href="profile.php">Profile</a>
        <a href="#">My Library</a>
        <a href="#">Account</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="dashboard-container">
        <div class="welcome-message">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</div>
        <div class="dashboard-title">My Library Stats</div>

        <form action="search.php" method="GET" class="search-container">
            <input type="text" name="query" class="search-input" placeholder="Search books..." required>
            <button type="submit" class="search-button">Search</button>
        </form>

        <div class="grid-container">
       <!-- Borrowed Books -->
       <div class="card" onclick="window.location.href='borrowed_books.php';">
            <div class="icon">📚</div>
            <div class="count"><?php echo count($borrowed_books); ?> Books Borrowed</div>
            <div class="label"><?php echo count($borrowed_books) > 0 ? htmlspecialchars($borrowed_books[0]['title']) . ' - Due: ' . $borrowed_books[0]['due_date'] : 'None'; ?></div>
        </div>

        <!-- Search Books -->
        <div class="card" onclick="window.location.href='search.php';">
            <div class="icon">🔍</div>
            <div class="count">Search Books</div>
            <div class="label">Find & Request Titles</div>
        </div>

        <!-- Due Soon -->
        <div class="card" onclick="window.location.href='borrowed_books.php';">
            <div class="icon">⏰</div>
            <div class="count"><?php echo count($borrowed_books); ?> Due Soon</div>
            <div class="label"><?php echo count($borrowed_books) > 0 ? htmlspecialchars($borrowed_books[0]['title']) . ' - Due: ' . $borrowed_books[0]['due_date'] : 'None'; ?></div>
        </div>

        <!-- Pending Fines -->
        <div class="card" onclick="window.location.href='fines.php';">
            <div class="icon">💰</div>
            <div class="count">₹<?php echo number_format($total_fine, 2); ?></div>
            <div class="label">Pending Fines</div>
        </div>

        <!-- Requested Books -->
        <div class="card" onclick="window.location.href='requested_books.php';">
            <div class="icon">🔄</div>
            <div class="count"><?php echo count($requested_books); ?> Requested</div>
            <div class="label"><?php echo count($requested_books) > 0 ? htmlspecialchars($requested_books[0]['title']) . ' - Status: ' . $requested_books[0]['status'] : 'None'; ?></div>
        </div>

        <!-- Change Password -->
        <div class="card" onclick="window.location.href='change_password.php';">
            <div class="icon">🔑</div>
            <div class="count">Change Password</div>
            <div class="label">Update Your Credentials</div>
        </div>
    </div>

        <div class="announcement-container">
            <h3>Announcements</h3>
            <div class="announcement">Library Closed on April 30, 2025 for Maintenance</div>
            <div class="announcement">New Books Added: Check the Catalog!</div>
        </div>
    </div>
</body>
</html>