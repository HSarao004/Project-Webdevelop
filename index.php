<?php
// Include database connection file
require('connect.php');

// Function to generate year range links
function generateYearRangeLinks()
{
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

// Fetch all distinct watch categories from the database
$category_query = "SELECT DISTINCT category FROM watchPost";
$category_statement = $db->query($category_query);
$categories = $category_statement->fetchAll(PDO::FETCH_ASSOC);

// Check if a year filter is applied
$filter_year = isset($_GET['year']) ? $_GET['year'] : null;

// Check if a category filter is applied
$filter_category = isset($_GET['category']) ? $_GET['category'] : null;

// Check if an excluded category filter is applied
$exclude_category = isset($_GET['exclude_category']) ? $_GET['exclude_category'] : null;

// Check if a search query is submitted
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Current page number
$results_per_page = 3; // Number of results per page

// Calculate the offset for pagination
$offset = ($page - 1) * $results_per_page;

// Initialize $posts array
$posts = [];

// Query to fetch total number of watch posts
$total_query = "SELECT COUNT(*) AS total FROM watchPost";

// Add filters to the query if they exist
if ($filter_year || $filter_category || $exclude_category || $search_query) {
    $total_query .= " WHERE";
    $conditions = [];
    if ($filter_year) {
        $year_range = explode('-', $filter_year);
        if (count($year_range) === 2) {
            $start_year = intval($year_range[0]);
            $end_year = intval($year_range[1]);
            $conditions[] = " watchYear >= $start_year AND watchYear <= $end_year";
        }
    }
    if ($filter_category) {
        $conditions[] = " category = '$filter_category'";
    }
    if ($exclude_category) {
        $conditions[] = " category != '$exclude_category'";
    }
    if ($search_query) {
        $conditions[] = " (make LIKE '%$search_query%' OR model LIKE '%$search_query%' OR category LIKE '%$search_query%')";
    }
    $total_query .= implode(" AND ", $conditions);
}

// Execute the total query and fetch the total number of results
$total_statement = $db->query($total_query);
$total_result = $total_statement->fetch(PDO::FETCH_ASSOC);
$total_posts = intval($total_result['total']);

// Calculate total number of pages
$total_pages = ceil($total_posts / $results_per_page);

// Query to fetch watch posts with pagination
$query = "SELECT id, make, model, watchYear, movement, image_url, DATE_FORMAT(date_created, '%M %d, %Y, %h:%i %p') AS formatted_date, category 
          FROM watchPost";

// Add filters to the query if they exist
if ($filter_year || $filter_category || $exclude_category || $search_query) {
    $query .= " WHERE";
    $conditions = [];
    if ($filter_year) {
        $year_range = explode('-', $filter_year);
        if (count($year_range) === 2) {
            $start_year = intval($year_range[0]);
            $end_year = intval($year_range[1]);
            $conditions[] = " watchYear >= $start_year AND watchYear <= $end_year";
        }
    }
    if ($filter_category) {
        $conditions[] = " category = '$filter_category'";
    }
    if ($exclude_category) {
        $conditions[] = " category != '$exclude_category'";
    }
    if ($search_query) {
        $conditions[] = " (make LIKE '%$search_query%' OR model LIKE '%$search_query%' OR category LIKE '%$search_query%')";
    }
    $query .= implode(" AND ", $conditions);
}

// Add pagination limit and offset to the query
$query .= " ORDER BY date_created DESC LIMIT $results_per_page OFFSET $offset";

// Execute the query
$statement = $db->query($query);
$posts = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Welcome to my Wrist rotation CMS</title>
</head>

<body>
    <div class="nav-bar">

        <a id="blog_link" href="process.php">New Post</a>
        <a id="blog_link" href="adindex.php">Admin Dashboard</a>
    </div>
    <h1>Welcome authorized user.</h1>

    <!-- Search form -->
    <form method="get" action="">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search" value="<?= htmlentities($search_query) ?>" placeholder="Enter keywords">
        <button type="submit">Submit</button>
        <!-- Include category filter in the search form -->
        <input type="hidden" name="category" value="<?= htmlentities($filter_category) ?>">
        <input type="hidden" name="exclude_category" value="<?= htmlentities($exclude_category) ?>">
        <input type="hidden" name="year" value="<?= htmlentities($filter_year) ?>">
    </form>

    <!-- Category dropdown -->
    <form method="get" action="">
        <label for="category">Select Watch Category:</label>
        <select name="category" id="category">
            <option value="">All</option>
            <?php foreach ($categories as $category) : ?>
                <option value="<?= $category['category'] ?>" <?= ($filter_category === $category['category']) ? 'selected' : '' ?>>
                    <?= $category['category'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <!-- Include search query in the category filter form -->
        <input type="hidden" name="search" value="<?= htmlentities($search_query) ?>">
        <input type="hidden" name="exclude_category" value="<?= htmlentities($exclude_category) ?>">
        <input type="hidden" name="year" value="<?= htmlentities($filter_year) ?>">
        <button type="submit">Filter</button>
    </form>

    <!-- Exclude category dropdown -->
    <form method="get" action="">
        <label for="exclude_category">Exclude Category:</label>
        <select name="exclude_category" id="exclude_category">
            <option value="">None</option>
            <?php foreach ($categories as $category) : ?>
                <option value="<?= $category['category'] ?>" <?= ($exclude_category === $category['category']) ? 'selected' : '' ?>>
                    <?= $category['category'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <!-- Include search query and category filter in the exclude category form -->
        <input type="hidden" name="search" value="<?= htmlentities($search_query) ?>">
        <input type="hidden" name="category" value="<?= htmlentities($filter_category) ?>">
        <input type="hidden" name="year" value="<?= htmlentities($filter_year) ?>">
        <button type="submit">Exclude</button>
    </form>

    <!-- Display year range links -->
    <div><?php echo generateYearRangeLinks(); ?></div>

    <?php if (!empty($posts)) : ?>
        <?php foreach ($posts as $post) : ?>
            <div class="watch_post">
                <h2><?= $post['make'] ?> <?= $post['model'] ?></h2>
                <p><strong>Year:</strong> <?= $post['watchYear'] ?></p>
                <p><strong>Movement:</strong> <?= $post['movement'] ?></p>
                <p><strong>Category:</strong> <?= $post['category'] ?></p>
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
            </div>
        <?php endforeach; ?>
        <!-- Pagination links -->
        <div class="pagination">
            <?php if ($page > 1) : ?>
                <a href="?page=<?= $page - 1 ?>&category=<?= $filter_category ?>&exclude_category=<?= $exclude_category ?>&search=<?= $search_query ?>">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <a href="?page=<?= $i ?>&category=<?= $filter_category ?>&exclude_category=<?= $exclude_category ?>&search=<?= $search_query ?>" <?= ($page === $i) ? 'class="active"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages) : ?>
                <a href="?page=<?= $page + 1 ?>&category=<?= $filter_category ?>&exclude_category=<?= $exclude_category ?>&search=<?= $search_query ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <p>No watch posts found.</p>
    <?php endif; ?>
</body>

</html>
