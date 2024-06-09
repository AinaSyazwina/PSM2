<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

$noInformation = true;
$books = array();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'LibPre') {
    header('Location: index.php');
    exit();
}

$memberID = $_SESSION['memberID'];
$query = "
SELECT 
    r.fullName,
    ib.memberID,
    ib.BoxSerialNum,
    b.category,
    ib.IssueDate,
    ib.DueDate AS BoxDueDate,
    ib.ReturnDate AS BoxReturnDate,
    COALESCE(f.amount, 0) AS FineAmount,
    CASE 
        WHEN f.isPaid = 1 THEN 'Paid' 
        ELSE 'Unpaid' 
    END AS FineStatus,
    f.datePaid AS FinePaymentDate,
    fb.type AS FineType
FROM issuebox ib
JOIN register r ON ib.memberID = r.memberID
JOIN boxs b ON ib.BoxSerialNum = b.BoxSerialNum
LEFT JOIN boxfines f ON ib.issueBoxID = f.issueBoxID
LEFT JOIN finebox fb ON f.fineID = fb.fineID
WHERE (ib.ReturnDate > ib.DueDate OR fb.type IN ('damage', 'kept'))
AND ib.memberID = ?
ORDER BY r.fullName



";

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param('s', $memberID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $books = $result->fetch_all(MYSQLI_ASSOC);
        $noInformation = count($books) === 0;
    } else {
       
        echo "Error: " . $conn->error;
    }
    $stmt->close();
} else {
    
    echo "Prepare failed: " . $conn->error;
}

$numberOfFines = 0;
$totalFines = 0;
$paidFines = 0;
$unpaidFines = 0;
$totalUnpaidFinesFormatted = number_format($totalUnpaidFines, 2, '.', '');

// Loop through the books and calculate stats AFTER the $books array is populated
foreach ($books as $book) {
    if ($book['FineAmount'] > 0) {
        $numberOfFines++; 
        $totalFines += $book['FineAmount']; 

        if ($book['FineStatus'] === 'Paid') {
            $paidFines++; 
        } else {
            $unpaidFines++;
            $totalUnpaidFinesFormatted += $book['FineAmount']; 
        }
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="Cssfile/ScheckIssue.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</head>
<body>
<?php include 'navigaLib.php'; ?>

<div class="container-padding">
<div class="cardBox">

<div class="card">
            <div>
            <div class="numbers"><?php echo $numberOfFines; ?></div>
        <div class="cardName">Number Fines</div>
            </div>
            <div class="iconBox">
            <ion-icon name="alert-circle-outline"></ion-icon>
        </div>
        </div>

        <div class="card">
            <div>
            <div class="numbers"><?php echo $paidFines; ?></div>
        <div class="cardName">Paid Fines</div>
            </div>
            <div class="iconBox">
            <ion-icon name="cash-outline"></ion-icon>
        </div>
        </div>

        <div class="card">
            <div>
            <div class="numbers"><?php echo $unpaidFines; ?></div>
        <div class="cardName">Unpaid Fines</div>
            </div>
            <div class="iconBox">
            <ion-icon name="wallet-outline"></ion-icon>
        </div>
        </div>

        <div class="card">
    <div>
        <div class="numbers"><?php echo $totalUnpaidFinesFormatted; ?></div>
        <div class="cardName">Total Unpaid </div>
    </div>
    <div class="iconBox">
    <ion-icon name="card-outline"></ion-icon>
    </div>
</div>
  
    </div>
 <div class="container"> 
 <div class="container-header">
 <h2>List of Fine Box</h2>
    </div>
<div class="container-body">
<table>
        <thead>
            <tr>
                <th>No</th>
                <th>Box Serial Num</th>
                <th>Category</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Date Paid</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
    <?php if ($noInformation): ?>
        <tr>
            <td colspan="9">No information available.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($books as $index => $book): ?>
            <tr>
    <td><?php echo $index + 1; ?></td>
    <td><?php echo htmlspecialchars($book['BoxSerialNum']); ?></td>
    <td><?php echo htmlspecialchars($book['category']); ?></td>
    <td><?php echo htmlspecialchars($book['BoxDueDate']); ?></td>
    <td><?php echo $book['BoxReturnDate'] ? htmlspecialchars($book['BoxReturnDate']) : 'Not Returned'; ?></td>
    <td><?php echo htmlspecialchars($book['FineType']); ?></td>
    <td><?php echo htmlspecialchars($book['FineAmount']); ?></td>
    <td><?php echo $book['FinePaymentDate'] ? htmlspecialchars($book['FinePaymentDate']) : 'Not Paid'; ?></td>
    <td><?php echo htmlspecialchars($book['FineStatus'] === 'Paid' ? 'Paid' : 'Unpaid'); ?></td>

</tr>

        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
    </table>
    </div>
    </div>
</div>

</body>
</html>
