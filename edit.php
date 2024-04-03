<?php
require('connect.php');
require('authenticate.php');

// Function to resize image
function resizeImage($imagePath, $newImagePath, $maxWidth) {
    // Get original image dimensions
    list($origWidth, $origHeight) = getimagesize($imagePath);

    // Calculate aspect ratio
    $ratio = $origWidth / $maxWidth;

    // Calculate new dimensions
    $newWidth = $maxWidth;
    $newHeight = $origHeight / $ratio;

    // Create a new image resource
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Load the original image
    $origImage = imagecreatefromjpeg($imagePath);

    // Resize the image
    imagecopyresampled($newImage, $origImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    // Save the resized image
    imagejpeg($newImage, $newImagePath);

    // Free up memory
    imagedestroy($newImage);
    imagedestroy($origImage);
}

// Function to check if the uploaded file is a valid image
function isValidImage($file) {
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}

// Function to delete image file
function deleteImageFile($imageUrl) {
    if (!empty($imageUrl) && file_exists($imageUrl)) {
        unlink($imageUrl); // Delete the image file
    }
}

// Check if form is submitted for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make']) && isset($_POST['model']) && isset($_POST['watchYear']) && isset($_POST['movement']) && isset($_POST['id'])) {
    // Sanitize user input
    $make = filter_input(INPUT_POST, 'make', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $model = filter_input(INPUT_POST, 'model', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $watchYear = filter_input(INPUT_POST, 'watchYear', FILTER_SANITIZE_NUMBER_INT);
    $movement = filter_input(INPUT_POST, 'movement', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

    // Check if image removal checkbox is checked
    $removeImage = isset($_POST['remove_image']) ? true : false;

    // Check if a new image is uploaded and if it's valid
    $newImageUploaded = isset($_FILES['image']['name']) && !empty($_FILES['image']['name']);
    if ($newImageUploaded && !isValidImage($_FILES['image'])) {
        echo "Error: Invalid image format. Please upload a valid image (jpg, jpeg, png, gif)";
        exit;
    }

    // Get the image URL of the post
    $queryImageUrl = "SELECT image_url FROM watchPost WHERE id = :id";
    $statementImageUrl = $db->prepare($queryImageUrl);
    $statementImageUrl->bindValue(':id', $id, PDO::PARAM_INT);
    $statementImageUrl->execute();
    $imageUrl = $statementImageUrl->fetchColumn();

    // Build the parameterized SQL query and bind the sanitized values
    $query = "UPDATE watchPost SET make = :make, model = :model, watchYear = :watchYear, movement = :movement";

    // Add image_url conditionally if image should be removed
    if ($removeImage) {
        $query .= ", image_url = NULL";
        // Delete the image file
        deleteImageFile($imageUrl);
    } elseif ($newImageUploaded) {
        // Upload new image
        $image_path = 'uploads/' . basename($_FILES['image']['name']);
        $resized_image_path = 'uploads/resized_' . basename($_FILES['image']['name']);

        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

        // Resize the image
        resizeImage($image_path, $resized_image_path, 500);

        $query .= ", image_url = :image_url";
    }

    $query .= " WHERE id = :id";

    $statement = $db->prepare($query);
    $statement->bindValue(':make', $make);
    $statement->bindValue(':model', $model);
    $statement->bindValue(':watchYear', $watchYear);
    $statement->bindValue(':movement', $movement);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);

    // Bind the new image URL if uploaded
    if ($newImageUploaded) {
        $statement->bindValue(':image_url', $resized_image_path);
    }

    // Execute the UPDATE
    if ($statement->execute()) {
        echo "Record updated successfully";
        // Redirect to index.php after updating
        header("Location: index.php");
        exit;
    } else {
        echo "Error updating record: " . $statement->errorInfo()[2];
    }
} elseif (isset($_GET['id'])) {
    // Sanitize the id from INPUT_GET
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    // Build the parameterized SQL query using the filtered id
    $query = "SELECT * FROM watchPost WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);

    // Execute the SELECT and fetch the single row returned
    $statement->execute();
    $watch = $statement->fetch();
} else {
    $id = false; // False if we are not UPDATING or SELECTING.
}

// Check if form is submitted for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (isset($_POST['id'])) {
        // Get the image URL of the post to be deleted
        $queryImageUrl = "SELECT image_url FROM watchPost WHERE id = :id";
        $statementImageUrl = $db->prepare($queryImageUrl);
        $statementImageUrl->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
        $statementImageUrl->execute();
        $imageUrl = $statementImageUrl->fetchColumn();

        // Delete the post from the database
        $deleteQuery = "DELETE FROM watchPost WHERE id = :id";
        $deleteStatement = $db->prepare($deleteQuery);
        $deleteStatement->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
        $deleteStatement->execute();

        // Delete the image file
        deleteImageFile($imageUrl);

        header("Location: adindex.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Edit this Watch Post!</title>
</head>
<body>
<div class="home_blog">
    <h1>Make your changes here!</h1>
</div>
<?php if ($id): ?>
    <form id="edit_form" method="post" enctype="multipart/form-data">
        <!-- Hidden input for the watch primary key -->
        <input type="hidden" name="id" value="<?= $watch['id'] ?>">

        <label for="make">Make</label>
        <input type="text" id="make" name="make" value="<?= htmlspecialchars($watch['make']) ?>" required>

        <label for="model">Model</label>
        <input type="text" id="model" name="model" value="<?= htmlspecialchars($watch['model']) ?>" required>

        <label for="watchYear">Year</label>
        <input type="number" id="watchYear" name="watchYear" value="<?= $watch['watchYear'] ?>" required>

        <label for="movement">Movement</label>
        <input type="text" id="movement" name="movement" value="<?= htmlspecialchars($watch['movement']) ?>" required>

        <?php if (!empty($watch['image_url'])): ?>
            <img src="<?= $watch['image_url'] ?>" alt="Watch Image">
            <input type="checkbox" id="remove_image" name="remove_image" value="1">
            <label for="remove_image">Remove Image</label>
        <?php else: ?>
            <!-- Input field for updating the image -->
            <label for="image">Upload Image:</label>
            <input type="file" id="image" name="image" accept="image/*">
        <?php endif; ?>

        
        <input class="button" type="submit" value="Update Post">
    </form>

    <form method="post" onsubmit="return confirm('Are you sure you want to delete this post?');">
        <!-- Hidden input for the watch primary key -->
        <input type="hidden" name="id" value="<?= $watch['id'] ?>">
        <button class="button" name="delete" type="submit">Delete</button>
    </form>
<?php else: ?>
    <?php header("Location: index.php"); ?>
    <?php exit; ?>
<?php endif; ?>
</body>
</html>
