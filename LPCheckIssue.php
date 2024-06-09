<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php'; 

$noInformation = false;

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'LibPre') {
    header('Location: index.php');
    exit();
}

$memberID = $_SESSION['memberID']; // Get the member ID from session

// Total Book Issues
$queryTotalBooks = "SELECT COUNT(*) as Total FROM issuebook WHERE memberID = ?";
$stmt = $conn->prepare($queryTotalBooks);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
$totalBooks = $result->fetch_assoc()['Total'];

// Books In Process (not yet returned)
$queryBooksInProcess = "SELECT COUNT(*) as InProcess FROM issuebook WHERE memberID = ? AND ReturnDate IS NULL";
$stmt = $conn->prepare($queryBooksInProcess);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
$booksInProcess = $result->fetch_assoc()['InProcess'];

// Number of Books Returned
$queryBooksReturned = "SELECT COUNT(*) as Returned FROM issuebook WHERE memberID = ? AND ReturnDate IS NOT NULL";
$stmt = $conn->prepare($queryBooksReturned);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
$booksReturned = $result->fetch_assoc()['Returned'];

// Calculating Available Issues (Max 4 - currently borrowed)
$maxBookIssues = 4;
$availableBookIssues = $maxBookIssues - $booksInProcess;

$query = "
    SELECT 
        ib.issueID, 
        r.fullname, 
        ib.bookID, 
        b.Title, 
        b.ISBN,
        ib.IssueDate, 
        ib.DueDate, 
        ib.ReturnDate, 
        b.Genre,
        CASE
            WHEN ib.DueDate < CURDATE() AND ib.ReturnDate IS NULL THEN 'Exceed'
            WHEN ib.DueDate < ib.ReturnDate THEN 'Return Late'
            WHEN ib.DueDate >= ib.ReturnDate THEN 'On Time'
            WHEN ib.ReturnDate IS NULL THEN 'In Process'
            ELSE 'Unknown'
        END as Status
    FROM issuebook ib
    INNER JOIN register r ON ib.memberID = r.memberID
    INNER JOIN books b ON ib.bookID = b.book_acquisition
    WHERE ib.memberID = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();

// Check if the student has borrowed any books
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
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
<?php
session_start(); 

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        include 'navigation.php'; 
    } elseif ($_SESSION['role'] == 'LibPre') {
        include 'navigaLib.php'; 
    } else {

    }
} else {

    header('Location: index.php');
    exit();
}
?>

<div class="container-padding">
<div class="cardBox">
        <div class="card">
            <div>
                <div class="numbers"><?php echo $totalBooks; ?></div>
                <div class="cardName">Total Issues</div>
            </div>
            <div class="iconBox">
            <ion-icon name="book-outline"></ion-icon>
        </div>
        </div>

        <div class="card">
            <div>
                <div class="numbers"><?php echo $booksInProcess; ?></div>
                <div class="cardName"> In Process</div>
            </div>
            <div class="iconBox">
            <ion-icon name="arrow-down-circle-outline"></ion-icon>
        </div>
        </div>

        <div class="card">
            <div>
                <div class="numbers"><?php echo $booksReturned; ?></div>
                <div class="cardName">Returned</div>
            </div>
            <div class="iconBox">
            <ion-icon name="checkmark-circle-outline"></ion-icon>
        </div>
        </div>

        <div class="card">
            <div>
                <div class="numbers"><?php echo $availableBookIssues; ?></div>
                <div class="cardName">Available Issue</div>
            </div>
            <div class="iconBox">
            <ion-icon name="hand-left-outline"></ion-icon>
        </div>
        </div>
    </div>

    <div class="container">
        <div class="container-header">
            <h2>List of Issued Books</h2>
        </div>
        <div class="container-body">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Title</th>
                        <th>ISBN</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($noInformation): ?>
                        <tr>
                            <td colspan="7">No information available.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($books as $index => $book): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($book['Title']); ?></td>
                                <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
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
