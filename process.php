<?php
// Include database connection file
require 'connect.php';
require 'authenticate.php';

$error_message = '';
$uploadOk = 1; // Initialize $uploadOk variable

// Fetch categories from the database
$category_query = "SELECT * FROM category";
$category_statement = $db->query($category_query);
$categories = $category_statement->fetchAll(PDO::FETCH_ASSOC);

// Function to check if the file is a valid image
function file_is_an_image($temporary_path, $new_path) {
    $allowed_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
    $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];

    $image_size_info = getimagesize($temporary_path);
    if ($image_size_info !== false) {
        $actual_mime_type = $image_size_info['mime'];
        $actual_file_extension = pathinfo($new_path, PATHINFO_EXTENSION);

        $file_extension_is_valid = in_array($actual_file_extension, $allowed_file_extensions);
        $mime_type_is_valid = in_array($actual_mime_type, $allowed_mime_types);

        return $file_extension_is_valid && $mime_type_is_valid;
    } else {
        return false; // Failed to get image size information
    }
}

// Function to resize image to a maximum width of 500 pixels
function resize_image($source_path, $target_path) {
    list($source_width, $source_height, $source_type) = getimagesize($source_path);

    // Calculate new height based on a maximum width of 500 pixels
    $target_width = 500;
    $target_height = round($source_height * ($target_width / $source_width));

    // Create a new image resource
    $target_image = imagecreatetruecolor($target_width, $target_height);

    // Load the original image
    switch ($source_type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        default:
            return false; // Unsupported image type
    }

    // Resize the image
    imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);

    // Save the resized image
    imagejpeg($target_image, $target_path);

    // Free up memory
    imagedestroy($source_image);
    imagedestroy($target_image);

    return true;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $make = filter_input(INPUT_POST, 'make', FILTER_SANITIZE_FULL_SPECIAL_CHARS);//sanitization
    $model = filter_input(INPUT_POST, 'model', FILTER_SANITIZE_FULL_SPECIAL_CHARS);//sanitization
    $watchYear = filter_input(INPUT_POST, 'watchYear', FILTER_VALIDATE_INT);//sanitization
    $movement = filter_input(INPUT_POST, 'movement', FILTER_SANITIZE_FULL_SPECIAL_CHARS);//sanitization
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);//sanitization

    // Check if an image file is uploaded
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the uploaded file is a valid image
        if (!file_is_an_image($_FILES["image"]["tmp_name"], $targetFile)) {
            $error_message = "Error: File is not a valid image.";
            $uploadOk = 0;
        }

        // Check file size and allow certain file formats
        if ($_FILES["image"]["size"] > 500000) {
            $error_message = "Error: File is too large.";
            $uploadOk = 0;
        }

        // Check file extension
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error_message = "Error: Only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                // Resize the uploaded image to a maximum width of 500 pixels
                $resized_image_path = $targetDir . "resized_" . basename($_FILES["image"]["name"]);
                resize_image($targetFile, $resized_image_path);
                $imagePath = $resized_image_path;
            } else {
                $error_message = "Error: There was an error uploading your file.";
            }
        }
    } else {
        // If no image uploaded, set a default image path or leave it empty
        $imagePath = ''; // You can set a default image path here if needed
    }

    // Insert the watch post into the database
    if ($uploadOk) {
        $query = "INSERT INTO watchPost (make, model, watchYear, movement, image_url, date_created, category)
                  VALUES (:make, :model, :watchYear, :movement, :imagePath, NOW(), :category)";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':make', $make);
        $stmt->bindParam(':model', $model);
        $stmt->bindParam(':watchYear', $watchYear);
        $stmt->bindParam(':movement', $movement);
        // If no image uploaded, set a default value for image_url
        $stmt->bindParam(':imagePath', $imagePath, PDO::PARAM_STR | PDO::PARAM_NULL);
        $stmt->bindParam(':category', $category);

        if ($stmt->execute()) {
            echo "Watch post added successfully!";
        } else {
            $error_message = "Error: Unable to add watch post.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Watch Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .nav_bar {
            background-color: #333;
            padding: 10px;
            text-align: center;
        }

        .nav_bar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }

        h2 {
            text-align: center;
        }

        form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            background-color: red;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="nav_bar">
    <a href="userpage.php">Homepage</a>
    <a href="adindex.php">Edit or delete Post</a>
</div>
<h2>Add Watch Post</h2>
<?php if (!empty($error_message)) : ?>
    <p><?= $error_message ?></p>
<?php endif; ?>
<form action="process.php" method="POST" enctype="multipart/form-data">
    <label for="make">Make:</label>
    <input type="text" id="make" name="make" required>

    <label for="model">Model:</label>
    <input type="text" id="model" name="model" required>

    <label for="watchYear">Year:</label>
    <input type="number" id="watchYear" name="watchYear" required>

    <label for="movement">Movement:</label>
    <input type="text" id="movement" name="movement" required>

    <label for="category">Category:</label>
    <select id="category" name="category" required>
        <option value="">Select a category</option>
        <?php foreach ($categories as $category) : ?>
            <option value="<?= $category['category'] ?>"><?= $category['category'] ?></option>
        <?php endforeach; ?>
    </select>

    <label for="image">Image:</label>
    <input type="file" id="image" name="image" accept="image/*">

    <input type="submit" value="Add Watch Post" name="submit">
</form>
</body>
</html>