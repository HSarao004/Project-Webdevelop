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
    <a href="index.php">Go to main page</a>
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
<?php endwhile; ?>
</body>
</html>
