<?php

/************ 
        
    Name: Your Name
    Date: Current Date
    Description: This PHP script fetches and displays details of a watch from the watchpost table.

****************/

require('connect.php');

if (isset($_GET['id'])) {
    // Sanitize the id parameter
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id !== false) {
        $query = "SELECT * FROM watchpost WHERE id = :id LIMIT 1";
        $statement = $db->prepare($query);

        $statement->bindValue(':id', $id);
        $statement->execute();
        $watch = $statement->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .watch_details {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .watch_details h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .watch_details img {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .watch_details h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .watch_details p {
            margin-bottom: 10px;
        }

        .watch_details p:first-child {
            margin-top: 0;
        }

        .watch_details .not-found {
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
    </style>
</head>
<body>
    <a href="index.php" class="go-back">Go Back</a>
    <div class="watch_details">
        <h1>Watch Details</h1>

        <?php if(isset($watch)): ?>
            <div class="watch_details">
                <img src="<?= $watch['image_url'] ?>" alt="Watch Image">
                <h2><?= $watch['make'] ?></h2>
                <p>Model: <?= $watch['model'] ?></p>
                <p>Year: <?= $watch['watchYear'] ?></p>
                <p>Movement: <?= $watch['movement'] ?></p>
                <p>Description: <?= $watch['watchDes'] ?></p>
                <p>Category: <?= $watch['category'] ?></p>
                <p>Created: <?= $watch['date_created'] ?></p>
            </div>
        <?php else: ?>
            <p class="not-found">No watch found with the provided ID.</p>
        <?php endif; ?>
    </div>
</body>
</html>

