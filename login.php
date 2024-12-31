<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "business_profile";

// Establishing a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Starting a session
session_start();

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input_username = $_POST['username'] ?? '';
    $input_password = $_POST['password'] ?? '';

    // Query to find the user in the database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $input_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found, verify the password
            $user = $result->fetch_assoc();
            if (password_verify($input_password, $user['password'])) {
                // Successful login
                $_SESSION['username'] = $user['username']; // Store username in session
                $_SESSION['is_admin'] = (bool)$user['is_admin']; // Store admin status in session

                // Redirect based on user role
                $redirect_url = $user['is_admin'] 
                    ? "/business_profile/public/edit.html" 
                    : "/business_profile/public/index.html";
                
                header("Location: $redirect_url");
                exit(); // Terminate script after redirection
            } else {
                // Invalid password
                $_SESSION['login_error'] = "Incorrect password.";
            }
        } else {
            // Username not found
            $_SESSION['login_error'] = "Username not found.";
        }

        $stmt->close();
    } else {
        // Statement preparation failed
        $_SESSION['login_error'] = "An error occurred. Please try again later.";
    }
}

// Closing the database connection
$conn->close();

// Display login error message if available
if (isset($_SESSION['login_error'])) {
    echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['login_error']) . "</div>";
    unset($_SESSION['login_error']); // Clear error message after displaying
}
?>
