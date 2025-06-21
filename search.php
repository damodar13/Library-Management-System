<?php
require_once 'config.php';

// Initialize variables
$books = [];
$query = $_GET['query'] ?? '';
$category = $_GET['category'] ?? '';
$availability = $_GET['availability'] ?? '';

// Base SQL query - Shows all books by default
$sql = "SELECT * FROM books WHERE 1";
$params = [];
$conditions = [];

// Add search conditions if query exists
if (!empty($query)) {
    $conditions[] = "(title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    array_push($params, "%$query%", "%$query%", "%$query%");
}

// Add category filter
if (!empty($category)) {
    $conditions[] = "category = ?";
    $params[] = $category;
}

// Add availability filter
if (!empty($availability)) {
    $conditions[] = "available = ?";
    $params[] = ($availability === 'Available') ? 1 : 0;
}

// Combine conditions
if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// Execute the query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch borrowed books for the current user (to check if they can return a book)
$borrowed_books = [];
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student') {
    $stmt = $pdo->prepare("SELECT bb.*, b.title FROM borrowed_books bb JOIN books b ON bb.book_id = b.id WHERE bb.user_id = ? AND bb.return_date IS NULL");
    $stmt->execute([$_SESSION['user']['id']]);
    $borrowed_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $borrowed_book_ids = array_column($borrowed_books, 'book_id');
}

// Handle book request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_book'])) {
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = 'Please login to request books';
        header('Location: login.php');
        exit;
    }

    $book_id = $_POST['book_id'] ?? '';
    $user_id = $_SESSION['user']['id'];

    try {
        // Verify book exists and is available
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND available = 1");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

        if (!$book) {
            $_SESSION['error'] = 'Book not available for request';
        } else {
            // Check for existing request
            $stmt = $pdo->prepare("SELECT id FROM requested_books WHERE user_id = ? AND book_id = ? AND status = 'pending'");
            $stmt->execute([$user_id, $book_id]);
            
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'You already have a pending request for this book';
            } else {
                // Create new request
                $stmt = $pdo->prepare("INSERT INTO requested_books (user_id, book_id, request_date, status) VALUES (?, ?, NOW(), 'pending')");
                $stmt->execute([$user_id, $book_id]);
                
                $_SESSION['success'] = 'Book request submitted successfully!';
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }

    // Redirect back with filters preserved
    $query_string = http_build_query($_GET);
    header("Location: search.php?$query_string");
    exit;
}

// Handle book return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_book'])) {
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = 'Please login to return books';
        header('Location: login.php');
        exit;
    }

    $borrowed_book_id = $_POST['borrowed_book_id'] ?? '';
    $book_id = $_POST['book_id'] ?? '';
    $user_id = $_SESSION['user']['id'];

    try {
        // Update the borrowed_books table to set return_date
        $stmt = $pdo->prepare("UPDATE borrowed_books SET return_date = CURDATE() WHERE id = ? AND user_id = ?");
        $stmt->execute([$borrowed_book_id, $user_id]);

        // Update the books table to set available = TRUE
        $stmt = $pdo->prepare("UPDATE books SET available = TRUE WHERE id = ?");
        $stmt->execute([$book_id]);

        // Check for overdue fines
        $stmt = $pdo->prepare("SELECT due_date FROM borrowed_books WHERE id = ?");
        $stmt->execute([$borrowed_book_id]);
        $due_date = $stmt->fetchColumn();

        $due_date = new DateTime($due_date);
        $return_date = new DateTime();
        if ($return_date > $due_date) {
            $days_overdue = $return_date->diff($due_date)->days;
            $fine_amount = $days_overdue * 5; // 5 INR per day overdue
            $stmt = $pdo->prepare("INSERT INTO fines (user_id, amount, issue_date, status) VALUES (?, ?, CURDATE(), 'pending')");
            $stmt->execute([$user_id, $fine_amount]);
        }

        $_SESSION['success'] = 'Book returned successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }

    // Redirect back with filters preserved
    $query_string = http_build_query($_GET);
    header("Location: search.php?$query_string");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books - SCMIRT Library</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .filter-container {
            max-width: 600px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
        }
        .filter-container select, .filter-container button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }
        .return-button {
            padding: 5px 10px;
            background-color: #dc3545;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .return-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="search.php">Search Books</a>
        <a href="profile.php">Profile</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
        <?php endif; ?>
    </div>

    <div class="dashboard-container">
        <h2>Search Books</h2>
        <form method="GET" class="search-container">
            <input type="text" name="query" class="search-input" placeholder="Search by title, author, or ISBN..." value="<?php echo htmlspecialchars($query); ?>">
            <button type="submit" class="search-button">Search</button>
        </form>

        <div class="filter-container">
            <form method="GET">
                <select name="category" class="search-input">
                    <option value="">All Categories</option>
                    <option value="Fiction" <?php echo $category === 'Fiction' ? 'selected' : ''; ?>>Fiction</option>
                    <option value="Science" <?php echo $category === 'Science' ? 'selected' : ''; ?>>Science</option>
                    <option value="Technology" <?php echo $category === 'Technology' ? 'selected' : ''; ?>>Technology</option>
                    <!-- Computer Science Categories -->
                    <option value="Programming" <?php echo $category === 'Programming' ? 'selected' : ''; ?>>Programming</option>
                    <option value="Algorithms" <?php echo $category === 'Algorithms' ? 'selected' : ''; ?>>Algorithms</option>
                    <option value="Databases" <?php echo $category === 'Databases' ? 'selected' : ''; ?>>Databases</option>
                    <option value="Networking" <?php echo $category === 'Networking' ? 'selected' : ''; ?>>Networking</option>
                    <option value="Operating Systems" <?php echo $category === 'Operating Systems' ? 'selected' : ''; ?>>Operating Systems</option>
                    <option value="Artificial Intelligence" <?php echo $category === 'Artificial Intelligence' ? 'selected' : ''; ?>>Artificial Intelligence</option>
                    <option value="Software Engineering" <?php echo $category === 'Software Engineering' ? 'selected' : ''; ?>>Software Engineering</option>
                    <option value="Computer Architecture" <?php echo $category === 'Computer Architecture' ? 'selected' : ''; ?>>Computer Architecture</option>
                    <!-- Academic Program Categories -->
                    <option value="BSCCS" <?php echo $category === 'BSCCS' ? 'selected' : ''; ?>>BSCCS</option>
                    <option value="BCOM" <?php echo $category === 'BCOM' ? 'selected' : ''; ?>>BCOM</option>
                    <option value="BBA" <?php echo $category === 'BBA' ? 'selected' : ''; ?>>BBA</option>
                </select>
                <input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>">
                <input type="hidden" name="availability" value="<?php echo htmlspecialchars($availability); ?>">
                <button type="submit" class="search-button">Filter</button>
            </form>
            <form method="GET">
                <select name="availability" class="search-input">
                    <option value="">All Availability</option>
                    <option value="Available" <?php echo $availability === 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="Borrowed" <?php echo $availability === 'Borrowed' ? 'selected' : ''; ?>>Borrowed</option>
                </select>
                <input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <button type="submit" class="search-button">Filter</button>
            </form>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="grid-container">
            <?php if (empty($books)): ?>
                <p>No books found.</p>
            <?php else: ?>
                <?php foreach ($books as $book): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                        <p>Category: <?php echo htmlspecialchars($book['category']); ?> | <?php echo $book['available'] ? 'Available' : 'Borrowed'; ?></p>
                        <?php if ($book['available'] && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student'): ?>
                            <form method="POST">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <button type="submit" name="request_book" class="search-button">Request</button>
                            </form>
                        <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student' && in_array($book['id'], $borrowed_book_ids)): ?>
                            <?php
                            // Find the borrowed book details
                            $borrowed_book = array_filter($borrowed_books, fn($bb) => $bb['book_id'] == $book['id']);
                            $borrowed_book = reset($borrowed_book);
                            ?>
                            <form method="POST">
                                <input type="hidden" name="borrowed_book_id" value="<?php echo $borrowed_book['id']; ?>">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <button type="submit" name="return_book" class="return-button">Return</button>
                            </form>
                        <?php else: ?>
                            <button disabled>Request</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>