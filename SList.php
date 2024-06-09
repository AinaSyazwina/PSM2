<?php
include 'navigaStu.php';  
include 'config.php';  // Ensure database configuration is properly set

// Get sort and search parameters from the request
$sort = $_GET['sort'] ?? 'all';
$search = $_GET['search'] ?? '';

// Mapping sort parameters to SQL conditions
$sortConditions = [
    'all' => '1=1',
    'fiction' => "genre LIKE '%Fiction%'",
    'non-fiction' => "genre LIKE '%Non-Fiction%'",
    'mystery' => "genre LIKE '%Mystery%'",
    'fairytale' => "genre LIKE '%FairyTale%'",
    'action' => "genre LIKE '%Action%'",
    'fantasy' => "genre LIKE '%Fantasy%'",
    'historical' => "genre LIKE '%Historical%'",
    'latest' => 'DateReceived DESC',
    'oldest' => 'DateReceived ASC'
];

// Building the query
$query = "SELECT book_acquisition, Title, author1, author2, picture FROM books WHERE 1=1";

// Adding search filter if search is provided
if (!empty($search)) {
    $query .= " AND (Title LIKE '%$search%' OR author1 LIKE '%$search%' OR author2 LIKE '%$search%' OR genre LIKE '%$search%')";
}

// Adding sort condition
if ($sort !== 'all') {
    if (in_array($sort, ['latest', 'oldest'])) {
        $query .= " ORDER BY " . $sortConditions[$sort];
    } else {
        $query .= " AND " . $sortConditions[$sort];
    }
} else {
    $query .= " ORDER BY Title";
}

$bookResult = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Cssfile/navStu.css">
    <title>All Books</title>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <style>
       
        .container-padding {
          
            margin-top: 150px;
        }
        .search-bar {
    display: flex;
    justify-content: flex-end; 
    margin-right: 30px; 
    margin-bottom: 20px;
    position: relative;
}

        .search-bar input[type="text"] {
            width: 300px;
            padding: 10px 40px 10px 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 30px;
            outline: none;
        }
        .search-bar box-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            cursor: pointer;
        }
        .filter-dropdown {
            display: none;
            position: absolute;
            top: 40px;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        .filter-dropdown.active {
            display: block;
        }
        .filter-dropdown a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: #333;
        }
        .filter-dropdown a:hover {
            background: #f4f4f4;
        }
        .books-display {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .book-item {
            text-align: center;
            transition: transform 0.3s ease;
        }
        .book-item:hover {
            transform: scale(1.05);
        }
        .book-cover {
            width: 200px;
            height: 300px;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: transform 0.3s ease;
        }
        .book-title1, .book-author1 {
            font-size: 16px;
            color: #333;
        }
        .book-title1 {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .no-underline {
            text-decoration: none;
        }
        .no-underline:hover {
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container-padding" style="margin-top: 150px;">
    <div class="book-listings">
        <div style="position: relative;">
            <h2>All Books</h2>
            <div class="search-bar">
            <input type="text" id="search" name="search" placeholder="Search here..." value="<?php echo htmlspecialchars($search); ?>">
        <box-icon name='filter' id="filter-icon"></box-icon>
        <div class="filter-dropdown" id="filter-dropdown">
                    <a href="?sort=all&search=<?php echo $search; ?>">All</a>
                    <a href="?sort=fiction&search=<?php echo $search; ?>">Fiction</a>
                    <a href="?sort=non-fiction&search=<?php echo $search; ?>">Non-Fiction</a>
                    <a href="?sort=mystery&search=<?php echo $search; ?>">Mystery</a>
                    <a href="?sort=fairytale&search=<?php echo $search; ?>">FairyTale</a>
                    <a href="?sort=action&search=<?php echo $search; ?>">Action</a>
                    <a href="?sort=fantasy&search=<?php echo $search; ?>">Fantasy</a>
                    <a href="?sort=historical&search=<?php echo $search; ?>">Historical</a>
                    <a href="?sort=latest&search=<?php echo $search; ?>">Latest</a>
                    <a href="?sort=oldest&search=<?php echo $search; ?>">Oldest</a>
                </div>
            </div>
            <div class="books-display">
                <?php 
                $uploadDir = ''; // Set this to the directory where images are stored
                if ($bookResult && $bookResult->num_rows > 0):
                    while($row = $bookResult->fetch_assoc()): ?>
                        <div class='book-item'>
                            <a href="SList1.php?bookAcquisition=<?php echo $row['book_acquisition']; ?>" class="book-link no-underline">
                                <img src="<?php echo $uploadDir . $row['picture']; ?>" alt='Book Cover' class='book-cover'>
                                <p class='book-title1'><strong><?php echo htmlspecialchars($row['Title']); ?></strong></p>
                            </a>
                            <p class='book-author1'> <?php echo htmlspecialchars($row['author1']); 
                                echo !empty($row['author2']) ? ', ' . htmlspecialchars($row['author2']) : ''; ?></p>
                        </div>
                    <?php endwhile; 
                else: ?>
                    <p>No books available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>    
<script>
   document.getElementById('filter-icon').addEventListener('click', function() {
        document.getElementById('filter-dropdown').classList.toggle('active');
    });

    document.getElementById('search').addEventListener('keyup', debounce(function() {
        let searchValue = this.value;
        let sortValue = "<?php echo $sort; ?>";
        window.location.href = `?search=${searchValue}&sort=${sortValue}`;
    }, 1000)); // 2000 milliseconds = 2 seconds

    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    
</script>
</body>
</html>