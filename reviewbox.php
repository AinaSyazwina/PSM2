<?php
include 'navigation.php';
include 'config.php';

// Get page number from query string, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 4; // Number of reviews per page

// Determine sorting and filtering criteria
$sort = $_GET['sort'] ?? 'all';
$search = $_GET['search'] ?? '';

// Reviews for current page
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

$query = "
SELECT 
    r.fullName,
    r.role,
    rv.memberID,
    rv.issueBoxID,
    b.category,
    b.BoxSerialNum,
    b.Boxpicture,
    rv.rating,
    rv.DateReview,
    rv.DateReviewEdit,
    rv.review
FROM 
    reviewbox rv
INNER JOIN 
    register r ON rv.memberID = r.memberID
INNER JOIN 
    issuebox ib ON rv.issueBoxID = ib.issueBoxID
INNER JOIN 
    boxs b ON ib.BoxSerialNum = b.BoxSerialNum
WHERE 
    rv.isReview = 1
";

$countQuery = "
SELECT 
    COUNT(*) as total
FROM 
    reviewbox rv
INNER JOIN 
    register r ON rv.memberID = r.memberID
INNER JOIN 
    issuebox ib ON rv.issueBoxID = ib.issueBoxID
INNER JOIN 
    boxs b ON ib.BoxSerialNum = b.BoxSerialNum
WHERE 
    rv.isReview = 1
";

$conditions = [];
if (!empty($search)) {
    $conditions[] = "(r.fullName LIKE '%" . $conn->real_escape_string($search) . "%' OR b.BoxSerialNum LIKE '%" . $conn->real_escape_string($search) . "%')";
}

if ($sort !== 'all') {
    $rating = (int)filter_var($sort, FILTER_SANITIZE_NUMBER_INT);
    if ($rating >= 1 && $rating <= 5) {
        $conditions[] = "rv.rating = $rating";
    }
}

if (!empty($conditions)) {
    $conditionStr = implode(' AND ', $conditions);
    $query .= " AND " . $conditionStr;
    $countQuery .= " AND " . $conditionStr;
}

$query .= " ORDER BY rv.DateReview DESC LIMIT $start, $perPage";

$result = $conn->query($query);

$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalReviews = $countRow['total'];

// Average Rating
$avgQuery = "SELECT AVG(rating) as avgRating FROM reviewbox WHERE isReview = 1";
$avgResult = $conn->query($avgQuery);
$avgRow = $avgResult->fetch_assoc();
$averageRating = round($avgRow['avgRating'], 2);

// Top Reviewers
$topReviewersQuery = "SELECT memberID, COUNT(*) as count FROM reviewbox GROUP BY memberID ORDER BY count DESC LIMIT 1";
$topReviewersResult = $conn->query($topReviewersQuery);
$topReviewer = $topReviewersResult->fetch_assoc();

// Count of issues not reviewed yet
$notReviewedQuery = "
SELECT COUNT(*) AS notReviewedCount
FROM issuebox ib
LEFT JOIN reviewbox rv ON ib.issueBoxID = rv.issueBoxID AND rv.isReview = 1
WHERE rv.reviewBoxID IS NULL;
";
$notReviewedResult = $conn->query($notReviewedQuery);
$notReviewedRow = $notReviewedResult->fetch_assoc();
$notReviewedCount = $notReviewedRow['notReviewedCount'];
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const sortLinks = document.querySelectorAll('.sort-content a');
        const reviewsContainer = document.getElementById('reviews-container');

        searchInput.addEventListener('input', function() {
            filterReviews();
        });

        sortLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const sortType = this.getAttribute('data-sort');
                updateUrlParam('sort', sortType);
            });
        });

        function filterReviews() {
            const searchText = searchInput.value.toLowerCase();
            const reviews = reviewsContainer.querySelectorAll('.review');
            reviews.forEach(review => {
                const textContent = review.textContent.toLowerCase();
                review.style.display = textContent.includes(searchText) ? '' : 'none';
            });
        }

        function updateUrlParam(key, value) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set(key, value);
            urlParams.set('page', 1); // Reset to first page on new sort
            window.location.search = urlParams.toString();
        }
    });
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Box Reviews</title>
    <link rel="stylesheet" href="Cssfile/review.css">
</head>
<style>
    .pagination {
        display: flex;
        justify-content: flex-end; /* Aligns the pagination to the right */
        margin-top: 20px;
    }
    .pagination a {
        height: 30px;
        padding: 4px 8px;    
        margin: 2px;         
        border: 1px solid #ddd; 
        border-radius: 5px; 
        text-decoration: none;
        color: #666;         
        font-size: 0.85rem; 
    }

    .pagination a.active {
        background-color: var(--blue2); 
        color: white;
        border-color: #4c44b6; 
    }
</style>
<body>
<div class="cardBox">
    <div class="card">
        <div>
            <div class="numbers"><?php echo $totalReviews; ?></div>
            <div class="cardName">Total Reviews</div>
        </div>
        <div class="iconBox">
            <ion-icon name="people-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $averageRating; ?></div>
            <div class="cardName">Average Rating</div>
        </div>
        <div class="iconBox">
            <ion-icon name="star-half-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $topReviewer['count']; ?></div>
            <div class="cardName">Top Reviewer: <?php echo htmlspecialchars($topReviewer['memberID']); ?></div>
        </div>
        <div class="iconBox">
            <ion-icon name="star-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $notReviewedCount; ?></div>
            <div class="cardName">Pending Reviews</div>
        </div>
        <div class="iconBox">
            <ion-icon name="calendar-outline"></ion-icon>
        </div>
    </div>
</div>

<div class="details">
    <div class="BookList">
        <h2>List of Box Reviews</h2>
        <div class="search3">
            <label>
                <input type="text" placeholder="Click here" id="searchInput" value="<?= htmlspecialchars($search) ?>">
            </label>
            <div class="sort">
                <button class="sortbtn">
                    <ion-icon name="filter-outline"></ion-icon> Filter
                </button>
                <div class="sort-content">
                    <a href="?sort=all&search=<?= htmlspecialchars($search) ?>" data-sort="all">All</a>
                    <a href="?sort=5 stars&search=<?= htmlspecialchars($search) ?>" data-sort="5 stars">5 stars</a>
                    <a href="?sort=4 stars&search=<?= htmlspecialchars($search) ?>" data-sort="4 stars">4 stars</a>
                    <a href="?sort=3 stars&search=<?= htmlspecialchars($search) ?>" data-sort="3 stars">3 stars</a>
                    <a href="?sort=2 stars&search=<?= htmlspecialchars($search) ?>" data-sort="2 stars">2 stars</a>
                    <a href="?sort=1 star&search=<?= htmlspecialchars($search) ?>" data-sort="1 star">1 star</a>
                </div>
            </div>
        </div>
        
        <div id="reviews-container">
            <?php if ($result->num_rows > 0): 
                $uploadDir = ''; ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="review">
                        <div class="left-side">
                            <img src="<?php echo htmlspecialchars($uploadDir . $row['Boxpicture']); ?>" alt="<?php echo htmlspecialchars($row['BoxSerialNum']); ?>" class="avatar">
                            <h3><?php echo htmlspecialchars($row['fullName']); ?></h3>
                            <p><?php echo htmlspecialchars($row['role']); ?></p>
                        </div>
                        <div class="right-side">
                            <div class="star-rating">
                                <?php echo str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']); ?>
                            </div>
                            <h3 style="color: var(--blue2);"><?php echo htmlspecialchars($row['BoxSerialNum']); ?></h3>
                            <p><strong>Date of Review:</strong> <?php echo htmlspecialchars($row['DateReview']); ?></p>
                            <p><strong>Date of Review Edit:</strong> <?php echo htmlspecialchars($row['DateReviewEdit']); ?></p>
                            <p><strong>Review:</strong> <?php echo htmlspecialchars($row['review']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews found.</p>
            <?php endif; ?>
        </div>
        
        <div class="pagination">
            <?php for ($i = 1; $i <= ceil($totalReviews / $perPage); $i++): ?>
                <a href="?page=<?php echo $i; ?>&sort=<?= htmlspecialchars($sort) ?>&search=<?= htmlspecialchars($search) ?>" class="<?= ($page === $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>
</body>
</html>
