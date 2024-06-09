<?php
include 'config.php';

$type = $_GET['type'] ?? 'all';  // Default to show all
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

$whereClause = "1 = 1";  // Default condition that always evaluates to true

switch ($type) {
    case 'returnLate':
        $whereClause = "fb.fineID = 1";
        break;
    case 'damage':
        $whereClause = "fb.fineID = 4";
        break;
    case 'missing':
        $whereClause = "fb.fineID = 3";
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
FROM boxfines f
JOIN issuebox ib ON f.issueBoxID = ib.issueBoxID
JOIN register r ON ib.memberID = r.memberID
JOIN finebox fb ON f.fineID = fb.fineID
WHERE $whereClause
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
    ib.DueDate,
    ib.ReturnDate,
    fb.amount AS FineAmount,
    fb.type AS Remark, 
    f.issueBoxID,
    f.isPaid
FROM boxfines f
JOIN issuebox ib ON f.issueBoxID = ib.issueBoxID
JOIN register r ON ib.memberID = r.memberID
JOIN finebox fb ON f.fineID = fb.fineID
WHERE $whereClause
ORDER BY r.fullName
LIMIT $offset, $recordsPerPage;
";

$result = $conn->query($query);
$counter = $offset + 1;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $paidStatus = $row['isPaid'] ? 'Paid' : 'Unpaid';
        echo "<tr>";
        echo "<td>" . $counter++ . "</td>";
        echo "<td>" . htmlspecialchars($row['fullName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['memberID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['BoxSerialNum']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DueDate']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ReturnDate'] ?? 'N/A') . "</td>";
        echo "<td class='text-center'>" . htmlspecialchars($row['Remark']) . "</td>";
        echo "<td class='text-center'>" . htmlspecialchars($row['FineAmount']) . "</td>";
        echo "<td class='text-center " . ($paidStatus === "Paid" ? "status-paid" : "status-unpaid") . "'>" . htmlspecialchars($paidStatus) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No records found.</td></tr>";
}

$conn->close();


?>
