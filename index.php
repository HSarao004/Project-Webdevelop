<?php
// Include database connection file
require('connect.php');

// Function to generate year range links
function generateYearRangeLinks() {
    $year_ranges = array(
        "Prior to 1950" => "0-1950",
        "1950-1970" => "1950-1970",
        "1971-2000" => "1971-2000",
        "2001-2024" => "2001-2024"
    );

    $links = '';

    foreach ($year_ranges as $label => $range) {
        $links .= '<a href="?year=' . $range . '">' . $label . '</a> ';
    }

    return $links;
}

// Check if a year filter is applied
$filter_year = isset($_GET['year']) ? $_GET['year'] : null;

// Initialize $posts array
$posts = [];

// Query to fetch all watch posts
$query = "SELECT id, make, model, watchYear, movement, image_url, DATE_FORMAT(date_created, '%M %d, %Y, %h:%i %p') AS formatted_date 
          FROM watchPost";

// Handle filtering if a year link is clicked
if ($filter_year) {
    // Extract start and end years from the filter
    $year_range = explode('-', $filter_year);
    if (count($year_range) === 2) {
        $start_year = intval($year_range[0]);
        $end_year = intval($year_range[1]);
        $query .= " WHERE watchYear >= $start_year AND watchYear <= $end_year";
    }
}

// Execute the query
$statement = $db->query($query . " ORDER BY date_created DESC");
$posts = $statement->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables for search functionality
$search_results = [];
$search_query = '';

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_query = trim($_POST['search_query']);
    // Query to search for watch posts based on the search query
    $search_statement = $db->prepare("SELECT id, make, model, watchYear, movement, image_url, DATE_FORMAT(date_created, '%M %d, %Y, %h:%i %p') AS formatted_date 
                                      FROM watchPost 
                                      WHERE make LIKE ? OR model LIKE ?
                                      ORDER BY date_created DESC");
    $search_statement->execute(["%$search_query%", "%$search_query%"]);
    // Fetch search results
    $search_results = $search_statement->fetchAll(PDO::FETCH_ASSOC);
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $post_id = $_POST['post_id'];
    $name = $_POST['name'];
    $comment = $_POST['comment'];

    // Insert comment into the database
    $comment_statement = $db->prepare("INSERT INTO reviews (id, name, content) VALUES (?, ?, ?)");
    $comment_statement->execute([$post_id, $name, $comment]);
}

// Query to fetch comments
$comments_statement = $db->query("SELECT * FROM reviews ORDER BY date_posted DESC");
$comments = $comments_statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Welcome to my Wrist rotation CMS</title>
</head>
<body>
<div class="home_blog">
    <a href="index.php">Go to main page</a>
    <a id="blog_link" href="process.php">New Post</a>
    <a id="blog_link" href="adindex.php">Admin Dashboard</a>
</div>
<h1>Welcome authorized user.</h1>

<!-- Search form -->
<form method="post" action="">
    <label for="search_query">Search your Watch here:</label>
    <input type="text" id="search_query" name="search_query" value="<?= htmlentities($search_query) ?>">
    <button type="submit" name="search">Search</button>
</form>

<!-- Display year range links -->
<div><?php echo generateYearRangeLinks(); ?></div>

<?php if (!empty($search_results)) : ?>
    <h2>Search Results</h2>
    <?php foreach ($search_results as $result) : ?>
        <div class="watch_post">
            <h2><?= $result['make'] ?> <?= $result['model'] ?></h2>
            <p><strong>Year:</strong> <?= $result['watchYear'] ?></p>
            <p><strong>Movement:</strong> <?= $result['movement'] ?></p>
            <p><strong>Date:</strong> <?= $result['formatted_date'] ?></p>
            <?php if (!empty($result['image_url'])) : ?>
                <img src="<?= $result['image_url'] ?>" alt="Watch Image">
            <?php endif; ?>

            <!-- Comment form for each watch post -->
            <form method="post" action="">
                <input type="hidden" name="post_id" value="<?= $result['id'] ?>">
                <label for="name">Your Name:</label>
                <input type="text" id="name" name="name" required><br>
                <label for="comment">Your Comment:</label><br>
                <textarea id="comment" name="comment" rows="4" cols="50" required></textarea><br>
                <button type="submit" name="submit_comment">Submit Comment</button>
            </form>

            <!-- Display comments for this watch post -->
            <?php
            $post_comments = array_filter($comments, function ($comment) use ($result) {
                return $comment['id'] == $result['id'];
            });
            ?>
            <?php if (!empty($post_comments)) : ?>
                <h3>Comments</h3>
                <?php foreach ($post_comments as $comment) : ?>
                    <div class="comment">
                        <p><strong>Name:</strong> <?= $comment['name'] ?></p>
                        <p><?= $comment['content'] ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No comments yet.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php elseif (!empty($posts)) : ?>
    <?php foreach ($posts as $post) : ?>
        <div class="watch_post">
            <h2><?= $post['make'] ?> <?= $post['model'] ?></h2>
            <p><strong>Year:</strong> <?= $post['watchYear'] ?></p>
            <p><strong>Movement:</strong> <?= $post['movement'] ?></p>
            <p><strong>Date:</strong> <?= $post['formatted_date'] ?></p>
            <?php if (!empty($post['image_url'])) : ?>
                <img src="<?= $post['image_url'] ?>" alt="Watch Image">
            <?php endif; ?>

            <!-- Comment form for each watch post -->
            <form method="post" action="">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <label for="name">Your Name:</label>
                <input type="text" id="name" name="name" required><br>
                <label for="comment">Your Comment:</label><br>
                <textarea id="comment" name="comment" rows="4" cols="50" required></textarea><br>
                <button type="submit" name="submit_comment">Submit Comment</button>
            </form>

            <!-- Display comments for this watch post -->
            <?php
            $post_comments = array_filter($comments, function ($comment) use ($post) {
                return $comment['id'] == $post['id'];
            });
            ?>
            <?php if (!empty($post_comments)) : ?>
                <h3>Comments</h3>
                <?php foreach ($post_comments as $comment) : ?>
                    <div class="comment">
                        <p><strong>Name:</strong> <?= $comment['name'] ?></p>
                        <p><?= $comment['content'] ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No comments yet.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <p>No watch posts found.</p>
<?php endif; ?>
</body>
</html>
