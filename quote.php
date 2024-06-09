<?php
include 'navigation.php';
include 'config.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 4;

// Determine sorting and filtering criteria
$sort = $_GET['sort'] ?? 'top_likes';
$search = $_GET['search'] ?? '';

// Quotes for the current page
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

$query = "
SELECT 
    r.fullName,
    r.role,
    q.quoteID,
    q.quote,
    q.tags,
    q.likes,
    q.date_added,
    r.picture
FROM 
    quotes q
INNER JOIN 
    register r ON q.memberID = r.memberID
";

$countQuery = "
SELECT 
    COUNT(*) as total
FROM 
    quotes q
INNER JOIN 
    register r ON q.memberID = r.memberID
";

$conditions = [];
if (!empty($search)) {
    $conditions[] = "(r.fullName LIKE '%" . $conn->real_escape_string($search) . "%' OR q.quote LIKE '%" . $conn->real_escape_string($search) . "%')";
}

if (!empty($conditions)) {
    $conditionStr = implode(' AND ', $conditions);
    $query .= " WHERE " . $conditionStr;
    $countQuery .= " WHERE " . $conditionStr;
}

// Sorting logic
switch ($sort) {
    case 'top_likes':
        $query .= " ORDER BY q.likes DESC";
        break;
    case 'least_likes':
        $query .= " ORDER BY q.likes ASC";
        break;
    case 'oldest':
        $query .= " ORDER BY q.date_added ASC";
        break;
    case 'latest':
        $query .= " ORDER BY q.date_added DESC";
        break;
    case 'all':
        // No additional order by clause for 'all'
        break;
    default:
        $query .= " ORDER BY q.likes DESC";
        break;
}


$query .= " LIMIT $start, $perPage";

$result = $conn->query($query);

$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalQuotes = $countRow['total'];

// Total Quoters
$totalQuotersQuery = $conn->query("SELECT COUNT(DISTINCT memberID) AS totalQuoters FROM quotes");
$totalQuotersRow = $totalQuotersQuery->fetch_assoc();
$totalQuoters = $totalQuotersRow['totalQuoters'];

// Top Quoters
$topQuotersQuery = $conn->query("SELECT memberID, COUNT(*) as count FROM quotes GROUP BY memberID ORDER BY count DESC LIMIT 1");
$topQuotersRow = $topQuotersQuery->fetch_assoc();
$topQuoter = $topQuotersRow['memberID'];

// Top Liked
$topLikedQuery = $conn->query("SELECT quote, MAX(likes) AS maxLikes FROM quotes");
$topLikedRow = $topLikedQuery->fetch_assoc();
$topLikedQuote = $topLikedRow['quote'];

// Custom Information (replace with relevant data)
$customInfoQuery = "
SELECT COUNT(*) AS totalQuotes
FROM quotes
";
$customInfoResult = $conn->query($customInfoQuery);
$customInfoRow = $customInfoResult->fetch_assoc();
$customInfo = $customInfoRow['totalQuotes'];
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const sortLinks = document.querySelectorAll('.sort-content a');
        const quotesContainer = document.getElementById('quotes-container');

        searchInput.addEventListener('input', function() {
            filterQuotes();
        });

        sortLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const sortType = this.getAttribute('data-sort');
                fetchSortedData(sortType);
            });
        });

        function filterQuotes() {
            const searchText = searchInput.value.toLowerCase();
            const quotes = quotesContainer.querySelectorAll('.quote');
            quotes.forEach(quote => {
                const textContent = quote.textContent.toLowerCase();
                quote.style.display = textContent.includes(searchText) ? '' : 'none';
            });
        }

        function fetchSortedData(sortType) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort', sortType);
            window.location.search = urlParams.toString();
        }
    });
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quotes</title>
    <link rel="stylesheet" href="Cssfile/review.css">
    <style>
        .pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        #quote-container {
   
         margin: 20px auto;
         display: flex;
          flex-direction: column;
          gap: 15px;
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
        .quote {
            width: 100%; 
            background-color: var(--white);
            border-radius: 10px;
            display: flex;
            align-items: flex-start;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 15px;
            margin-bottom: 15px; 
            max-width: 1200px;
        }
        .quote .left-side {
            flex: 0 0 auto; 
            padding-right: 15px;
            border-right: 2px solid var(--gray);
            text-align: center; 
        }
        .quote .left-side img {
            width: 80px; 
            height: 100px; 
            object-fit: cover; 
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .quote .right-side {
            flex: 1;
            padding-left: 15px;
        }
        .quote .right-side .quote-text {
            font-size: 1.2em;
            font-weight: bold;
            color: var(--blue2);
        }
        .quote .right-side .quote-date {
            color: black;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .quote .right-side .quote-tags {
            font-size: 0.9em;
            color: black;
        }
        .quote .right-side .quote-likes {
            display: flex;
            align-items: center;
            font-size: 1.2em;
            color: black;
            margin-bottom: 10px;
        }
        .quote .right-side .quote-likes ion-icon {
            margin-right: 5px;
            font-size: 1.5em;
            color: #ff69b4; /* Pink color */
        }
    </style>
</head>
<body>
<div class="cardBox">
    <div class="card">
        <div>
            <div class="numbers"><?php echo $totalQuoters; ?></div>
            <div class="cardName">Total Quoters</div>
        </div>
        <div class="iconBox">
            <ion-icon name="people-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $topQuotersRow['count']; ?></div>
            <div class="cardName">Top Quoters: <?php echo htmlspecialchars($topQuoter); ?></div>
        </div>
        <div class="iconBox">
            <ion-icon name="star-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $topLikedRow['maxLikes']; ?></div>
            <div class="cardName">Top Liked</div>
        </div>
        <div class="iconBox">
            <ion-icon name="heart-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $customInfo; ?></div>
            <div class="cardName">Total Quotes</div>
        </div>
        <div class="iconBox">
            <ion-icon name="quote-outline"></ion-icon>
        </div>
    </div>
</div>

<div class="details">
    <div class="BookList">
        <h2>List of Quotes</h2>
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
                    <a href="?sort=top_likes&search=<?= htmlspecialchars($search) ?>" data-sort="top_likes">Top Likes</a>
                    <a href="?sort=least_likes&search=<?= htmlspecialchars($search) ?>" data-sort="least_likes">Least Likes</a>
                    <a href="?sort=oldest&search=<?= htmlspecialchars($search) ?>" data-sort="oldest">Oldest</a>
                    <a href="?sort=latest&search=<?= htmlspecialchars($search) ?>" data-sort="latest">Latest</a>
                </div>
            </div>
        </div>
        
        <div id="quotes-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="quote">
                        <div class="left-side">
                            <img src="<?php echo htmlspecialchars($row['picture']); ?>" alt="<?php echo htmlspecialchars($row['quote']); ?>" class="avatar">
                            <h3><?php echo htmlspecialchars($row['fullName']); ?></h3>
                            <p><?php echo htmlspecialchars($row['role']); ?></p>
                        </div>
                        <div class="right-side">
                            <div class="quote-likes">
                                <ion-icon name="heart"></ion-icon>
                                <?php echo $row['likes']; ?> likes
                            </div>
                            <div class="quote-text"><?php echo htmlspecialchars($row['quote']); ?></div>
                            <p class="quote-date"><strong>Date:</strong> <?php echo htmlspecialchars(date('Y-m-d', strtotime($row['date_added']))); ?></p>
                            <p class="quote-tags"><strong>Tags:</strong> <?php echo htmlspecialchars($row['tags']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No quotes found.</p>
            <?php endif; ?>
        </div>
        
        <div class="pagination">
            <?php for ($i = 1; $i <= ceil($totalQuotes / $perPage); $i++): ?>
                <a href="?page=<?php echo $i; ?>&sort=<?= htmlspecialchars($sort) ?>&search=<?= htmlspecialchars($search) ?>" class="<?= ($page === $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>
</body>
</html>
