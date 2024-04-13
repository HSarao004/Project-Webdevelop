<?php
require('authenticate.php');
require('connect.php');

// Process sorting if sorting parameter is present in the URL
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date_created'; // Default sorting by date_created
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc'; // Default sorting order

// Validate sort parameters to prevent SQL injection
$allowed_sort_columns = ['watchYear', 'make', 'date_created'];
$sort_by = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'date_created';
$sort_order = ($sort_order === 'asc' || $sort_order === 'desc') ? $sort_order : 'desc';

// Query to fetch watch posts with sorting
$statement = $db->prepare("SELECT id, make, model, watchYear, movement, image_url, DATE_FORMAT(date_created, '%M %d, %Y, %h:%i %p') AS formatted_date 
                           FROM watchPost 
                           ORDER BY $sort_by $sort_order");
$statement->execute();

// Function to generate alphabet links
function generateAlphabetLinks() {
    $alphabet = range('A', 'Z');
    $links = '';

    foreach ($alphabet as $letter) {
        $links .= '<a href="?letter=' . $letter . '">' . $letter . '</a> ';
    }

    return $links;
}

// Check if a letter filter is applied
$filter_letter = isset($_GET['letter']) ? $_GET['letter'] : '';

// Filter watches if a letter filter is applied
if (!empty($filter_letter)) {
    $statement = $db->prepare("SELECT id, make, model, watchYear, movement, image_url, DATE_FORMAT(date_created, '%M %d, %Y, %h:%i %p') AS formatted_date 
                               FROM watchPost 
                               WHERE make LIKE ? 
                               ORDER BY $sort_by $sort_order");
    $statement->execute([$filter_letter . '%']);
}

// Process moderation actions for comments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id']) && isset($_POST['moderation_action'])) {
    $comment_id = $_POST['comment_id'];
    $moderation_action = $_POST['moderation_action'];

    switch ($moderation_action) {
        case 'delete':
            // Delete comment from the database
            $delete_query = "DELETE FROM reviews WHERE reviewid = :reviewid";
            $delete_statement = $db->prepare($delete_query);
            $delete_statement->bindParam(':reviewid', $comment_id);
            $delete_statement->execute();
            break;
        case 'hide':
            // Update status to 'hidden' for the comment
            $update_query = "UPDATE reviews SET status = 'hidden' WHERE reviewid = :reviewid";
            $update_statement = $db->prepare($update_query);
            $update_statement->bindParam(':reviewid', $comment_id);
            $update_statement->execute();
            break;
        case 'unhide':
            // Update status to 'visible' for the comment
            $unhide_query = "UPDATE reviews SET status = '' WHERE reviewid = :reviewid";
            $unhide_statement = $db->prepare($unhide_query);
            $unhide_statement->bindParam(':reviewid', $comment_id);
            $unhide_statement->execute();
            break;
        default:
            // Handle invalid moderation action
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="auth.css">
    <title>Welcome to my Wrist rotation CMS</title>
</head>
<body>
<div class="home_blog">
    <a href="userpage.php">Go to main page</a>
    <a id="blog_link" href="process.php">New Post</a>
    <a href="category.php">categories</a>
    <a href="edituser.php">Edit user</a>
</div>
<h1>Welcome authorized user.</h1>
<h2>Recently Posted Watch Entries</h2>

<!-- Display alphabet links -->
<div><?php echo generateAlphabetLinks(); ?></div>

<form action="" method="GET">
    <label for="sort">Sort by:</label>
    <select name="sort" id="sort">
        <option value="watchYear" <?php echo $sort_by == 'watchYear' ? 'selected' : ''; ?>>Year</option>
        <option value="make" <?php echo $sort_by == 'make' ? 'selected' : ''; ?>>Make</option>
        <option value="date_created" <?php echo $sort_by == 'date_created' ? 'selected' : ''; ?>>Date Posted</option>
    </select>
    <select name="order">
        <option value="asc" <?php echo $sort_order == 'asc' ? 'selected' : ''; ?>>Ascending</option>
        <option value="desc" <?php echo $sort_order == 'desc' ? 'selected' : ''; ?>>Descending</option>
    </select>
    <button type="submit">Sort</button>
</form>

<?php while ($post = $statement->fetch(PDO::FETCH_ASSOC)): ?>
    <div>
        <h2><?= $post['make'] ?> <?= $post['model'] ?></h2>
        <p><strong>Year:</strong> <?= $post['watchYear'] ?></p>
        <p><strong>Movement:</strong> <?= $post['movement'] ?></p>
        <p><strong>Date:</strong> <?= $post['formatted_date'] ?></p>
        <img src="<?= $post['image_url'] ?>" alt="Watch Image">
        <p><a href="edit.php?id=<?= $post['id'] ?>">Edit</a></p>
    </div>
    <!-- Comment Section -->
    <div class="comment-section">
        <h3>Comments</h3>
        <?php
        // Query to fetch comments for the current watch post
        $comments_query = "SELECT * FROM reviews WHERE id = :id ORDER BY date_posted DESC"; // Order comments by date_created in descending order
        $comments_stmt = $db->prepare($comments_query);
        $comments_stmt->bindParam(':id', $post['id']);
        $comments_stmt->execute();

        while ($comment = $comments_stmt->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <div class="comment">
                <div class="content">
                    <p>Name: <?= $comment['name'] ?></p>
                    <p>Comment: <?= $comment['content'] ?></p>
                </div>
                <div class="moderation">
                    <form action="" method="POST">
                        <input type="hidden" name="comment_id" value="<?php echo $comment['reviewid']; ?>">
                        <select name="moderation_action">
                            <option value="delete">Delete</option>
                            <option value="hide">Hide</option>
                            <option value="unhide">Unhide</option> <!-- Added option for unhide -->
                        </select>
                        <button type="submit">Moderate</button>
                    </form>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
<?php endwhile; ?>
</body>
</html>
