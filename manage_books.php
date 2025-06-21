<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all books, categories, and publications
try {
    $stmt = $pdo->query("SELECT books.*, categories.name AS category_name, publications.name AS publication_name 
                         FROM books 
                         LEFT JOIN categories ON books.category_id = categories.id 
                         LEFT JOIN publications ON books.publication_id = publications.id");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $publications = $pdo->query("SELECT * FROM publications")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle Add/Edit Book
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    if (isset($_POST['add_book'])) {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $isbn = trim($_POST['isbn']);
        $category_id = $_POST['category_id'];
        $publication_id = $_POST['publication_id'];
        $published_year = (int)$_POST['published_year'];
        $available_copies = (int)$_POST['available_copies'];

        // Validation
        if (empty($title) || empty($author) || empty($isbn) || empty($category_id) || empty($publication_id)) {
            header('Location: manage_books.php?error=All fields are required');
            exit;
        }
        if (!preg_match('/^\d{10}|\d{13}$/', $isbn)) {
            header('Location: manage_books.php?error=Invalid ISBN format (10 or 13 digits)');
            exit;
        }
        if ($published_year < 1800 || $published_year > date('Y')) {
            header('Location: manage_books.php?error=Invalid published year');
            exit;
        }
        if ($available_copies < 0) {
            header('Location: manage_books.php?error=Available copies cannot be negative');
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, category_id, publication_id, published_year, available_copies) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $isbn, $category_id, $publication_id, $published_year, $available_copies]);
            header('Location: manage_books.php?success=Book added successfully');
            exit;
        } catch (PDOException $e) {
            header('Location: manage_books.php?error=Database error: ' . $e->getMessage());
            exit;
        }
    } elseif (isset($_POST['edit_book'])) {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $isbn = trim($_POST['isbn']);
        $category_id = $_POST['category_id'];
        $publication_id = $_POST['publication_id'];
        $published_year = (int)$_POST['published_year'];
        $available_copies = (int)$_POST['available_copies'];

        // Validation
        if (empty($title) || empty($author) || empty($isbn) || empty($category_id) || empty($publication_id)) {
            header('Location: manage_books.php?error=All fields are required');
            exit;
        }
        if (!preg_match('/^\d{10}|\d{13}$/', $isbn)) {
            header('Location: manage_books.php?error=Invalid ISBN format (10 or 13 digits)');
            exit;
        }
        if ($published_year < 1800 || $published_year > date('Y')) {
            header('Location: manage_books.php?error=Invalid published year');
            exit;
        }
        if ($available_copies < 0) {
            header('Location: manage_books.php?error=Available copies cannot be negative');
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, category_id = ?, publication_id = ?, 
                                   published_year = ?, available_copies = ? WHERE id = ?");
            $stmt->execute([$title, $author, $isbn, $category_id, $publication_id, $published_year, $available_copies, $id]);
            header('Location: manage_books.php?success=Book updated successfully');
            exit;
        } catch (PDOException $e) {
            header('Location: manage_books.php?error=Database error: ' . $e->getMessage());
            exit;
        }
    } elseif (isset($_POST['delete_book'])) {
        $id = $_POST['delete_book'];
        try {
            $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: manage_books.php?success=Book deleted successfully');
            exit;
        } catch (PDOException $e) {
            header('Location: manage_books.php?error=Database error: ' . $e->getMessage());
            exit;
        }
    }
}

// Fetch book for editing
$edit_book = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$edit_book) {
            header('Location: manage_books.php?error=Book not found');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: manage_books.php?error=Database error: ' . $e->getMessage());
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="dashboard-container">
        <h2>Manage Books</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <!-- Add/Edit Book Form -->
        <h3><?php echo $edit_book ? 'Edit Book' : 'Add New Book'; ?></h3>
        <?php if (empty($categories)): ?>
            <div class="alert error">No categories available. Please add a category first.</div>
        <?php endif; ?>
        <?php if (empty($publications)): ?>
            <div class="alert error">No publications available. Please add a publication first.</div>
        <?php endif; ?>
        <form method="POST" class="container">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <?php if ($edit_book): ?>
                <input type="hidden" name="id" value="<?php echo $edit_book['id']; ?>">
                <input type="hidden" name="edit_book" value="1">
            <?php else: ?>
                <input type="hidden" name="add_book" value="1">
            <?php endif; ?>
            <input type="text" name="title" placeholder="Title" value="<?php echo $edit_book ? htmlspecialchars($edit_book['title']) : ''; ?>" required>
            <input type="text" name="author" placeholder="Author" value="<?php echo $edit_book ? htmlspecialchars($edit_book['author']) : ''; ?>" required>
            <input type="text" name="isbn" placeholder="ISBN" value="<?php echo $edit_book ? htmlspecialchars($edit_book['isbn']) : ''; ?>" required>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $edit_book && $edit_book['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="publication_id" required>
                <option value="">Select Publication</option>
                <?php foreach ($publications as $publication): ?>
                    <option value="<?php echo $publication['id']; ?>" <?php echo $edit_book && $edit_book['publication_id'] == $publication['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($publication['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="published_year" placeholder="Published Year" value="<?php echo $edit_book ? htmlspecialchars($edit_book['published_year']) : ''; ?>" required>
            <input type="number" name="available_copies" placeholder="Available Copies" value="<?php echo $edit_book ? htmlspecialchars($edit_book['available_copies']) : ''; ?>" required>
            <button type="submit"><?php echo $edit_book ? 'Update Book' : 'Add Book'; ?></button>
        </form>

        <!-- Books Table -->
        <h3>Books List</h3>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Category</th>
                    <th>Publication</th>
                    <th>Published Year</th>
                    <th>Available Copies</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($book['category_name'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($book['publication_name'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($book['published_year']); ?></td>
                        <td><?php echo htmlspecialchars($book['available_copies']); ?></td>
                        <td>
                            <a href="manage_books.php?edit=<?php echo $book['id']; ?>">Edit</a> |
                            <form method="POST" action="manage_books.php" style="display:inline;">
                                <input type="hidden" name="delete_book" value="<?php echo $book['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this book?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>