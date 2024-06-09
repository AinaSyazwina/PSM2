<?php
ini_set('display_errors', 0);
error_reporting(0);

ob_start();

include 'config.php'; 

$session = isset($_POST['session']) ? $_POST['session'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$reportType = isset($_POST['reportType']) ? $_POST['reportType'] : '';
$category = isset($_POST['category']) ? $_POST['category'] : '';

if (empty($session) || empty($reportType) || empty($category)) {
    $response = ['error' => 'Required fields are missing'];
    sendResponseAndExit($response);
}

$month_parameter = $month === 'all' ? '%' : date('m', strtotime("1 " . $month . " 2000"));
$sql_condition = $month === 'all' ? "LIKE ?" : "= ?";


if ($category == 'Book' && $reportType == 'Borrow Return') {
    $sql = "SELECT 
                ib.issueID, 
                r.fullname AS fullname,   
                ib.bookID, 
                b.ISBN,
                b.Title, 
                ib.IssueDate, 
                ib.DueDate, 
                ib.ReturnDate, 
                b.genre,
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
            WHERE YEAR(ib.IssueDate) = ? AND MONTH(ib.IssueDate) " . $sql_condition . " AND MONTH(ib.ReturnDate) " . $sql_condition;
} elseif ($category == 'Box' && $reportType == 'Borrow Return') {
   
    $sql = "SELECT 
                ib.issueBoxID AS issueID, 
                r.fullname AS fullname,
                ib.BoxSerialNum,
                b.category, 
                ib.IssueDate, 
                ib.DueDate, 
                ib.ReturnDate,
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
            WHERE YEAR(ib.IssueDate) = ? AND MONTH(ib.IssueDate) " . $sql_condition . " AND MONTH(ib.ReturnDate) " . $sql_condition;
} elseif ($category == 'Book' && $reportType == 'Fine'){
    $sql = "SELECT
    r.fullName AS fullname,
    ib.memberID,
    ib.bookID,
    b.ISBN,
    b.Title,
    ib.IssueDate,
    ib.DueDate AS DueDate,
    ib.ReturnDate AS ReturnDate,
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
WHERE YEAR(ib.IssueDate) = ? AND (MONTH(ib.IssueDate) $sql_condition OR MONTH(ib.ReturnDate) $sql_condition)
AND (ib.ReturnDate > ib.DueDate OR fb.type IN ('damage', 'kept'))
ORDER BY r.fullName";
} 

elseif ($category == 'Box' && $reportType == 'Fine') {

    $sql = "SELECT 
    r.fullName AS fullname, 
    ib.memberID,
    ib.BoxSerialNum,
    b.category,
    ib.IssueDate,
    ib.DueDate AS DueDate,
    ib.ReturnDate AS ReturnDate,
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
WHERE YEAR(ib.IssueDate) = ? AND (MONTH(ib.IssueDate) $sql_condition OR MONTH(ib.ReturnDate) $sql_condition)
AND (ib.ReturnDate > ib.DueDate OR fb.type IN ('damage', 'kept'))
ORDER BY r.fullName";

} else {

    $response = ['error' => 'Invalid category or report type'];
    sendResponseAndExit($response);
}


$stmt = $conn->prepare($sql);
if (!$stmt) {
    $response = ['error' => 'Prepare failed: ' . $conn->error];
    sendResponseAndExit($response);
}


if ($month === 'all') {
    $stmt->bind_param("sss", $session, $month_parameter, $month_parameter);
} else {
    $stmt->bind_param("ssi", $session, $month_parameter, $month_parameter);
}


if (!$stmt->execute()) {
    $response = ['error' => 'Execute failed: ' . $stmt->error];
    sendResponseAndExit($response);
}


$result = $stmt->get_result();
if ($result) {
    $data = $result->fetch_all(MYSQLI_ASSOC);
   
    error_log(print_r($data, true));
    $response = ['data' => $data];
} else {
    $response = ['error' => 'Get result failed: ' . $stmt->error];
    sendResponseAndExit($response);
}

$stmt->close();
$conn->close();

sendResponseAndExit($response);

function sendResponseAndExit($response) {

    ob_get_clean();
    $json = json_encode($response);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response = ['error' => 'JSON encoding error: ' . json_last_error_msg()];

        $json = json_encode($response);
        if ($json === false) {     
            $json = '{"error":"unknown"}';  
            http_response_code(500);
        }
    }

    header('Content-Type: application/json');

    echo $json;
    exit();
}


?>
