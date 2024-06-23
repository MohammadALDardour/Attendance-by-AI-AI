<?php
include 'connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Get course_id and section_number from POST data
$course_id = $_POST['course_id'];
$section_number = $_POST['section_number'];

// Check if attendance data is provided
if (isset($_POST['attendance'])) {
    foreach ($_POST['attendance'] as $student_id => $dates) {
        foreach ($dates as $date => $status) {
            // Update the attendance status in the database
            $query = "UPDATE attendance SET status = 1 WHERE course_id = '$course_id' AND section_number = '$section_number' AND student_id = '$student_id' AND date = '$date' AND status = 0";
            mysqli_query($conn, $query);
        }
    }
}

// Redirect back to the course page
header("Location: course_page.php?course_id=$course_id&section_number=$section_number");
exit();
?>
