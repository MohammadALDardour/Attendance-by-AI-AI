<?php
include 'connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Get course_id and section_number from URL
$course_id = $_GET['course_id'];
$section_number = $_GET['section_number'];

// Fetch course details
$query = "SELECT courses.course_symbol, courses.course_id, teacher_courses.section_number, course_sections.time 
          FROM teacher_courses 
          INNER JOIN courses ON teacher_courses.course_number = courses.course_id 
          INNER JOIN course_sections ON teacher_courses.section_number = course_sections.section_number
          WHERE teacher_courses.course_number = '$course_id' AND teacher_courses.section_number = '$section_number' AND teacher_courses.teacher_id = '{$_SESSION['user_id']}'";
$result = mysqli_query($conn, $query);

$course_details = mysqli_fetch_assoc($result);

// Fetch students in the current course and section
$query = "SELECT student_courses.student_id, users.user_name 
          FROM student_courses 
          INNER JOIN users ON student_courses.student_id = users.user_id
          WHERE student_courses.course_number = '$course_id' AND student_courses.section_number = '$section_number'";
$students_result = mysqli_query($conn, $query);

$students = [];
while ($row = mysqli_fetch_assoc($students_result)) {
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Information Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="si.css">
</head>
<body>
    <div class="sidebar">
        <button class="button" onclick="window.location.href='teacher_page.php'">Courses</button>
        <button class="button" onclick="window.location.href='course_page.php?course_id=<?php echo $course_id; ?>&section_number=<?php echo $section_number; ?>'">Course Page</button>
        <button class="button" onclick="window.location.href='excuses.php?course_id=<?php echo $course_id; ?>&section_number=<?php echo $section_number; ?>'">Excuses</button>
        <button class="button" onclick="window.location.href='students_information.php?course_id=<?php echo $course_id; ?>&section_number=<?php echo $section_number; ?>'">Students Information</button>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <div>
                <h3><?php echo $course_details['course_symbol'] . " " . $course_details['course_id']; ?></h3>
                <p>Section: <?php echo $course_details['section_number']; ?></p>
                <p>Time: <?php echo $course_details['time']; ?></p>
            </div>
            <div class="title">Attendance By AI</div>
            <div class="buttons">
                <button id="notification-btn">Notifications</button>
                <button id="logout-btn" class="logout-btn">Logout</button>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Serial</th>
                    <th>ID</th>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody>
                <?php $serial = 1; ?>
                <?php foreach ($students as $student) : ?>
                    <tr>
                        <td><?php echo $serial++; ?></td>
                        <td><?php echo $student['student_id']; ?></td>
                        <td><?php echo $student['user_name']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('logout-btn').addEventListener('click', function() {
            window.location.href = 'index.html';
        });

        document.getElementById('notification-btn').addEventListener('click', function() {
            // Add notification functionality here
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>
