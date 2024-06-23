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
$student_id = $_SESSION['user_id'];

// Fetch course details
$query = "SELECT courses.course_symbol, courses.course_id, teacher_courses.section_number, course_sections.time 
          FROM teacher_courses 
          INNER JOIN courses ON teacher_courses.course_number = courses.course_id 
          INNER JOIN course_sections ON teacher_courses.section_number = course_sections.section_number
          WHERE teacher_courses.course_number = '$course_id' AND teacher_courses.section_number = '$section_number'";
$result = mysqli_query($conn, $query);

$course_details = mysqli_fetch_assoc($result);

// Fetch attendance records where status is 'No' for the current student in the current course
$query = "SELECT date FROM attendance 
          WHERE course_id = '$course_id' AND section_number = '$section_number' AND student_id = '$student_id' AND status = 0";
$attendance_result = mysqli_query($conn, $query);

$absences = [];
while ($row = mysqli_fetch_assoc($attendance_result)) {
    $absences[] = $row;
}

$absence_count = count($absences);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Page - Student</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cps.css">
</head>
<body>
    <div class="sidebar">
        <h2>Student Panel</h2>
        <button class="button1" onclick="window.location.href='student_page.php'">Student Page</button>
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
        <?php if ($absence_count > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Index</th>
                        <th>Date</th>
                        <th>Submit an Excuse</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($absences as $index => $absence): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $absence['date']; ?></td>
                            <td><button class="button" onclick="window.location.href='submit_excuse.php?course_id=<?php echo $course_id; ?>&section_number=<?php echo $section_number; ?>&date=<?php echo $absence['date']; ?>'">Submit an Excuse</button></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3">Total Number of Absence Days: <?php echo $absence_count; ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-absences">
                <p>You do not have any absences in this course.</p>
            </div>
        <?php endif; ?>
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
