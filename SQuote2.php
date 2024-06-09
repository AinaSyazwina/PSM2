<?php
include 'navigaStu.php';
include 'config.php';

session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$query = $conn->prepare("SELECT * FROM register WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$query->close();

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'popular';
$orderBy = $sort == 'new' ? 'date_added DESC' : 'likes DESC';

// Pagination logic
$quotesPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $quotesPerPage;

// Get total number of quotes
$totalQuotesQuery = $conn->query("SELECT COUNT(*) AS total FROM quotes");
$totalQuotesRow = $totalQuotesQuery->fetch_assoc();
$totalQuotes = $totalQuotesRow['total'];
$totalPages = ceil($totalQuotes / $quotesPerPage);

$sql = "SELECT quotes.*, register.fullname, register.picture FROM quotes JOIN register ON quotes.memberID = register.memberID ORDER BY $orderBy LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $quotesPerPage);
$stmt->execute();
$quotesResult = $stmt->get_result();

if (!isset($_SESSION['likedQuotes'])) {
    $_SESSION['likedQuotes'] = [];
    $likesQuery = $conn->prepare("SELECT quoteID FROM quote_likes WHERE memberID = ?");
    $likesQuery->bind_param("s", $username);
    $likesQuery->execute();
    $likesResult = $likesQuery->get_result();
    while ($row = $likesResult->fetch_assoc()) {
        $_SESSION['likedQuotes'][] = $row['quoteID'];
    }
    $likesQuery->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quoteID'])) {
    $quoteID = $_POST['quoteID'];
    $action = $_POST['action'];

    if ($action === 'like' && !in_array($quoteID, $_SESSION['likedQuotes'])) {
        $updateLikes = $conn->prepare("UPDATE quotes SET likes = likes + 1 WHERE quoteID = ?");
        $addLike = $conn->prepare("INSERT INTO quote_likes (memberID, quoteID) VALUES (?, ?)");
        $addLike->bind_param("si", $username, $quoteID);
        $addLike->execute();
        $addLike->close();
        $_SESSION['likedQuotes'][] = $quoteID;
    } elseif ($action === 'unlike' && in_array($quoteID, $_SESSION['likedQuotes'])) {
        $updateLikes = $conn->prepare("UPDATE quotes SET likes = likes - 1 WHERE quoteID = ?");
        $removeLike = $conn->prepare("DELETE FROM quote_likes WHERE memberID = ? AND quoteID = ?");
        $removeLike->bind_param("si", $username, $quoteID);
        $removeLike->execute();
        $removeLike->close();
        $_SESSION['likedQuotes'] = array_diff($_SESSION['likedQuotes'], [$quoteID]);
    }
    $updateLikes->bind_param("i", $quoteID);
    $updateLikes->execute();
    $updateLikes->close();

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Quotes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
    font-size: 2.5em;
}
        p.description {
            text-align: center;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 20px;
        }
        .twitter-icon {
            text-align: center;
            margin-bottom: 10px;
            color: #1da1f2;
        }
        .twitter-icon i {
            font-size: 3em;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .main-nav {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }
        .main-nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .main-nav a.active {
            color: #007bff;
        }
        .sub-nav {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
        }
        .sub-nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .sub-nav a.active {
            color: #007bff;
        }
        .quotes {
            margin: 20px auto;
        }
        .quote {
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 10px;
            display: flex;
            align-items: flex-start;
        }
        .quote img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin-right: 15px;
        }
        .quote-details {
            flex-grow: 1;
        }
        .quote-details p {
            margin: 3px 0;
        }
        .quote-details .quote-text {
            font-style: italic;
        }
        .quote-details .quote-author {
            font-weight: bold;
        }
        .quote-details .quote-tags {
            color: green;
            font-size: 0.85em;
        }
        .quote-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .quote-actions .like-button {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
        }
        .quote-actions .like-button.liked {
            color: #e25555;
        }
        .quote-actions .like-button.unliked {
            color: #ccc;
        }
        .quote-actions .likes {
            margin: 5px 0 0;
            color: #333;
            font-size: 0.85em;
        }
        .pagination {
            text-align: right;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 2px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .pagination a.active {
            background-color: #2e2185;
            color: #fff;
            border-color: #2e2185;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="twitter-icon">
            <i class="fab fa-twitter"></i>
        </div>
        <h1>Community Quotes</h1>
        <p class="description">Join our vibrant community by sharing your 
            favorite quotes and insights. <BR> Inspire others and get inspired by the collective wisdom of our members.</p>

        <div class="main-nav">
            <a href="SQuote.php">Create a Quote</a>
            <a href="SQuote3.php">Your Quotes</a>
            <a href="SQuote2.php" class="active">View All Quotes</a>
        </div>
        <div class="sub-nav">
            <a href="SQuote2.php?sort=popular" class="<?= $sort == 'popular' ? 'active' : '' ?>">Popular</a>
            <a href="SQuote2.php?sort=new" class="<?= $sort == 'new' ? 'active' : '' ?>">New</a>
        </div>
        <div class="quotes">
            <?php while ($row = $quotesResult->fetch_assoc()) : ?>
                <div class="quote">
                    <img src="<?= htmlspecialchars($row['picture']) ?>" alt="Profile Picture">
                    <div class="quote-details">
                        <p class="quote-text">"<?php echo htmlspecialchars($row['quote']); ?>"</p>
                        <p class="quote-author">- <?php echo htmlspecialchars($row['fullname']); ?></p>
                        <p class="quote-tags">Tags: <?php echo htmlspecialchars($row['tags']); ?></p>
                    </div>
                    <div class="quote-actions">
                        <form method="post" action="">
                            <input type="hidden" name="quoteID" value="<?= $row['quoteID'] ?>">
                            <button class="like-button <?= in_array($row['quoteID'], $_SESSION['likedQuotes']) ? 'liked' : 'unliked' ?>" name="action" value="<?= in_array($row['quoteID'], $_SESSION['likedQuotes']) ? 'unlike' : 'like' ?>">
                                ❤
                            </button>
                        </form>
                        <p class="likes"><?php echo $row['likes']; ?> likes</p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <a href="SQuote2.php?page=<?= $i ?>&sort=<?= $sort ?>" class="<?= $page == $i ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
