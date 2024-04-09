<?php
require('connect.php');
require('authenticate.php');

$queryCategories = "SELECT * FROM category";
$stmtCategories = $db->query($queryCategories);
$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

function resizeImage($imagePath, $newImagePath, $maxWidth) {
    list($origWidth, $origHeight) = getimagesize($imagePath);
    $ratio = $origWidth / $maxWidth;
    $newWidth = $maxWidth;
    $newHeight = $origHeight / $ratio;
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    $origImage = imagecreatefromjpeg($imagePath);
    imagecopyresampled($newImage, $origImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    imagejpeg($newImage, $newImagePath);
    imagedestroy($newImage);
    imagedestroy($origImage);
}

function isValidImage($file) {
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}

function deleteImageFile($imageUrl) {
    if (!empty($imageUrl) && file_exists($imageUrl)) {
        unlink($imageUrl);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make']) && isset($_POST['model']) && isset($_POST['watchYear']) && isset($_POST['movement']) && isset($_POST['id'])) {
    $make = filter_input(INPUT_POST, 'make', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $model = filter_input(INPUT_POST, 'model', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $watchYear = filter_input(INPUT_POST, 'watchYear', FILTER_SANITIZE_NUMBER_INT);
    $movement = filter_input(INPUT_POST, 'movement', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

    $removeImage = isset($_POST['remove_image']) ? true : false;

    $newImageUploaded = isset($_FILES['image']['name']) && !empty($_FILES['image']['name']);
    if ($newImageUploaded && !isValidImage($_FILES['image'])) {
        echo "Error: Invalid image format. Please upload a valid image (jpg, jpeg, png, gif)";
        exit;
    }

    $queryImageUrl = "SELECT image_url FROM watchPost WHERE id = :id";
    $statementImageUrl = $db->prepare($queryImageUrl);
    $statementImageUrl->bindValue(':id', $id, PDO::PARAM_INT);
    $statementImageUrl->execute();
    $imageUrl = $statementImageUrl->fetchColumn();

    $query = "UPDATE watchPost SET make = :make, model = :model, watchYear = :watchYear, movement = :movement";

    if ($removeImage) {
        $query .= ", image_url = NULL";
        deleteImageFile($imageUrl);
    } elseif ($newImageUploaded) {
        $image_path = 'uploads/' . basename($_FILES['image']['name']);
        $resized_image_path = 'uploads/resized_' . basename($_FILES['image']['name']);

        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

        resizeImage($image_path, $resized_image_path, 500);

        $query .= ", image_url = :image_url";
    }

    if (isset($_POST['category'])) {
        $query .= ", category = :category";
    }

    $query .= " WHERE id = :id";

    $statement = $db->prepare($query);
    $statement->bindValue(':make', $make);
    $statement->bindValue(':model', $model);
    $statement->bindValue(':watchYear', $watchYear);
    $statement->bindValue(':movement', $movement);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);

    if ($newImageUploaded) {
        $statement->bindValue(':image_url', $resized_image_path);
    }

    if (isset($_POST['category'])) {
        $statement->bindValue(':category', $_POST['category']);
    }

    if ($statement->execute()) {
        echo "Record updated successfully";
        header("Location: index.php");
        exit;
    } else {
        echo "Error updating record: " . $statement->errorInfo()[2];
    }
} elseif (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    $query = "SELECT * FROM watchPost WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $watch = $statement->fetch();
} else {
    $id = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (isset($_POST['id'])) {
        $queryImageUrl = "SELECT image_url FROM watchPost WHERE id = :id";
        $statementImageUrl = $db->prepare($queryImageUrl);
        $statementImageUrl->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
        $statementImageUrl->execute();
        $imageUrl = $statementImageUrl->fetchColumn();

        $deleteQuery = "DELETE FROM watchPost WHERE id = :id";
        $deleteStatement = $db->prepare($deleteQuery);
        $deleteStatement->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
        $deleteStatement->execute();

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
        <input type="hidden" name="id" value="<?= $watch['id'] ?>">
        <label for="make">Make</label>
        <input type="text" id="make" name="make" value="<?= htmlspecialchars($watch['make']) ?>" required>
        <label for="model">Model</label>
        <input type="text" id="model" name="model" value="<?= htmlspecialchars($watch['model']) ?>" required>
        <label for="watchYear">Year</label>
        <input type="number" id="watchYear" name="watchYear" value="<?= $watch['watchYear'] ?>" required>
        <label for="movement">Movement</label>
        <input type="text" id="movement" name="movement" value="<?= htmlspecialchars($watch['movement']) ?>" required>
        <label for="category_id">Category:</label>
        <select id="category_id" name="category" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['category'] ?>" <?= $watch['category'] == $category['category'] ? 'selected' : '' ?>>
                    <?= $category['category'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <?php if (!empty($watch['image_url'])): ?>
            <img src="<?= $watch['image_url'] ?>" alt="Watch Image">
            <input type="checkbox" id="remove_image" name="remove_image" value="1">
            <label for="remove_image">Remove Image</label>
        <?php else: ?>
            <label for="image">Upload Image:</label>
            <input type="file" id="image" name="image" accept="image/*">
        <?php endif; ?>
        <input class="button" type="submit" value="Update Post">
    </form>
    <form method="post" onsubmit="return confirm('Are you sure you want to delete this post?');">
        <input type="hidden" name="id" value="<?= $watch['id'] ?>">
        <button class="button" name="delete" type="submit">Delete</button>
    </form>
<?php else: ?>
    <?php header("Location: index.php"); ?>
    <?php exit; ?>
<?php endif; ?>
</body>
</html>
