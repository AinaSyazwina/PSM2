<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Box Fine Details</title>
    <?php include 'navigation.php'; ?>
    <link rel="stylesheet" href="Cssfile/style4.css">
    <link rel="stylesheet" href="Cssfile/box.css">
    <link rel="stylesheet" href="Cssfile/fine.css">
</head>
<body>
<?php 
include 'config.php'; // Make sure this file exists and sets up the database connection.

function getPenaltyStudentsCount($conn) {
    $query = "SELECT COUNT(DISTINCT memberID) AS penaltyStudents FROM boxfines WHERE amount > 0";
    $result = $conn->query($query);
    return $result->fetch_assoc()['penaltyStudents'];
}

function getTotalFines($conn) {
    $query = "SELECT SUM(amount) AS totalFines FROM boxfines";
    $result = $conn->query($query);
    return $result->fetch_assoc()['totalFines'];
}

function getPaidFineStudentsCount($conn) {
    $query = "SELECT COUNT(DISTINCT memberID) AS paidStudents FROM boxfines WHERE isPaid = 1";
    $result = $conn->query($query);
    return $result->fetch_assoc()['paidStudents'];
}

function getUnpaidFinesCount($conn) {
    $query = "SELECT COUNT(*) AS unpaidFines FROM boxfines WHERE isPaid = 0";
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

<?php 
include 'config.php';

function getFineData($conn, $type) {
    $stmt = $conn->prepare("SELECT fineID, amount FROM finebox WHERE type = ?");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        return $data; // Return the whole data array including fineID.
    } else {
        return null;
    }
}

function getFineAmount($conn, $type) {
    $stmt = $conn->prepare("SELECT amount FROM finebox WHERE type = ?");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['amount'] : 0;
}

function insertFine($conn, $memberID, $issueBoxID, $fineType, $amount, $fineTypeID) {
    $checkStmt = $conn->prepare("SELECT 1 FROM fines WHERE memberID = ? AND issueBookID = ?");
    $checkStmt->bind_param("si", $memberID, $issueBoxID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        $insertStmt = $conn->prepare("INSERT INTO fines (memberID, issueBoxID, amount, isPaid, fineID) VALUES (?, ?, ?, 0, ?)");
        $insertStmt->bind_param("sidi", $memberID, $issueBoxID, $amount, $fineTypeID);
        if (!$insertStmt->execute()) {
            echo "Error inserting fine: " . $insertStmt->error;
        }
    }
}

function calculateRemark($dueDate, $returnDate, $fineType = null) {
    $remark = 'On Time';
    if ($returnDate) {
        $dueDateTime = new DateTime($dueDate);
        $returnDateTime = new DateTime($returnDate);
        if ($returnDateTime > $dueDateTime) {
            $remark = 'return late';
        }
    }

    if ($fineType === 'damage') {
        $remark = 'damage';
    } elseif ($fineType === 'missing') {
        $remark = 'missing';
    }

    return $remark;
}

function insertLateReturnFines($conn) {
    $conn->begin_transaction();

    $lateReturnsQuery = "
        SELECT ib.issueBoxID, ib.memberID, ib.BoxSerialNum, ib.IssueDate, ib.DueDate, ib.ReturnDate
        FROM issuebox ib
        LEFT JOIN boxfines bf ON ib.issueBoxID = bf.issueBoxID AND bf.amount IS NOT NULL
        WHERE ib.ReturnDate > ib.DueDate AND bf.issueBoxID IS NULL";

    $lateReturnsResult = $conn->query($lateReturnsQuery);

    if ($lateReturnsResult->num_rows > 0) {
        while ($row = $lateReturnsResult->fetch_assoc()) {
            $fineAmount = 5.00;  
            $fineTypeID = 1;     

            $insertFineQuery = "
                INSERT INTO boxfines (memberID, issueBoxID, amount, isPaid, fineID)
                VALUES (?, ?, ?, 0, ?)";

            $insertFineStmt = $conn->prepare($insertFineQuery);
            $insertFineStmt->bind_param('sidi', $row['memberID'], $row['issueBoxID'], $fineAmount, $fineTypeID);

            if (!$insertFineStmt->execute()) {
                echo "Error inserting fine for memberID: " . $row['memberID'] . " - " . $insertFineStmt->error;
                $conn->rollback(); 
                return; 
            }
        }
        $conn->commit();
    } else {
        $conn->rollback(); 
    }
}

insertLateReturnFines($conn);

$conn->close();
?>

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

<div class="details">
    <div class="BookList">
        <div class="bookHeader">
            <h2>List of Student Box Fines</h2>
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
                        <a href="#" data-sort="missing_books">Missing Books</a>
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
                    <th>Box Serial Num</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Fine Type</th>
                    <th>ISBN</th>
                    <th>Total Fine</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php 
include 'config.php';

// Pagination Setup
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
    case 'missing_books':
        $whereClause = "fb.type = 'missing_books'";
        break;
    case 'paid':
        $whereClause = "bf.isPaid = 1";
        break;
    case 'unpaid':
        $whereClause = "bf.isPaid = 0";
        break;
    case 'all':
        $whereClause = "1 = 1";  // Resets the filter
        break;
}

$countQuery = "
SELECT COUNT(*) AS totalRecords
FROM issuebox ib
JOIN register r ON ib.memberID = r.memberID
LEFT JOIN boxfines bf ON ib.issueBoxID = bf.issueBoxID
LEFT JOIN finebox fb ON bf.fineID = fb.fineID
WHERE (ib.ReturnDate > ib.DueDate OR fb.type IN ('damage', 'missing', 'missing_books')) AND $whereClause;
";
$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['totalRecords'];
$totalPages = ceil($totalRecords / $recordsPerPage);

$query = "
SELECT 
    r.fullName,
    ib.memberID,
    ib.BoxSerialNum,
    ib.IssueDate,
    ib.DueDate,
    ib.ReturnDate,
    fb.amount AS FineAmount,
    bf.isPaid,
    fb.type AS FineType,
    bf.bookISBN,
    bf.copyCount
FROM issuebox ib
JOIN register r ON ib.memberID = r.memberID
LEFT JOIN boxfines bf ON ib.issueBoxID = bf.issueBoxID
LEFT JOIN finebox fb ON bf.fineID = fb.fineID
WHERE (ib.ReturnDate > ib.DueDate OR fb.type IN ('damage', 'missing', 'missing_books')) AND $whereClause
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
        echo "<td>" . htmlspecialchars($row['BoxSerialNum']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DueDate']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ReturnDate'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['FineType']) . "</td>";
        echo "<td>" . ($row['FineType'] === 'missing_books' ? htmlspecialchars($row['bookISBN'] . ' (' . $row['copyCount'] . ')') : 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['FineAmount']) . "</td>";
        echo "<td class='text-center " . $statusClass . "'>" . $status . "</td>";
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

</body>
</html>
