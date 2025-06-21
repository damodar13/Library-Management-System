<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle form submissions (Add/Edit/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        // Add a new category
        $name = $_POST['name'];
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header('Location: manage_categories.php?success=Category added successfully');
        exit;
    } elseif (isset($_POST['edit_category'])) {
        // Edit an existing category
        $id = $_POST['id'];
        $name = $_POST['name'];
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        header('Location: manage_categories.php?success=Category updated successfully');
        exit;
    } elseif (isset($_POST['delete_category'])) {
        // Delete a category
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: manage_categories.php?success=Category deleted successfully');
        exit;
    }
}

// Fetch all categories
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

// Check if editing a category
$edit_category = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $edit_category = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="dashboard-container">
        <h2>Manage Categories</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <!-- Add/Edit Category Form -->
        <div class="container">
            <h3><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h3>
            <form method="POST">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                    <input type="hidden" name="edit_category" value="1">
                <?php else: ?>
                    <input type="hidden" name="add_category" value="1">
                <?php endif; ?>
                <input type="text" name="name" placeholder="Category Name" value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                <button type="submit"><?php echo $edit_category ? 'Update Category' : 'Add Category'; ?></button>
            </form>
            <?php if ($edit_category): ?>
                <a href="manage_categories.php"><button>Cancel Edit</button></a>
            <?php endif; ?>
        </div>

        <!-- Categories Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['id']; ?></td>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td>
                            <a href="manage_categories.php?edit=<?php echo $category['id']; ?>"><button>Edit</button></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category? This may affect books in this category.');">
                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                <input type="hidden" name="delete_category" value="1">
                                <button type="submit" class="return-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>