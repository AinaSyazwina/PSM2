
 <?php
session_start();
include 'config.php'; 

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php'); 
    exit;
}

$memberID = $_SESSION['memberID']; 

// Total Issues
$queryTotalIssues = "SELECT COUNT(*) as Total FROM issuebox WHERE memberID = ?";
$stmt = $conn->prepare($queryTotalIssues);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
$totalIssues = $result->fetch_assoc()['Total'];

// In Process (not yet returned)
$queryInProcess = "SELECT COUNT(*) as InProcess FROM issuebox WHERE memberID = ? AND ReturnDate IS NULL";
$stmt = $conn->prepare($queryInProcess);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
$inProcess = $result->fetch_assoc()['InProcess'];

// Number of Boxes Returned
$queryReturned = "SELECT COUNT(*) as Returned FROM issuebox WHERE memberID = ? AND ReturnDate IS NOT NULL";
$stmt = $conn->prepare($queryReturned);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
$returned = $result->fetch_assoc()['Returned'];

$maxIssues = 2;
$availableIssues = $maxIssues - $inProcess;

$query = "
SELECT 
ib.issueBoxID, 
r.fullname, 
ib.BoxSerialNum, 
ib.IssueDate, 
ib.DueDate, 
ib.ReturnDate, 
b.category,
CASE
    WHEN ib.DueDate < CURDATE() AND ib.ReturnDate IS NULL THEN 'Exceed'
    WHEN ib.DueDate < ib.ReturnDate THEN 'Return Late'
    WHEN ib.DueDate >= ib.ReturnDate THEN 'On Time'
    WHEN ib.ReturnDate IS NULL THEN 'In Process'
    ELSE 'Unknown'
END as Status
FROM issuebox ib
INNER JOIN register r ON ib.memberID = r.memberID
INNER JOIN boxs b ON ib.BoxSerialNum = b.BoxSerialNum
    WHERE ib.memberID = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $noInformation = true;
} else {
    $books = $result->fetch_all(MYSQLI_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="Cssfile/ScheckIssue.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
.center-text {
    text-align: center;   
    width: 100%;          
}
</style>


</head>
<body>
<?php include 'navigaStu.php'; ?>

<div class="container-padding">
<div class="cardBox">
    <div class="card">
        <div>
            <div class="numbers"><?php echo $totalIssues; ?></div>
            <div class="cardName">Total Issue</div>
        </div>
        <div class="iconBox">
        <ion-icon name="trash-bin-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $inProcess; ?></div>
            <div class="cardName">In Process</div>
        </div>
        <div class="iconBox">
        <ion-icon name="hand-right-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $returned; ?></div>
            <div class="cardName">Returned Box</div>
        </div>
        <div class="iconBox">
        <ion-icon name="checkmark-circle-outline"></ion-icon>
        </div>
    </div>

    <div class="card">
        <div>
            <div class="numbers"><?php echo $availableIssues; ?></div>
            <div class="cardName">Available Issue</div>
        </div>
        <div class="iconBox">
        <ion-icon name="download-outline"></ion-icon>
        </div>
    </div>
</div>


 <div class="container"> 
 <div class="container-header">
 <h2>List of Issued Box</h2>
    </div>
<div class="container-body">
<table>
        <thead>
            <tr>
                <th>No</th>
                <th>BoxSerialNum</th>
                <th>Category</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($noInformation)): ?>
                <tr>
                <td colspan="7" class="center-text">No information available.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($books as $index => $book): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($book['BoxSerialNum']); ?></td>
                        <td><?php echo htmlspecialchars($book['category']); ?></td>
                        <td><?php echo htmlspecialchars($book['IssueDate']); ?></td>
                        <td><?php echo htmlspecialchars($book['DueDate']); ?></td>
                        <td><?php echo htmlspecialchars($book['ReturnDate'] ?? 'Not Returned'); ?></td>
                        <td><?php echo htmlspecialchars($book['Status']); ?></td>
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