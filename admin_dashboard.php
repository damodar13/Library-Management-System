<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Fetch stats with error handling
try {
    $books_count = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $borrowed_count = $pdo->query("SELECT COUNT(*) FROM borrowed_books")->fetchColumn();
    $returned_count = $pdo->query("SELECT COUNT(*) FROM borrowed_books WHERE return_date IS NOT NULL")->fetchColumn();
    $publications_count = $pdo->query("SELECT COUNT(*) FROM publications")->fetchColumn();
    $categories_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $total_fines = $pdo->query("SELECT SUM(amount) FROM fines WHERE status = 'pending'")->fetchColumn() ?? 0;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="search.php">Search Books</a>
        <a href="profile.php">Profile</a>
        <a href="manage_categories.php">Categories</a>
        <a href="manage_publications.php">Publications</a>
        <a href="manage_books.php">Books</a>
        <a href="manage_issued.php">Issue Books</a>
        <a href="requested_books.php">Requested Books</a>
        <a href="manage_report.php">Report</a>
        <a href="manage_users.php">Reg Students</a>
        <a href="profile.php">Account</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="dashboard-container">
        <div class="welcome-message">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</div>
        <div class="dashboard-title">Admin Dashboard</div>

        <form action="search.php" method="GET" class="search-container">
            <input type="text" name="query" class="search-input" placeholder="Search books..." required>
            <button type="submit" class="search-button">Search</button>
        </form>

        <div class="admin-grid-container">
            <a href="manage_books.php" class="card-link">
                <div class="card admin-card card-books">
                    <div class="icon">📚</div>
                    <div class="count"><?php echo $books_count; ?></div>
                    <div class="label">Books Listed</div>
                </div>
            </a>

            <a href="manage_issued.php" class="card-link">
                <div class="card admin-card card-issued">
                    <div class="icon">📖</div>
                    <div class="count"><?php echo $borrowed_count; ?></div>
                    <div class="label">Times Books Issued</div>
                </div>
            </a>

            <a href="manage_returned.php" class="card-link">
                <div class="card admin-card card-returned">
                    <div class="icon">♻️</div>
                    <div class="count"><?php echo $returned_count; ?></div>
                    <div class="label">Times Books Returned</div>
                </div>
            </a>

            <a href="manage_users.php" class="card-link">
                <div class="card admin-card card-users">
                    <div class="icon">👥</div>
                    <div class="count"><?php echo $users_count; ?></div>
                    <div class="label">Registered Users</div>
                </div>
            </a>

            <a href="manage_publications.php" class="card-link">
                <div class="card admin-card card-publications">
                    <div class="icon">📝</div>
                    <div class="count"><?php echo $publications_count; ?></div>
                    <div class="label">Publications Listed</div>
                </div>
            </a>

            <a href="manage_categories.php" class="card-link">
                <div class="card admin-card card-categories">
                    <div class="icon">📂</div>
                    <div class="count"><?php echo $categories_count; ?></div>
                    <div class="label">Listed Categories</div>
                </div>
            </a>

            <a href="manage_fines.php" class="card-link">
                <div class="card admin-card card-fine">
                    <div class="icon">💰</div>
                    <div class="count"><?php echo number_format($total_fines, 2); ?></div>
                    <div class="label">Total Pending Fines</div>
                </div>
            </a>
        </div>

        <div class="announcement-container">
            <h3>Announcements</h3>
            <div class="announcement">Library Closed on April 30, 2025 for Maintenance</div>
            <div class="announcement">New Books Added: Check the Catalog!</div>
        </div>

        <div class="user-management-container">
            <h3>Manage Users</h3>
            <a href="manage_users.php"><button class="manage-button">View Registered Students</button></a>
        </div>
    </div>

    <script>
        document.querySelectorAll('.admin-card').forEach(card => {
            card.addEventListener('click', function(event) {
                try {
                    event.preventDefault();
                    console.log('Card classes:', this.classList);
                    const typeClass = Array.from(this.classList).find(cls => cls.startsWith('card-'));
                    if (!typeClass) {
                        console.error('No card-* class found on this card:', this.classList);
                        return;
                    }
                    const type = typeClass.split('-')[1];
                    console.log('Extracted type:', type);
                    const pages = {
                        books: 'manage_books.php',
                        issued: 'manage_issued.php',
                        returned: 'manage_returned.php',
                        users: 'manage_users.php',
                        publications: 'manage_publications.php',
                        categories: 'manage_categories.php',
                        fine: 'manage_fines.php'
                    };
                    if (!pages[type]) {
                        console.error('No page mapping found for type:', type);
                        return;
                    }
                    console.log('Redirecting to:', pages[type]);
                    window.location.href = pages[type];
                } catch (error) {
                    console.error('Error in card click handler:', error);
                }
            });
        });
    </script>
</body>
</html>