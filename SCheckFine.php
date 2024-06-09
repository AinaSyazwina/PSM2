<?php
session_start();
include 'config.php';

$noInformation = true;
$books = array();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

$memberID = $_SESSION['memberID'];
$query = "
SELECT 
    r.fullName,
    ib.memberID,
    ib.bookID,
    b.ISBN,
    b.Title AS BookTitle,
    ib.IssueDate,
    ib.DueDate AS BookDueDate,
    ib.ReturnDate AS BookReturnDate,
    COALESCE(f.amount, 0) AS FineAmount,
    CASE 
        WHEN f.isPaid = 1 THEN 'Paid' 
        ELSE 'Unpaid' 
    END AS FineStatus,
    f.datePaid AS FinePaymentDate,
    fb.type AS FineType
FROM issuebook ib
JOIN register r ON ib.memberID = r.memberID
JOIN books b ON ib.bookID = b.book_acquisition
LEFT JOIN fines f ON ib.issueID = f.issueBookID
LEFT JOIN finebook fb ON f.fineID = fb.fineID
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
</head>
<body>
<?php include 'navigaStu.php'; ?>

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
        <div class="cardName">Total Unpaid</div>
    </div>
    <div class="iconBox">
    <ion-icon name="card-outline"></ion-icon>
    </div>
</div>


      
    </div>
 <div class="container"> 
 <div class="container-header">
 <h2>List of Fine Books</h2>
    </div>
<div class="container-body">
<table>
        <thead>
            <tr>
                <th>No</th>
                <th>Title</th>
                <th>ISBN</th>
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
    <td><?php echo htmlspecialchars($book['BookTitle']); ?></td>
    <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
    <td><?php echo htmlspecialchars($book['BookDueDate']); ?></td>
    <td><?php echo $book['BookReturnDate'] ? htmlspecialchars($book['BookReturnDate']) : 'Not Returned'; ?></td>
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