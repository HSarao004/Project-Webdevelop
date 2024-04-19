<?php
require('authenticate.php');
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once('connect.php');

// Fetch all users from the database
$query = "SELECT * FROM user";
$stmt = $db->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        // Update user functionality
        $userID = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_NUMBER_INT);//Sanitization
        $newUsername = filter_input(INPUT_POST, 'newUsername', FILTER_SANITIZE_FULL_SPECIAL_CHARS);//Sanitization

        if (!empty($userID) && !empty($newUsername)) {
            $query = "UPDATE user SET username = :username WHERE userID = :userID";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $newUsername);
            $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            if ($stmt->execute()) {
                header("Location: edituser.php");
                exit();
            } else {
                $error = "Error updating user.";
            }
        } else {
            $error = "User ID and new username are required.";
        }
    } elseif (isset($_POST['delete'])) {
        // Delete user functionality
        $userID = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_NUMBER_INT);//Sanitization

        if (!empty($userID)) {
            $query = "DELETE FROM user WHERE userID = :userID";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            if ($stmt->execute()) {
                header("Location: edituser.php");
                exit();
            } else {
                $error = "Error deleting user.";
            }
        } else {
            $error = "User ID is required.";
        }
    } elseif (isset($_POST['create'])) {
        // Create new user functionality
        $newUsername = filter_input(INPUT_POST, 'newUsername', FILTER_SANITIZE_FULL_SPECIAL_CHARS);//Sanitization

        if (!empty($newUsername)) {
            $newPassword = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
            $query = "INSERT INTO user (username, password) VALUES (:username, :password)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $newUsername);
            $stmt->bindParam(':password', $newPassword);
            if ($stmt->execute()) {
                header("Location: edituser.php");
                exit();
            } else {
                $error = "Error creating user.";
            }
        } else {
            $error = "New username is required.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Users</title>
    <style>
         body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        h2 {
            color: #333;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        table th {
            background-color: #f2f2f2;
        }

        input[type="text"],
        input[type="password"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        button[type="submit"] {
            background-color: red;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        a {
            display: block;
            margin-top: 20px;
            color: #333;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <h2>Edit Users</h2>
    <?php if ($error) : ?>
        <p><?= $error ?></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) : ?>
                <tr>
                    <td><?= $user['userID'] ?></td>
                    <td><?= $user['username'] ?></td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="userID" value="<?= $user['userID'] ?>">
                            <input type="email" name="newUsername" placeholder="New Username">
                            <input type="password" name="newPassword" placeholder="New Password">
                            <button type="submit" name="update">Update</button>
                            <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <tr>
                <td></td>
                <td><h2>Add new user here </h2>
                    <form action="" method="post">
                        <input type="email" name="newUsername" placeholder="New Username" required>
                        <input type="password" name="newPassword" placeholder="New Password" required>
                        <button type="submit" name="create">Create</button>
                    </form>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <a href="adindex.php">Admin Dashboard</a>
    <a href="logout.php">Logout</a>
    
</body>

</html>
