<?php

require('connect.php');

session_start();

function getComments($postId, $db) {
    $query = "SELECT * FROM reviews WHERE id = :post_id AND status = ''";
    $statement = $db->prepare($query);
    $statement->bindValue(':post_id', $postId);
    $statement->execute();
    return $statement->fetchAll();
}

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);//Sanitization

    if ($id !== false) {
        $query = "SELECT * FROM watchpost WHERE id = :id LIMIT 1";
        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();
        $watch = $statement->fetch();

        if (is_array($watch) && !empty($watch)) {
            $comments = getComments($id, $db);
        } else {
            echo "Watch details not found.";
            exit();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
    
    $commentContent = trim($_POST['comment']);
    if (!empty($commentContent)) {
        $insertQuery = "INSERT INTO reviews (id, name, content) VALUES (:id, :name, :content)";
        $insertStatement = $db->prepare($insertQuery);
        $insertStatement->bindValue(':id', $id);
        $insertStatement->bindValue(':name', $_SESSION['username']);
        $insertStatement->bindValue(':content', $commentContent);
        $insertStatement->execute();
        
        header("Location: fullpost.php?id=$id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <style>
       body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f5f5f5;
    }

    .container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        font-size: 24px;
        margin-bottom: 20px;
    }

    img {
        max-width: 100%;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    h2 {
        font-size: 20px;
        margin-bottom: 10px;
    }

    p {
        margin-bottom: 10px;
    }

    p:first-child {
        margin-top: 0;
    }

    .not-found {
        color: #ff0000;
        font-weight: bold;
    }

    .go-back {
        margin-bottom: 20px;
        display: inline-block;
        text-decoration: none;
        color: #333;
        background-color: #f0f0f0;
        padding: 10px 20px;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .go-back:hover {
        background-color: #ddd;
    }

    .comments {
        margin-top: 20px;
        border-top: 1px solid #ccc;
        padding-top: 20px;
    }

    .comment {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #ccc;
    }

    .comment p {
        margin: 0;
    }

    .comment .author {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .comment-form {
        margin-top: 20px;
    }

    .comment-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-bottom: 10px;
        resize: vertical;
    }

    .comment-form button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .comment-form button:hover {
        background-color: #45a049;
    }

    .login-link {
        color: blue;
        text-decoration: underline;
    }

    .login-link:hover {
        color: #0056b3;
    }
   </style>
</head>
<body>
    <a href="userpage.php" class="go-back">Go Back</a>
    <div class="watch_details">
        <?php if(isset($watch)): ?>
            <div class="watch_details">
                <?php if(isset($watch['image_url']) && !empty($watch['image_url'])): ?>
                    <img src="<?= $watch['image_url'] ?>" alt="Watch Image">
                <?php endif; ?>
                <h2><?= isset($watch['make']) ? $watch['make'] : 'N/A' ?></h2>
                <p>Model: <?= isset($watch['model']) ? $watch['model'] : 'N/A' ?></p>
                <p>Year: <?= isset($watch['watchYear']) ? $watch['watchYear'] : 'N/A' ?></p>
                <p>Movement: <?= isset($watch['movement']) ? $watch['movement'] : 'N/A' ?></p>
                
                <p>Category: <?= isset($watch['category']) ? $watch['category'] : 'N/A' ?></p>
                <p>Created: <?= isset($watch['date_created']) ? $watch['date_created'] : 'N/A' ?></p>
            </div>
            <div class="comments">
                <h2>Comments</h2>
                <?php if (!empty($comments)) : ?>
                    <?php foreach ($comments as $comment) : ?>
                        <div class="comment">
                            <p class="author"><?= $comment['name'] ?>:</p>
                            <p><?= $comment['content'] ?></p>
                            <p>Date Posted: <?= $comment['date_posted'] ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>No comments yet.</p>
                <?php endif; ?>
            </div>
            <div class="comment-form">
                <h2>Add Comment</h2>
                <?php if(isset($_SESSION['username'])): ?>
                    <form method="post" action="">
                        <textarea name="comment" placeholder="Write your comment here..." required></textarea><br>
                        <button type="submit">Submit Comment</button>
                    </form>
                <?php else: ?>
                    <p>Please <a href="login.php">login</a> to add a comment.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="not-found">No watch found with the provided ID.</p>
        <?php endif; ?>
    </div>
</body>
</html>
