<?php
include 'navigation.php';
include 'config.php';

// Existing code for fetching box issue records
$boxIssueQuery = "SELECT m.fullname, i.issueBoxID, i.IssueDate, i.DueDate, i.ReturnDate, i.BoxSerialNum
                  FROM issuebox i 
                  JOIN register m ON i.memberID = m.memberID 
                  ORDER BY i.DueDate ASC"; 
$boxIssueResult = mysqli_query($conn, $boxIssueQuery);

// Function to determine the status of the box issue
function getBoxStatus($dueDate, $returnDate) {
    $currentDate = date('Y-m-d');
    if ($returnDate != NULL) {
        if (strtotime($returnDate) > strtotime($dueDate)) {
            return ['late', 'Late Return']; 
        } else {
            return ['return', 'Returned']; 
        }
    } elseif (strtotime($currentDate) <= strtotime($dueDate)) {
        return ['pending', 'In Progress']; 
    } else {
        return ['exceed', 'Exceeded']; 
    }
}

// Pagination for box issues
$boxRecordsPerPage = 10;
$boxPage = isset($_GET['boxPage']) ? (int)$_GET['boxPage'] : 1;
$boxOffset = ($boxPage - 1) * $boxRecordsPerPage;

$boxIssueQuery = "SELECT m.fullname, i.issueBoxID, i.IssueDate, i.DueDate, i.ReturnDate, i.BoxSerialNum
                  FROM issuebox i 
                  JOIN register m ON i.memberID = m.memberID 
                  ORDER BY i.IssueDate DESC 
                  LIMIT $boxRecordsPerPage OFFSET $boxOffset";
$boxIssueResult = mysqli_query($conn, $boxIssueQuery);

$boxCountQuery = "SELECT COUNT(*) AS totalBoxRecords FROM issuebox";
$boxCountResult = mysqli_query($conn, $boxCountQuery);
$boxCountRow = mysqli_fetch_assoc($boxCountResult);
$totalBoxRecords = $boxCountRow['totalBoxRecords'];
$totalBoxPages = ceil($totalBoxRecords / $boxRecordsPerPage);

// Pagination for book issues
$recordsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

$paginationQuery = "SELECT m.fullname, i.bookID, i.IssueDate, i.DueDate, i.ReturnDate, b.ISBN
FROM issuebook i 
JOIN register m ON i.memberID = m.memberID 
JOIN books b ON i.bookID = b.book_acquisition
ORDER BY i.IssueDate DESC 
LIMIT $recordsPerPage OFFSET $offset";
$paginationResult = mysqli_query($conn, $paginationQuery);

$countQuery = "SELECT COUNT(*) AS totalRecords FROM issuebook";
$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = $countRow['totalRecords'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Queries to get the total number of users, books, and boxes
$userCountQuery = "SELECT COUNT(*) AS userCount FROM register";
$userCountResult = mysqli_query($conn, $userCountQuery);
$userCountRow = mysqli_fetch_assoc($userCountResult);
$userCount = ($userCountRow['userCount'] ?? 0);

$bookCountQuery = "SELECT SUM(Copy) AS bookCount FROM books";
$bookCountResult = mysqli_query($conn, $bookCountQuery);
$bookCountRow = mysqli_fetch_assoc($bookCountResult);
$bookCount = ($bookCountRow['bookCount'] ?? 0);

$boxCountQuery = "SELECT COUNT(*) AS boxCount FROM boxs";
$boxCountResult = mysqli_query($conn, $boxCountQuery);
$boxCountRow = mysqli_fetch_assoc($boxCountResult);
$boxCount = ($boxCountRow['boxCount'] ?? 0);

$query = "SELECT m.fullname, i.bookID, i.IssueDate, i.DueDate, i.ReturnDate 
          FROM issuebook i 
          JOIN register m ON i.memberID = m.memberID 
          ORDER BY i.DueDate ASC"; 
$result = mysqli_query($conn, $query);

$totalIssues = mysqli_num_rows($result);

function getTotalFines($conn) {
    $boxFinesQuery = "SELECT IFNULL(SUM(amount), 0) AS boxFines FROM boxfines";
    $boxFinesResult = $conn->query($boxFinesQuery);
    $boxFinesTotal = $boxFinesResult->fetch_assoc()['boxFines'];

    $bookFinesQuery = "SELECT IFNULL(SUM(amount), 0) AS bookFines FROM fines";
    $bookFinesResult = $conn->query($bookFinesQuery);
    $bookFinesTotal = $bookFinesResult->fetch_assoc()['bookFines'];

    $totalFines = $boxFinesTotal + $bookFinesTotal;

    return 'RM' . number_format($totalFines, 2);
}

function getStatus($dueDate, $returnDate) {
    $currentDate = date('Y-m-d');
    if ($returnDate != NULL) {
        if (strtotime($returnDate) > strtotime($dueDate)) {
            return ['late', 'Late Return']; 
        } else {
            return ['return', 'Returned']; 
        }
    } elseif (strtotime($currentDate) <= strtotime($dueDate)) {
        return ['pending', 'In Progress']; 
    } else {
        return ['exceed', 'Exceeded']; 
    }
}

$query = "SELECT m.fullname, i.bookID, i.IssueDate, i.DueDate, i.ReturnDate, b.ISBN
          FROM issuebook i 
          JOIN register m ON i.memberID = m.memberID 
          JOIN books b ON i.bookID = b.book_acquisition
          ORDER BY i.DueDate ASC";
$result = mysqli_query($conn, $query);
$resultForCounting = $result;

$statusCounts = [
    'exceed' => 0,
    'pending' => 0,
    'late' => 0,
    'return' => 0
];

while ($row = mysqli_fetch_assoc($resultForCounting)) {
    list($statusClass, $statusText) = getStatus($row['DueDate'], $row['ReturnDate']);
    if (array_key_exists($statusClass, $statusCounts)) {
        $statusCounts[$statusClass]++;
    }
}

mysqli_data_seek($result, 0);

$totalIssues = array_sum($statusCounts);

$statusPercentages = [];

foreach ($statusCounts as $status => $count) {
    $statusPercentages[$status] = [
        'count' => $count,
        'percent' => $totalIssues > 0 ? ($count / $totalIssues) * 100 : 0
    ];
}
echo "<script>var statusCounts = " . json_encode($statusCounts) . ";</script>";

// Queries for top review book and box ensuring unique names
$topReviewBookQuery = "
    SELECT DISTINCT r.fullname, r.picture, rv.memberID, COUNT(rv.reviewID) AS totalReviews, MAX(rv.DateReview) AS lastReviewDate
    FROM reviews rv
    INNER JOIN register r ON rv.memberID = r.memberID
    INNER JOIN issuebook ib ON rv.issueID = ib.issueID
    GROUP BY rv.memberID
    ORDER BY totalReviews DESC, lastReviewDate DESC
    LIMIT 10";
$topReviewBookResult = mysqli_query($conn, $topReviewBookQuery);

$topReviewBoxQuery = "
    SELECT DISTINCT r.fullname, r.picture, rv.memberID, COUNT(rv.reviewBoxID) AS totalReviews, MAX(rv.DateReview) AS lastReviewDate
    FROM reviewbox rv
    INNER JOIN register r ON rv.memberID = r.memberID
    INNER JOIN issuebox ib ON rv.issueBoxID = ib.issueBoxID
    GROUP BY rv.memberID
    ORDER BY totalReviews DESC, lastReviewDate DESC
    LIMIT 10";
$topReviewBoxResult = mysqli_query($conn, $topReviewBoxQuery);

// Query to fetch box issue counts per month
$queryBoxes = "SELECT MONTH(IssueDate) AS IssueMonth, COUNT(*) AS Count
               FROM issuebox
               WHERE YEAR(IssueDate) = YEAR(CURDATE())
               GROUP BY MONTH(IssueDate)";
$resultBoxes = mysqli_query($conn, $queryBoxes);

$monthlyBoxIssues = [];
while ($row = mysqli_fetch_assoc($resultBoxes)) {
    $monthlyBoxIssues[$row['IssueMonth']] = (int) $row['Count'];
}

// Query to fetch book issue counts per month
$queryBooks = "SELECT MONTH(IssueDate) AS IssueMonth, COUNT(*) AS Count
               FROM issuebook
               WHERE YEAR(IssueDate) = YEAR(CURDATE())
               GROUP BY MONTH(IssueDate)";
$resultBooks = mysqli_query($conn, $queryBooks);

$monthlyBookIssues = [];
while ($row = mysqli_fetch_assoc($resultBooks)) {
    $monthlyBookIssues[$row['IssueMonth']] = (int) $row['Count'];
}

$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Prepare data for chart
$chartData = [];
for ($i = 1; $i <= 12; $i++) {
    $chartData[] = [
        $monthNames[$i - 1], 
        $monthlyBoxIssues[$i] ?? 0, 
        $monthlyBookIssues[$i] ?? 0 
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body>

<div class="cardBox">
    <div class="card">
        <div>
            <div class="numbers"><?php echo $userCount; ?></div>
            <div class="cardName">Members</div>
        </div>
        <div class="iconBox">
            <ion-icon name="people-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $bookCount; ?></div>
            <div class="cardName">Books</div>
        </div>
        <div class="iconBox">
            <ion-icon name="book-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $boxCount; ?></div>
            <div class="cardName">Storage Box</div>
        </div>
        <div class="iconBox">
            <ion-icon name="laptop-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo getTotalFines($conn); ?></div>
            <div class="cardName">Fines</div>
        </div>
        <div class="iconBox">
            <ion-icon name="card-outline"></ion-icon>
        </div>
    </div>
</div>

<div class="container">
    <div class="issueRecords">
        <div class="IssueBoxH">
            <h2 style="color: var(--blue2);">Box Issue Records</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Box ID</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($boxIssueResult)): ?>
                <?php list($statusClass, $statusText) = getBoxStatus($row['DueDate'], $row['ReturnDate']); ?>
                <tr>
                    <td><?= htmlspecialchars($row['fullname']); ?></td>
                    <td><?= htmlspecialchars($row['BoxSerialNum']); ?></td>
                    <td><?= htmlspecialchars($row['IssueDate']); ?></td>
                    <td><?= htmlspecialchars($row['DueDate']); ?></td>
                    <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalBoxPages; $i++): ?>
                <a href="?boxPage=<?= $i; ?>" class="<?= ($boxPage === $i) ? 'active' : ''; ?>">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    
    <div class="chartContainer1" style="flex: 1; min-width: 400px; height: 550px;">
        <h2 style="color: var(--blue2);">Issues Records</h2>
        <div id="monthlyIssueChart" style="width: 100%; height: 100%;"></div>
    </div>
</div>

<div class="issuesBox">
    <div class="issueHistory">
        <div class="issueHeader">
            <h2>Borrow Book Records</h2>
            <a href="#" class="btn">View All</a>
        </div>
        <table>
            <thead>
                <tr>
                    <td>Name</td>
                    <td>ISBN</td>
                    <td>Borrow Date</td>
                    <td>Due Date</td>
                    <td>Status</td>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($paginationResult)): ?>
                    <?php list($statusClass, $statusText) = getStatus($row['DueDate'], $row['ReturnDate']); ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo isset($row['ISBN']) ? htmlspecialchars($row['ISBN']) : 'No ISBN available'; ?></td>
                        <td><?php echo htmlspecialchars($row['IssueDate']); ?></td>
                        <td><?php echo htmlspecialchars($row['DueDate']); ?></td>
                        <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <!-- Pagination Links -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i; ?>" class="<?= ($page === $i) ? 'active' : ''; ?>">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    
    <div class="chartContainer">
        <div id="statusPieChart"></div>
        <div class="chartLegend">
            <table class="statusTable">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Total</th>
                        <th>Percent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statusPercentages as $status => $data): ?>
                        <tr>
                            <td>
                                <span class="legendIndicator" style="background-color: <?= $statusColors[$status]; ?>"></span>
                                <?= ucfirst($status); ?>
                            </td>
                            <td><?= number_format($data['count'], 2); ?></td>
                            <td><?= number_format($data['percent'], 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="reviewsContainer" style="display: flex; justify-content: space-between; margin-top: 20px;">
    <!-- Top Review Books Table -->
    <div class="topReviewBooks" style="width: 48%; background-color: white; padding: 20px; border-radius: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
        <h2 style="color: #2e2185;">Top Review Books</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Full Name</th>
                    <th>Total Reviews</th>
                    <th>Last Review Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($row = mysqli_fetch_assoc($topReviewBookResult)): ?>
                <tr>
                    <td><?= $i; ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($row['picture']); ?>" alt="User Picture">
                        <?= htmlspecialchars($row['fullname']); ?>
                    </td>
                    <td class="totalReviews"><?= htmlspecialchars($row['totalReviews']); ?></td>
                    <td><?= htmlspecialchars($row['lastReviewDate']); ?></td>
                </tr>
                <?php
                $i++;
                endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Top Review Boxes Table -->
    <div class="topReviewBoxes" style="width: 48%; background-color: white; padding: 20px; border-radius: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
        <h2 style="color: #2e2185;">Top Review Boxes</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Full Name</th>
                    <th>Total Reviews</th>
                    <th>Last Review Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($row = mysqli_fetch_assoc($topReviewBoxResult)): ?>
                <tr>
                    <td><?= $i; ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($row['picture']); ?>" alt="User Picture">
                        <?= htmlspecialchars($row['fullname']); ?>
                    </td>
                    <td class="totalReviews"><?= htmlspecialchars($row['totalReviews']); ?></td>
                    <td><?= htmlspecialchars($row['lastReviewDate']); ?></td>
                </tr>
                <?php
                $i++;
                endwhile; ?>
            </tbody>
        </table>
    </div>
</div>



<script>
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawPieChart);

    function drawPieChart() {
        var data = google.visualization.arrayToDataTable([
            ['Status', 'Number'],
            ['Exceeded', statusCounts.exceed],
            ['Pending', statusCounts.pending],
            ['Late Return', statusCounts.late],
            ['Returned', statusCounts.return]
        ]);

        var options = {
            title: 'Book Borrow Status',
            titleTextStyle: {
                fontSize: 20
            },
            pieHole: 0.4,
            chartArea: {
                width: '85%',  
                height: '85%', 
                left: '15%',   
                top: '20%'    
            }
        };

        var chart = new google.visualization.PieChart(document.getElementById('statusPieChart'));
        chart.draw(data, options);
    }
</script>

<script>
    google.charts.load('current', {'packages':['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawMonthlyChart);

    function drawMonthlyChart() {
        var data = google.visualization.arrayToDataTable([
            ['Month', 'Boxes', 'Books'],
            <?php foreach ($chartData as $data) {
                echo "['" . $data[0] . "', " . $data[1] . ", " . $data[2] . "],";
            } ?>
        ]);

        var options = {
            chart: {
                title: 'Monthly Issues',
                subtitle: 'Books and Boxes',
            },
            bars: 'vertical',
            vAxis: {format: 'decimal'},
            height: 400,
            colors: ['#1b9e77', '#d95f02']
        };

        var chart = new google.charts.Bar(document.getElementById('monthlyIssueChart'));
        chart.draw(data, google.charts.Bar.convertOptions(options));
    }
</script>

</body>
</html>


    <!-- 
    <div class="card">
        <div>
            <div class="numbers" id="currentDate"><?php echo date('Y-m-d'); ?></div>
            <div class="cardName">Date Today</div>
        </div>
        <div class="iconBox">
            <ion-icon name="calendar-number-outline"></ion-icon>
        </div>
    </div>
   // document.getElementById('currentDate').innerText = new Date().toLocaleDateString();
-->