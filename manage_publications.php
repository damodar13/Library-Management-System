<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle form submissions (Add/Edit/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_publication'])) {
        // Add a new publication
        $name = $_POST['name'];
        $stmt = $pdo->prepare("INSERT INTO publications (name) VALUES (?)");
        $stmt->execute([$name]);
        header('Location: manage_publications.php?success=Publication added successfully');
        exit;
    } elseif (isset($_POST['edit_publication'])) {
        // Edit an existing publication
        $id = $_POST['id'];
        $name = $_POST['name'];
        $stmt = $pdo->prepare("UPDATE publications SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        header('Location: manage_publications.php?success=Publication updated successfully');
        exit;
    } elseif (isset($_POST['delete_publication'])) {
        // Delete a publication
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM publications WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: manage_publications.php?success=Publication deleted successfully');
        exit;
    }
}

// Fetch all publications
$stmt = $pdo->query("SELECT * FROM publications");
$publications = $stmt->fetchAll();

// Check if editing a publication
$edit_publication = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM publications WHERE id = ?");
    $stmt->execute([$id]);
    $edit_publication = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Publications</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="dashboard-container">
        <h2>Manage Publications</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <!-- Add/Edit Publication Form -->
        <div class="container">
            <h3><?php echo $edit_publication ? 'Edit Publication' : 'Add New Publication'; ?></h3>
            <form method="POST">
                <?php if ($edit_publication): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_publication['id']; ?>">
                    <input type="hidden" name="edit_publication" value="1">
                <?php else: ?>
                    <input type="hidden" name="add_publication" value="1">
                <?php endif; ?>
                <input type="text" name="name" placeholder="Publication Name" value="<?php echo $edit_publication ? htmlspecialchars($edit_publication['name']) : ''; ?>" required>
                <button type="submit"><?php echo $edit_publication ? 'Update Publication' : 'Add Publication'; ?></button>
            </form>
            <?php if ($edit_publication): ?>
                <a href="manage_publications.php"><button>Cancel Edit</button></a>
            <?php endif; ?>
        </div>

        <!-- Publications Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($publications as $publication): ?>
                    <tr>
                        <td><?php echo $publication['id']; ?></td>
                        <td><?php echo htmlspecialchars($publication['name']); ?></td>
                        <td>
                            <a href="manage_publications.php?edit=<?php echo $publication['id']; ?>"><button>Edit</button></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this publication? This may affect books under this publication.');">
                                <input type="hidden" name="id" value="<?php echo $publication['id']; ?>">
                                <input type="hidden" name="delete_publication" value="1">
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