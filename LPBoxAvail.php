<?php include 'navigaLib.php'; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $('#searchBox').keyup(function() {
        var searchTerm = $(this).val();
        fetchBoxAvailability(searchTerm);
    });
});

function fetchBoxAvailability(searchTerm) {
    $.ajax({
        url: 'boxAvail2.php',
        type: 'GET',
        data: {search: searchTerm},
        success: function(data) {
            $('tbody').html(data);
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}
</script>

<?php 
include 'config.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

$countQuery = "SELECT COUNT(*) AS total FROM boxs";
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
    <title>Box Status Availability</title>
    <link rel="stylesheet" href="Cssfile/bookAvail.css">
    <style>
        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .search-container label {
            display: flex;
            align-items: center;
        }
        .search-container input {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .table2 th {
            background-color: #3e3398;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .status-button {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
            box-sizing: border-box;
            min-width: 60px;
        }
        .status-button.green {
            background-color: green;
        }
        .status-button.red {
            background-color: red;
        }
        .status-button.grey {
            background-color: grey;
        }
        .pagination-container ul {
            list-style: none;
            display: flex;
            justify-content: center;
            padding: 0;
        }
        .pagination-container ul li {
            margin: 0 5px;
        }
        .pagination-container ul li.active a {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container-padding">
    <div style="margin-top: 20px;">
        <h1 style="text-align:center; color:black;">Box Status Availability</h1>
        <h1 style="text-align:center; color:black;">Box Status Availability</h1>
        <div class="AvailBox">
            <div class="AvailHeader">
                <div class="search-container">
                    <label>
                        <input type="text" id="searchBox" placeholder="Search here">
                    </label>
                </div>
                <table class="table2">
                    <thead>
                        <tr>
                            <th>Box Serial Number</th>
                            <th>Category</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include 'boxAvail2.php'; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container">
                <ul>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="<?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($searchTerm); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>
