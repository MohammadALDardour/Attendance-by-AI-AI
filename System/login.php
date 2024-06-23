<?php
// Include the database connection file
include 'connection.php';

// Start session
session_start();

// Check post or get method  
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user_id and password from form
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];

    // SQL query to fetch user details based on user_id and password
    $query = "SELECT * FROM users WHERE user_id='$user_id' AND password='$password'";
    $result = mysqli_query($conn, $query);

    // Check if query executed successfully
    if ($result) {
        // Check if user exists
        if (mysqli_num_rows($result) == 1) {
            // Fetch user details
            $row = mysqli_fetch_assoc($result);
            $role_id = $row['role_id'];

            // Set user details in session
            $_SESSION['user_id'] = $row['user_id']; // store info to use everywhere 
            $_SESSION['user_name'] = $row['user_name'];

            // Redirect based on role_id
            if ($role_id == 1) {
                header("Location: teacher_page.php");
                exit();
            } elseif ($role_id == 2) {
                header("Location: student_page.php");
                exit();
            } else {
                echo "Invalid role!";
            }
        } else {
            echo "Invalid username or password!";
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
