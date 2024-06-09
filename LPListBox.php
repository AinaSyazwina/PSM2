<?php
include 'navigaLib.php';  
include 'config.php'; 

$sort = $_GET['sort'] ?? 'all';
$search = $_GET['search'] ?? '';

$sortConditions = [
    'all' => '1=1',
    'BookPanda' => "category LIKE '%BookPanda%'",
    'GrabBook' => "category LIKE '%GrabBook%'",
    'latest' => 'DateCreate DESC',
    'oldest' => 'DateCreate ASC'
];
$query = "SELECT BoxSerialNum, category, Boxpicture, DateCreate FROM boxs WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (BoxSerialNum LIKE '%$search%' OR category LIKE '%$search%')";
}

if ($sort !== 'all') {
    if (in_array($sort, ['latest', 'oldest'])) {
        $query .= " ORDER BY " . $sortConditions[$sort];
    } else {
        $query .= " AND " . $sortConditions[$sort];
    }
} else {
    $query .= " ORDER BY BoxSerialNum";
}

$boxResult = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Cssfile/navStu.css">
    <title>All Boxes</title>
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
            height: 280px;
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
            color: inherit; 
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
            <h2>All Boxes</h2>
            <div class="search-bar">
                <input type="text" id="search" name="search" placeholder="Search here..." value="<?php echo htmlspecialchars($search); ?>">
                <box-icon name='filter' id="filter-icon"></box-icon>
                <div class="filter-dropdown" id="filter-dropdown">
                    <a href="?sort=all&search=<?php echo $search; ?>">All</a>
                    <a href="?sort=BookPanda&search=<?php echo $search; ?>">BookPanda</a>
                    <a href="?sort=GrabBook&search=<?php echo $search; ?>">GrabBook</a>
                    <a href="?sort=latest&search=<?php echo $search; ?>">Latest</a>
                    <a href="?sort=oldest&search=<?php echo $search; ?>">Oldest</a>
                </div>
            </div>
            <div class="books-display">
                <?php 
                $uploadDir = ''; // Set this to the directory where images are stored
                if ($boxResult && $boxResult->num_rows > 0):
                    while($row = $boxResult->fetch_assoc()): ?>
                        <div class='book-item'>
                            <a href="LPListBox1.php?BoxSerialNum=<?php echo $row['BoxSerialNum']; ?>" class="no-underline">
                                <img src="<?php echo $uploadDir . $row['Boxpicture']; ?>" alt='Box Picture' class='book-cover'>
                                <p class='book-title1'><strong><?php echo htmlspecialchars($row['BoxSerialNum']); ?></strong></p>
                            </a>
                            <p class='book-author1'>Category: <?php echo htmlspecialchars($row['category']); ?></p>
                        </div>
                    <?php endwhile; 
                else: ?>
                    <p>No boxes available.</p>
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
