<?php
include 'connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$course_id = $_GET['course_id'];
$section_number = $_GET['section_number'];

// Placeholder content
echo "<h1>Stats Page</h1>";
echo "<p>Course ID: $course_id</p>";
echo "<p>Section Number: $section_number</p>";

// Add your specific logic here for the Stats page
?>
