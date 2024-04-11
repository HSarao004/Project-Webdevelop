<?php
require 'connect.php'; // Include database connection file

// Initialize variables
$email = $password = $confirmPassword = '';
$error = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate email
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if the passwords match
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user data into the database
            $query = "INSERT INTO user (username, password) VALUES (:email, :password)";
            $statement = $db->prepare($query);
            $statement->bindParam(':email', $email);
            $statement->bindParam(':password', $hashedPassword);
            
            if ($statement->execute()) {
                // Registration successful
                $message = 'Registration successful!';
            } else {
                $error = 'Error registering user';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
       body {
    font-family: Arial, sans-serif;
}

.container {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.error {
    color: red;
    margin-bottom: 10px;
}

.success {
    color: green;
    margin-bottom: 10px;
}

form {
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 5px;
}

input[type="email"],
input[type="password"] {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-bottom: 10px;
}

button[type="submit"] {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

    </style>
</head>
<body>
    <div class="container">
        <h2>User Registration</h2>
        <?php if (!empty($error)) : ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($message)) : ?>
            <div class="success"><?= $message ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
