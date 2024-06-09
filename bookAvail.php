<?php include 'navigation.php'; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $('#searchBox').keyup(function() {
        var searchTerm = $(this).val();
        $.ajax({
            url: 'bookAvail2.php',
            type: 'GET',
            data: {search: searchTerm},
            success: function(data) {
                $('tbody').html(data);
            },
            error: function(xhr, status, error) {
                console.error("Error: " + error);
            }
        });
    });
});
</script>

<?php
include 'config.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; 
$countQuery = "SELECT COUNT(*) AS total FROM books";
$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalPages = ceil($countRow['total'] / $perPage);

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
