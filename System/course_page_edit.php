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
$query = "SELECT courses.course_symbol, courses.course_id, courses.day, teacher_courses.section_number, course_sections.time 
          FROM teacher_courses 
          INNER JOIN courses ON teacher_courses.course_number = courses.course_id 
          INNER JOIN course_sections ON teacher_courses.section_number = course_sections.section_number
          WHERE teacher_courses.course_number = '$course_id' AND teacher_courses.section_number = '$section_number' AND teacher_courses.teacher_id = '{$_SESSION['user_id']}'";
$result = mysqli_query($conn, $query);

$course_details = mysqli_fetch_assoc($result);

// Get the current month
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Fetch attendance records for the current month
$query = "SELECT student_id, date, status FROM attendance 
          WHERE course_id = '$course_id' AND section_number = '$section_number' AND DATE_FORMAT(date, '%Y-%m') = '$month'";
$attendance_result = mysqli_query($conn, $query);

$attendance = [];
while ($row = mysqli_fetch_assoc($attendance_result)) {
    $attendance[] = $row;
}

// Fetch all students for the course and section
$query = "SELECT DISTINCT student_id FROM attendance 
          WHERE course_id = '$course_id' AND section_number = '$section_number'";
$students_result = mysqli_query($conn, $query);

$students = [];
while ($row = mysqli_fetch_assoc($students_result)) {
    $students[] = $row['student_id'];
}

// Generate the dates for the current month
$days_of_week = $course_details['day'] == 'sun' ? ['Sunday', 'Tuesday', 'Thursday'] : ['Monday', 'Wednesday'];
$start_date = new DateTime("first day of $month");
$end_date = new DateTime("last day of $month");

$dates = [];
while ($start_date <= $end_date) {
    if (in_array($start_date->format('l'), $days_of_week)) {
        $dates[] = $start_date->format('Y-m-d');
    }
    $start_date->modify('+1 day');
}

// Fetch pictures for the dates
$pictures = [];
foreach ($dates as $date) {
    $query = "SELECT pic FROM picture WHERE date = '$date'";
    $picture_result = mysqli_query($conn, $query);
    $picture_row = mysqli_fetch_assoc($picture_result);
    $pictures[$date] = $picture_row['pic'] ?? null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cpe.css">
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
                
                <button id="logout-btn">Logout</button>
            </div>
        </div>
        <div class="navigation">
            <button onclick="changeMonth(-1)">Previous Month</button>
            <span><?php echo date('F Y', strtotime($month)); ?></span>
            <button onclick="changeMonth(1)">Next Month</button>
        </div>
        <form method="post" action="save_edits.php">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <input type="hidden" name="section_number" value="<?php echo $section_number; ?>">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <?php foreach ($dates as $date) : ?>
                            <th><?php echo date('D, d M', strtotime($date)); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student_id) : ?>
                        <tr>
                            <td><?php echo $student_id; ?></td>
                            <?php foreach ($dates as $date) : ?>
                                <td class="<?php
                                    $status = '-';
                                    foreach ($attendance as $record) {
                                        if ($record['student_id'] == $student_id && $record['date'] == $date) {
                                            $status = $record['status'] == 1 ? 'Yes' : 'No';
                                            echo $status == 'Yes' ? 'yes' : 'no';
                                            break;
                                        }
                                    }
                                    ?>"><?php
                                    if ($status == 'No') {
                                        echo "<input type='checkbox' name='attendance[$student_id][$date]' value='1'> No";
                                    } else {
                                        echo $status;
                                    }
                                    ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td>Picture</td>
                        <?php foreach ($dates as $date) : ?>
                            <td>
                                <?php if (!empty($pictures[$date])) : ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($pictures[$date]); ?>" alt="Picture for <?php echo $date; ?>" onclick="openModal(this)">
                                <?php else : ?>
                                    No picture
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
            <div class="edit-buttons">
                <button type="submit" class="button">Save</button>
                <button type="button" class="button" onclick="window.location.href='course_page.php?course_id=<?php echo $course_id; ?>&section_number=<?php echo $section_number; ?>'">Cancel</button>
            </div>
        </form>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img id="modal-img" src="" alt="Zoomed Picture">
        </div>
    </div>

    <script>
        function changeMonth(offset) {
            const currentMonth = new Date('<?php echo $month; ?>-01');
            currentMonth.setMonth(currentMonth.getMonth() + offset);
            const newMonth = currentMonth.toISOString().slice(0, 7);
            window.location.href = `course_page_edit.php?course_id=<?php echo $course_id; ?>&section_number=<?php echo $section_number; ?>&month=${newMonth}`;
        }

        document.getElementById('logout-btn').addEventListener('click', function() {
            window.location.href = 'index.html';
        });

        document.getElementById('notification-btn').addEventListener('click', function() {
            // Add notification functionality here
        });

        

        function openModal(img) {
            const modal = document.getElementById("myModal");
            const modalImg = document.getElementById("modal-img");
            modal.style.display = "flex";
            modalImg.src = img.src;
            modalImg.style.width = img.naturalWidth + 'px';
            modalImg.style.height = img.naturalHeight + 'px';
        }

        function closeModal() {
            const modal = document.getElementById("myModal");
            modal.style.display = "none";
        }
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>
