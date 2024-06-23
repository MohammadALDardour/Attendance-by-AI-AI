<?php

include 'connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Fetch student's courses from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT courses.course_symbol, student_courses.course_number, student_courses.section_number
          FROM student_courses
          INNER JOIN courses ON student_courses.course_number = courses.course_id
          WHERE student_courses.student_id = '$user_id'";
$result = mysqli_query($conn, $query);

// Close database connection
mysqli_close($conn);

// Store courses in an array
$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sp.css">
</head>
<body>
    <div class="sidebar">
        <h2>Student Panel</h2>
        <p>Welcome, <span id="user_name"></span></p>
        <p>ID: <span id="user_id"></span></p>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <h3>My Courses</h3>
            <div class="title">Attendance By AI</div>
            <div class="buttons">
                <button id="notification-btn">Notifications</button>
                <button id="logout-btn" class="logout-btn">Logout</button>
            </div>
        </div>
        <div class="course-list" id="course-list">
            <!-- Course cards will be dynamically added here -->
        </div>
    </div>

    <script src="student_page.js"></script>
</body>

<script>
    // Set user name and ID
    document.getElementById('user_name').innerText = "<?php echo $_SESSION['user_name']; ?>";
    document.getElementById('user_id').innerText = "<?php echo $_SESSION['user_id']; ?>";

    // Logout button click event
    document.getElementById('logout-btn').addEventListener('click', function() {
        window.location.href = 'index.html';
    });

    // Notification button click event - functionality to be added later
    document.getElementById('notification-btn').addEventListener('click', function() {
        // Add functionality here
    });

    // Function to add courses dynamically
    function addCourses(courses) {
        var courseList = document.getElementById('course-list');
        courses.forEach(function(course) {
            var courseCard = document.createElement('div');
            courseCard.className = 'course-card';
            courseCard.innerHTML = '<h4>' + course.course_symbol + ' ' + course.course_number + '</h4><p>Section: ' + course.section_number + '</p>';
            courseCard.addEventListener('click', function() {
                window.location.href = 'course_page_std.php?course_id=' + course.course_number + '&section_number=' + course.section_number;
            });
            courseList.appendChild(courseCard);
        });
    }

    // Call addCourses function with the fetched data
    addCourses(<?php echo json_encode($courses); ?>);
</script>
</html>
