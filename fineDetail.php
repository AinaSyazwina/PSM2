<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine Details</title>
    <?php include 'navigation.php'; ?>
    <link rel="stylesheet" href="Cssfile/style4.css">
    <link rel="stylesheet" href="Cssfile/box.css">
    <link rel="stylesheet" href="Cssfile/fine.css">
</head>
<body>
<?php 
include 'config.php'; 

function getPenaltyStudentsCount($conn) {
    $query = "SELECT COUNT(DISTINCT memberID) AS penaltyStudents FROM fines WHERE amount > 0";
    $result = $conn->query($query);
    return $result->fetch_assoc()['penaltyStudents'];
}

function getTotalFines($conn) {
    $query = "SELECT SUM(amount) AS totalFines FROM fines";
    $result = $conn->query($query);
    return $result->fetch_assoc()['totalFines'];
}

function getPaidFineStudentsCount($conn) {
    $query = "SELECT COUNT(DISTINCT memberID) AS paidStudents FROM fines WHERE isPaid = 1";
    $result = $conn->query($query);
    return $result->fetch_assoc()['paidStudents'];
}

function getUnpaidFinesCount($conn) {
    $query = "SELECT COUNT(*) AS unpaidFines FROM fines WHERE isPaid = 0";
    $result = $conn->query($query);
    return $result->fetch_assoc()['unpaidFines'];
}
?>

<div class="cardBox">
    <div class="card">
        <div>
            <div class="numbers"><?php echo getPenaltyStudentsCount($conn); ?></div>
            <div class="cardName">Penalty Students</div>
        </div>
        <div class="iconBox">
            <i class='bx bx-user'></i>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo getTotalFines($conn); ?></div>
            <div class="cardName">Total Fines</div>
        </div>
        <div class="iconBox">
            <i class='bx bx-calculator'></i>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo getPaidFineStudentsCount($conn); ?></div>
            <div class="cardName">Paid Fines</div>
        </div>
        <div class="iconBox">
            <i class='bx bx-wallet-alt'></i>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo getUnpaidFinesCount($conn); ?></div>
            <div class="cardName">Unpaid Fines</div>
        </div>
        <div class="iconBox">
            <ion-icon name="card-outline"></ion-icon>
        </div>
    </div>
</div>

<div class="details">
    <div class="BookList">
        <div class="bookHeader">
            <h2>List of Student Book Fines</h2>
            <div class="search3">
                <label>
                    <input type="text" placeholder="Click here" id="searchInput">
                </label>
                <div class="sort">
                    <button class="sortbtn">
                        <ion-icon name="filter-outline"></ion-icon> Filter
                    </button>
                    <div class="sort-content">
                        <a href="#" data-sort="all">All</a>
                        <a href="#" data-sort="returnLate">Return Late</a>
                        <a href="#" data-sort="damage">Damage</a>
                        <a href="#" data-sort="missing">Missing</a>
                        <a href="#" data-sort="paid">Paid</a>
                        <a href="#" data-sort="unpaid">Unpaid</a>
                    </div>
                </div>
            </div>
        </div>
        <table id="BookListTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>MemberID</th>
                    <th>ISBN</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Remark</th>
                    <th>Total Fine</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php 
include 'config.php';

$recordsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

$sort = $_GET['sort'] ?? 'all';
$whereClause = "1 = 1";  // Default condition that always evaluates to true

switch ($sort) {
    case 'returnLate':
        $whereClause = "fb.type = 'return late'";
        break;
    case 'damage':
        $whereClause = "fb.type = 'damage'";
        break;
    case 'missing':
        $whereClause = "fb.type = 'missing'";
        break;
    case 'paid':
        $whereClause = "f.isPaid = 1";
        break;
    case 'unpaid':
        $whereClause = "f.isPaid = 0";
        break;
    case 'all':
        $whereClause = "1 = 1";  // Resets the filter
        break;
}

$countQuery = "
SELECT COUNT(*) AS totalRecords
FROM issuebook ib
JOIN register r ON ib.memberID = r.memberID
LEFT JOIN fines f ON ib.issueID = f.issueBookID
LEFT JOIN finebook fb ON f.fineID = fb.fineID
WHERE (ib.ReturnDate > ib.DueDate OR fb.type IN ('damage', 'missing')) AND $whereClause;
";
$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['totalRecords'];
$totalPages = ceil($totalRecords / $recordsPerPage);

$query = "
SELECT 
    r.fullName,
    ib.memberID,
    b.ISBN,
    ib.IssueDate,
    ib.DueDate,
    ib.ReturnDate,
    fb.amount AS FineAmount,
    f.isPaid,
    fb.type AS FineType
FROM issuebook ib
JOIN register r ON ib.memberID = r.memberID
JOIN books b ON ib.bookID = b.book_acquisition  
LEFT JOIN fines f ON ib.issueID = f.issueBookID
LEFT JOIN finebook fb ON f.fineID = fb.fineID
WHERE (ib.ReturnDate > ib.DueDate OR fb.type IN ('damage', 'missing')) AND $whereClause
ORDER BY r.fullName
LIMIT $offset, $recordsPerPage;
";

$result = $conn->query($query);
$counter = $offset + 1; 
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $remark = calculateRemark($row['DueDate'], $row['ReturnDate'], $row['FineType']); 
        $status = ($row['isPaid'] == 1) ? "Paid" : "Unpaid";
        $statusClass = ($status === "Paid") ? "status-paid" : "status-unpaid";
        echo "<tr>";
        echo "<td>" . $counter++ . "</td>";
        echo "<td>" . htmlspecialchars($row['fullName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['memberID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ISBN']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DueDate']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ReturnDate'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($remark) . "</td>";
        echo "<td>" . htmlspecialchars($row['FineAmount']) . "</td>";
        echo "<td class='text-center " . $statusClass . "'>" . htmlspecialchars($status) . "</td>";
        echo "</tr>";
    }
} else {
    echo "Error fetching data: " . $conn->error;
}

$conn->close();
?>
            </tbody>
        </table>
        
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i; ?>&sort=<?= htmlspecialchars($sort) ?>" class="<?= ($page === $i) ? 'active' : ''; ?>">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    bindSearchEvents();
    bindSortingEvents();
});

function bindSearchEvents() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#BookListTable tbody tr');

        tableRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            row.style.display = rowText.includes(searchValue) ? '' : 'none';
        });
    });
}

function bindSortingEvents() {
    document.querySelectorAll('.sort-content a').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const sortType = this.getAttribute('data-sort');
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort', sortType);
            urlParams.set('page', 1);
            window.location.search = urlParams.toString();
        });
    });
}
</script>

</body>
</html>

<?php
include 'config.php';

function calculateRemark($dueDate, $returnDate, $fineType = null) {
    $remark = 'On Time';

    if ($returnDate) {
        $dueDateTime = new DateTime($dueDate);
        $returnDateTime = new DateTime($returnDate);
        if ($returnDateTime > $dueDateTime) {
            $remark = 'return late';
        }
    } elseif ($fineType === 'missing') {
        $remark = 'missing';
    }

    if ($fineType === 'damage') {
        $remark = 'damage';
    } elseif ($fineType === 'kept') {
        $remark = 'kept';
    }

    return $remark;
}

function insertLateReturnFines($conn) {
    $lateReturnsQuery = "
        SELECT ib.issueID, ib.memberID, ib.bookID, ib.IssueDate, ib.DueDate, ib.ReturnDate
        FROM issuebook ib
        LEFT JOIN fines f ON ib.issueID = f.issueBookID
        WHERE ib.ReturnDate > ib.DueDate
        AND (f.issueBookID IS NULL OR f.amount IS NULL)";

    $lateReturnsResult = $conn->query($lateReturnsQuery);

    if ($lateReturnsResult->num_rows > 0) {
        while ($row = $lateReturnsResult->fetch_assoc()) {
            $fineAmount = 2.00; 
            $fineTypeID = 1; 

            $insertFineQuery = "
                INSERT INTO fines (memberID, issueBookID, amount, isPaid, fineID)
                VALUES (?, ?, ?, 0, ?)
                ON DUPLICATE KEY UPDATE amount = VALUES(amount), isPaid = VALUES(isPaid), fineID = VALUES(fineID)";

            $insertFineStmt = $conn->prepare($insertFineQuery);
            $insertFineStmt->bind_param('sidi', $row['memberID'], $row['issueID'], $fineAmount, $fineTypeID);

            if (!$insertFineStmt->execute()) {
                echo "Error inserting fine for memberID: " . $row['memberID'] . " - " . $insertFineStmt->error;
            }
        }
    }
}

insertLateReturnFines($conn);

$conn->close();
?>
