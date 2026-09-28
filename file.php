<?php

// Database connection
$host = "localhost";
$dbname = "from";
$dbusername = "root";
$dbpassword = ""; // XAMPP default password is usually empty

$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Run only when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form data
    $firstName = trim($_POST["firstName"] ?? "");
    $lastName = trim($_POST["lastName"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";

    // Basic validation
    if (
        empty($firstName) ||
        empty($lastName) ||
        empty($email) ||
        empty($username) ||
        empty($password) ||
        empty($confirmPassword)
    ) {
        die("Please fill in all fields.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    if (strlen($password) < 6 || strlen($password) > 20) {
        die("Password must be between 6 and 20 characters.");
    }

    if ($password !== $confirmPassword) {
        die("Passwords do not match.");
    }

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement
    $sql = "INSERT INTO users 
            (first_name, last_name, email, username, password)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL preparation failed: " . $conn->error);
    }

    // Bind values: 5 strings
    $stmt->bind_param(
        "sssss",
        $firstName,
        $lastName,
        $email,
        $username,
        $hashedPassword
    );

    // Execute insertion
    if ($stmt->execute()) {
        echo "Account created successfully!";
    } else {
        if ($conn->errno === 1062) {
            echo "Email or username already exists.";
        } else {
            echo "Error creating account: " . $stmt->error;
        }
    }

    $stmt->close();
}

$conn->close();

?>