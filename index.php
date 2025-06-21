<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCMIRT Digital Library</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="index-page">
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="search.php">Search Books</a>
        <a href="profile.php">Profile</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="logout.php">Logout</a>
            <a href="student_dashboard.php">Dashboard</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
        <?php endif; ?>
    </div>
    
    <header>
        <img src="https://scmirt.org/wp-content/uploads/2022/10/logo-scmirt316.png" alt="SCMIRT Logo">
        <h1>Welcome to SCMIRT Digital Library</h1>
    </header>
    
    <section id="about">
        <h2>About SCMIRT Library</h2>
        <p>The SCMIRT Digital Library helps students and faculty manage books, track issues, and access digital resources efficiently.</p>
    </section>
    
    <section id="features">
        <h2>Features</h2>
        <ul>
            <li>Search and borrow books online</li>
            <li>Track due dates and return books</li>
            <li>Access digital resources</li>
            <li>User-friendly dashboard</li>
            <li>Calendar for due dates and events</li>
        </ul>
    </section>
    
    <section id="contact">
        <h2>Contact Us</h2>
        <p>Email: library@scmirt.edu | Phone: +91 12345 67890</p>
    </section>

    <form action="search.php" method="GET" class="search-container">
        <input type="text" name="query" class="search-input" placeholder="Search books..." required>
        <button type="submit" class="search-button">Search</button>
    </form>

    <div class="announcement-container">
        <h3>Announcements</h3>
        <div class="announcement">Library Closed on April 30, 2025 for Maintenance</div>
        <div class="announcement">New Books Added: Check the Catalog!</div>
    </div>

    <div class="calendar-container">
        <div class="calendar-month">April 2025</div>
        <div class="calendar-grid">
            <div class="calendar-day calendar-day-header">Sun</div>
            <div class="calendar-day calendar-day-header">Mon</div>
            <div class="calendar-day calendar-day-header">Tue</div>
            <div class="calendar-day calendar-day-header">Wed</div>
            <div class="calendar-day calendar-day-header">Thu</div>
            <div class="calendar-day calendar-day-header">Fri</div>
            <div class="calendar-day calendar-day-header">Sat</div>
            <div class="calendar-day">1</div>
            <div class="calendar-day">2</div>
            <div class="calendar-day">3</div>
            <!-- Add more days as needed -->
        </div>
    </div>
    
    <footer>
        <p>© 2025 SCMIRT Library. All Rights Reserved.</p>
    </footer>
</body>
</html>