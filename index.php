
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
$statement = $db->query("SELECT id, make, model, watchYear, movement, image_url, DATE_FORMAT(date_created, '%M %d, %Y, %h:%i %p') AS formatted_date 
                         FROM watchPost 
                         ORDER BY date_created DESC");
$posts = $statement->fetchAll(PDO::FETCH_ASSOC);

// Handle filtering if a year link is clicked
if ($filter_year) {
    // Extract start and end years from the filter
    $year_range = explode('-', $filter_year);
    if (count($year_range) === 2) {
        $start_year = intval($year_range[0]);
        $end_year = intval($year_range[1]);

        // Query to fetch watch posts within the specified year range
        $statement = $db->prepare("SELECT id, make, model, watchYear, movement, image_url, DATE_FORMAT(date_created, '%M %d, %Y, %h:%i %p') AS formatted_date 
                                   FROM watchPost 
                                   WHERE watchYear >= ? AND watchYear <= ?
                                   ORDER BY date_created DESC");
        $statement->execute([$start_year, $end_year]);

        // Fetch posts
        $posts = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Initialize variables for search functionality
$search_results = [];
$search_query = '';

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_query = trim($_POST['search_query']);

    // Query to search for watch posts based on the search query
    $statement = $db->prepare("SELECT id, make, model, watchYear, movement, image_url, DATE_FORMAT(date_created, '%M %d, %Y, %h:%i %p') AS formatted_date 
                               FROM watchPost 
                               WHERE make LIKE ? OR model LIKE ?
                               ORDER BY date_created DESC");
    $statement->execute(["%$search_query%", "%$search_query%"]);

    // Fetch search results
    $search_results = $statement->fetchAll(PDO::FETCH_ASSOC);
}
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
    <label for="search_query">Search your Watch here </label>
    <input type="text" id="search_query" name="search_query" value="<?= htmlentities($search_query) ?>">
    <button type="submit" name="search">Search</button>
</form>

<!-- Display year range links -->
<div><?php echo generateYearRangeLinks(); ?></div>

<?php if (!empty($search_results)) : ?>
    <h2>Search Results</h2>
    <?php foreach ($search_results as $post) : ?>
        <div>
            <h2><?= $post['make'] ?> <?= $post['model'] ?></h2>
            <p><strong>Year:</strong> <?= $post['watchYear'] ?></p>
            <p><strong>Movement:</strong> <?= $post['movement'] ?></p>
            <p><strong>Date:</strong> <?= $post['formatted_date'] ?></p>
            <?php if (!empty($post['image_url'])) : ?>
                <!-- Display image if image URL is not empty -->
                <img src="<?= $post['image_url'] ?>" alt="Watch Image">
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <?php if (!empty($posts)) : ?>
        <h2>Watch Posts</h2>
        <?php foreach ($posts as $post) : ?>
            <div>
                <h2><?= $post['make'] ?> <?= $post['model'] ?></h2>
                <p><strong>Year:</strong> <?= $post['watchYear'] ?></p>
                <p><strong>Movement:</strong> <?= $post['movement'] ?></p>
                <p><strong>Date:</strong> <?= $post['formatted_date'] ?></p>
                <?php if (!empty($post['image_url'])) : ?>
                    <!-- Display image if image URL is not empty -->
                    <img src="<?= $post['image_url'] ?>" alt="Watch Image">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No watch posts found.</p>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
