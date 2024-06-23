<?php
include 'connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Get excuse_id, course_id, and section_number from URL
$excuse_id = $_GET['excuse_id'];
$course_id = $_GET['course_id'];
$section_number = $_GET['section_number'];

// Delete the excuse from the database
$query = "DELETE FROM excuses WHERE excuse_id = '$excuse_id'";
mysqli_query($conn, $query);

// Redirect back to the excuses page
header("Location: excuses.php?course_id=$course_id&section_number=$section_number");
exit();
?>
