<?php
include 'config.php';

// Determine the current page number from the URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Set the number of records per page

// Calculate the total number of pages needed
$countQuery = "SELECT COUNT(*) AS total FROM books";
$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalPages = ceil($countRow['total'] / $perPage);

// Calculate the offset for SQL query
$offset = ($page - 1) * $perPage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="Cssfile/bookAvail.css">
</head>
<body>
<?php include 'navigaLib.php'; ?>
    <div class="container-padding">
    <div style="margin-top: 20px;">
    <h1 style="text-align:center; color:black;">Book Status Availability</h1>
    <h1 style="text-align:center; color:black;">Book Status Availability</h1>
    <div class="AvailBox">
        <div class="search-container">
            <input type="text" id="searchBox" placeholder="Search...">
        </div>
        <table class="table2">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>ISBN</th>
                    <th>Total Books</th>
                    <th>Total Available</th>
                    <th>Total Issues</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php include 'bookAvail2.php'; ?>
            </tbody>
        </table>
        <div class="pagination-container">
            <ul>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="<?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    </div>
</body>
</html>


