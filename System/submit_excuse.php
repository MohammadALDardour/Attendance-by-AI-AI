<?php

include 'connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Get course_id and section_number from URL
$course_id = $_GET['course_id'] ?? '';
$section_number = $_GET['section_number'] ?? '';
$student_id = $_SESSION['user_id'];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excuse_pic'])) {
    $date = $_POST['date'];
    $excuse_pic = addslashes(file_get_contents($_FILES['excuse_pic']['tmp_name']));

    // Update the excuse picture in the excuses table if the record exists
    $query = "UPDATE excuses SET excuse_pic='$excuse_pic' 
              WHERE student_id='$student_id' AND course_id='$course_id' AND section_number='$section_number' AND excuse_date='$date'";
    mysqli_query($conn, $query);

    // Redirect to avoid form resubmission
    header("Location: submit_excuse.php?course_id=$course_id&section_number=$section_number");
    exit();
}

// Fetch course details
$query = "SELECT courses.course_symbol, courses.course_id, teacher_courses.section_number, course_sections.time 
          FROM teacher_courses 
          INNER JOIN courses ON teacher_courses.course_number = courses.course_id 
          INNER JOIN course_sections ON teacher_courses.section_number = course_sections.section_number
          WHERE teacher_courses.course_number = '$course_id' AND teacher_courses.section_number = '$section_number'";
$result = mysqli_query($conn, $query);

$course_details = mysqli_fetch_assoc($result);

// Fetch excuses records for the current student in the current course
$query = "SELECT excuse_date, excuse_pic FROM excuses 
          WHERE course_id = '$course_id' AND section_number = '$section_number' AND student_id = '$student_id'";
$excuse_result = mysqli_query($conn, $query);

$excuses = [];
while ($row = mysqli_fetch_assoc($excuse_result)) {
    $excuses[] = $row;
}

$absence_count = count($excuses);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit an Excuse</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="se.css">
</head>
<body>
    <div class="sidebar">
        <h2>Student Panel</h2>
        <button class="button" onclick="window.location.href='student_page.php'">Student Page</button>
        <button class="button" onclick="window.location.href='course_page_std.php?course_id=<?php echo $course_id; ?>&section_number=<?php echo $section_number; ?>'">Course Absences</button>
    </div>
    <div class="main-content">
        <div class="top-bar">
            <div>
                <h3><?php echo isset($course_details['course_symbol']) ? $course_details['course_symbol'] . " " . $course_details['course_id'] : ''; ?></h3>
                <p>Section: <?php echo isset($course_details['section_number']) ? $course_details['section_number'] : ''; ?></p>
                <p>Time: <?php echo isset($course_details['time']) ? $course_details['time'] : ''; ?></p>
            </div>
            <div class="title">Attendance By AI</div>
            <div class="buttons">
                <button id="notification-btn" class="button">Notifications</button>
                <button id="logout-btn" class="button logout-btn">Logout</button>
            </div>
        </div>
        <?php if ($absence_count > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Index</th>
                        <th>Date</th>
                        <th>Upload an Excuse</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($excuses as $index => $excuse): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $excuse['excuse_date']; ?></td>
                            <td>
                                <?php if (!empty($excuse['excuse_pic'])): ?>
                                    <p>Excuse uploaded</p>
                                <?php else: ?>
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="date" value="<?php echo $excuse['excuse_date']; ?>">
                                        <input type="file" name="excuse_pic" accept="image/*" onchange="this.form.submit()" required>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3">Total Number of Excuses allowed: <?php echo $absence_count; ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-absences">
                <p>You do not have any Excuses left for this course.</p>
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
