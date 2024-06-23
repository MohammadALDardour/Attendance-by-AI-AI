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

// Fetch excuses for the current course and section
$query = "SELECT * FROM excuses 
          WHERE course_id = '$course_id' AND section_number = '$section_number'";
$excuses_result = mysqli_query($conn, $query);

$excuses = [];
while ($row = mysqli_fetch_assoc($excuses_result)) {
    $excuses[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excuses Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="e.css">
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
                    <th>ID</th>
                    <th>Excuse Picture</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($excuses as $excuse) : ?>
                    <tr>
                        <td><?php echo $excuse['student_id']; ?></td>
                        <td><img src="data:image/jpeg;base64,<?php echo base64_encode($excuse['excuse_pic']); ?>" alt="Excuse Picture" onclick="openModal(this)"></td>
                        <td class="action-buttons">
                            <button onclick="dismissExcuse(<?php echo $excuse['excuse_id']; ?>)">Dismiss</button>
                            <button onclick="window.location.href='course_page_edit.php?course_id=<?php echo $course_id; ?>&section_number=<?php echo $section_number; ?>'">Edit</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img id="modal-img" src="" alt="Zoomed Picture">
        </div>
    </div>

    <script>
        function openModal(img) {
            const modal = document.getElementById("myModal");
            const modalImg = document.getElementById("modal-img");
            modal.style.display = "flex";
            modalImg.src = img.src;
        }

        function closeModal() {
            const modal = document.getElementById("myModal");
            modal.style.display = "none";
        }

        document.getElementById('logout-btn').addEventListener('click', function() {
            window.location.href = 'index.html';
        });

        document.getElementById('notification-btn').addEventListener('click', function() {
            // Add notification functionality here
        });

        function dismissExcuse(excuseId) {
            if (confirm('Are you sure you want to dismiss this excuse?')) {
                window.location.href = 'dismiss_excuse.php?excuse_id=' + excuseId + '&course_id=<?php echo $course_id; ?>&section_number=<?php echo $section_number; ?>';
            }
        }
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>
