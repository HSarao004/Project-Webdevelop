<?php
// Include your database connection file
require 'connect.php';

// Create Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $category_name = filter_input(INPUT_POST, 'category_name', FILTER_SANITIZE_STRING);

    $query = "INSERT INTO category (category) VALUES (:category_name)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':category_name', $category_name);

    if ($stmt->execute()) {
        echo "Category created successfully!";
    } else {
        echo "Error creating category: " . $stmt->errorInfo()[2];
    }
}

// Update Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $new_category_name = filter_input(INPUT_POST, 'new_category_name', FILTER_SANITIZE_STRING);

    $query = "UPDATE category SET category = :new_category_name WHERE category_id = :category_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':new_category_name', $new_category_name);
    $stmt->bindParam(':category_id', $category_id);

    if ($stmt->execute()) {
        echo "Category updated successfully!";
    } else {
        echo "Error updating category: " . $stmt->errorInfo()[2];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create or Update Category</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 50px;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #333;
        }

        a:hover {
            color: #000;
        }
    </style>
</head>
<body>

    <h1>Create or Update Category</h1>

    <!-- HTML form for creating a new category -->
    <form action="category.php" method="POST">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" required>
        <input type="submit" value="Create Category" name="create_category">
    </form>

    <!-- HTML form for updating an existing category -->
    <form action="category.php" method="POST">
        <label for="category_id">Select Category:</label>
        <select id="category_id" name="category_id">
            <?php
            $query = "SELECT * FROM category";
            $stmt = $db->query($query);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='" . $row['category_id'] . "'>" . $row['category'] . "</option>";
            }
            ?>
        </select>
        <label for="new_category_name">New Category Name:</label>
        <input type="text" id="new_category_name" name="new_category_name" required>
        <input type="submit" value="Update Category" name="update_category">
    </form>

    <a href="index.php">Home</a>
</body>
</html>
