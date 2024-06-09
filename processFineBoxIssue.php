<?php
include 'config.php'; // Include the database configuration

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'An error occurred.'
];

// Retrieve POST parameters
$memberID = $_POST['memberID'] ?? '';
$BoxSerialNum = $_POST['BoxSerialNum'] ?? '';
$paymentFor = $_POST['paymentFor'] ?? '';
$total = $_POST['total'] ?? '';
$isbnArray = isset($_POST['isbn']) ? json_decode($_POST['isbn'], true) : null;
$copyCountArray = isset($_POST['copyCount']) ? json_decode($_POST['copyCount'], true) : null;

// Map paymentFor to fineID
$fineMapping = [
    "return_late" => "1",
    "exceed" => "2",
    "missing" => "3",
    "damage" => "4",
    "missing_books" => "5",
];

$fineID = $fineMapping[$paymentFor] ?? null;

// Validate the input
if (!$memberID || !$BoxSerialNum || !$fineID || !$total) {
    $response['message'] = 'Required fields are missing or incorrect.';
    echo json_encode($response);
    exit;
}

// Check if the student is active
$studentStatusQuery = $conn->prepare("SELECT status FROM register WHERE memberID = ?");
$studentStatusQuery->bind_param("s", $memberID);
$studentStatusQuery->execute();
$studentStatusResult = $studentStatusQuery->get_result();

if ($studentStatusResult->num_rows == 0) {
    $response['message'] = 'Student not found.';
    echo json_encode($response);
    exit;
}

$studentStatusRow = $studentStatusResult->fetch_assoc();
if ($studentStatusRow['status'] !== 'active') {
    $response['message'] = 'The student is not active. Fines cannot be issued.';
    echo json_encode($response);
    exit;
}
$studentStatusQuery->close();

// Retrieve the issueBoxID based on BoxSerialNum and memberID
$issueBoxID_stmt = $conn->prepare("SELECT issueBoxID, ReturnDate FROM issuebox WHERE BoxSerialNum = ? AND memberID = ? ORDER BY issueDate DESC LIMIT 1");
$issueBoxID_stmt->bind_param("ss", $BoxSerialNum, $memberID);
$issueBoxID_stmt->execute();
$issueBoxID_result = $issueBoxID_stmt->get_result();

if ($issueBoxID_result->num_rows == 0) {
    $response['message'] = 'No issue record found for the provided BoxSerialNum and memberID.';
    echo json_encode($response);
    exit;
}

$issueBoxID_row = $issueBoxID_result->fetch_assoc();
$issueBoxID = $issueBoxID_row['issueBoxID'];
$returnDate = $issueBoxID_row['ReturnDate'];
$issueBoxID_stmt->close();

// Validate return status for different fines
if ($paymentFor === 'missing' && $returnDate !== null) {
    $response['message'] = 'Box already returned, cannot issue fine for missing box.';
    echo json_encode($response);
    exit;
} elseif (($paymentFor === 'damage' || $paymentFor === 'missing_books') && $returnDate === null) {
    $response['message'] = 'Box not yet returned, cannot issue fine.';
    echo json_encode($response);
    exit;
}

// For missing_books, ensure the ISBN and copy count exist in book_distribution and no duplicate entries
if ($paymentFor === 'missing_books' && $isbnArray && $copyCountArray) {
    for ($i = 0; $i < count($isbnArray); $i++) {
        $isbn = $isbnArray[$i];
        $copyCount = $copyCountArray[$i];

        $bookCheckQuery = $conn->prepare("SELECT * FROM book_distribution WHERE ISBN = ? AND BoxSerialNum = ?");
        $bookCheckQuery->bind_param("ss", $isbn, $BoxSerialNum);
        $bookCheckQuery->execute();
        $bookCheckResult = $bookCheckQuery->get_result();

        if ($bookCheckResult->num_rows == 0) {
            $response['message'] = 'ISBN and copy count do not match records.';
            echo json_encode($response);
            exit;
        }
        $bookCheckQuery->close();
        
        // Check for duplicates
        $existingFineQuery = $conn->prepare("SELECT * FROM boxfines WHERE memberID = ? AND issueBoxID = ? AND fineID = ? AND bookISBN = ?");
        $existingFineQuery->bind_param("ssss", $memberID, $issueBoxID, $fineID, $isbn);
        $existingFineQuery->execute();
        $existingFineResult = $existingFineQuery->get_result();

        if ($existingFineResult->num_rows > 0) {
            $response['message'] = 'Fine already exists for this box, member, and ISBN.';
            echo json_encode($response);
            exit;
        }
        $existingFineQuery->close();
        
        // Insert the fine record for each book
        $stmt = $conn->prepare("INSERT INTO boxfines (memberID, issueBoxID, fineID, amount, isPaid, bookISBN, copyCount) VALUES (?, ?, ?, ?, 0, ?, ?)");
        
        if (false === $stmt) {
            $response['message'] = 'Prepare statement failed: ' . htmlspecialchars($conn->error);
            echo json_encode($response);
            exit;
        }
        
        $amount = $copyCount * 2; // Calculate amount based on copy count
        
        $bind = $stmt->bind_param("sssdsi", $memberID, $issueBoxID, $fineID, $amount, $isbn, $copyCount);
        if (false === $bind) {
            $response['message'] = 'Bind param failed: ' . htmlspecialchars($stmt->error);
            echo json_encode($response);
            exit;
        }
        
        $exec = $stmt->execute();
        if (false === $exec) {
            $response['message'] = 'Execute failed: ' . htmlspecialchars($stmt->error);
            echo json_encode($response);
            exit;
        }
        
        $stmt->close();
    }
} else {
    // Check for duplicates
    $existingFineQuery = $conn->prepare("SELECT * FROM boxfines WHERE memberID = ? AND issueBoxID = ? AND fineID = ?");
    $existingFineQuery->bind_param("sss", $memberID, $issueBoxID, $fineID);
    $existingFineQuery->execute();
    $existingFineResult = $existingFineQuery->get_result();

    if ($existingFineResult->num_rows > 0) {
        $response['message'] = 'Fine already exists for this box and member.';
        echo json_encode($response);
        exit;
    }
    $existingFineQuery->close();

    // Insert the fine record
    $stmt = $conn->prepare("INSERT INTO boxfines (memberID, issueBoxID, fineID, amount, isPaid) VALUES (?, ?, ?, ?, 0)");
    
    if (false === $stmt) {
        $response['message'] = 'Prepare statement failed: ' . htmlspecialchars($conn->error);
        echo json_encode($response);
        exit;
    }
    
    $bind = $stmt->bind_param("sssd", $memberID, $issueBoxID, $fineID, $total);
    if (false === $bind) {
        $response['message'] = 'Bind param failed: ' . htmlspecialchars($stmt->error);
        echo json_encode($response);
        exit;
    }
    
    $exec = $stmt->execute();
    if (false === $exec) {
        $response['message'] = 'Execute failed: ' . htmlspecialchars($stmt->error);
        echo json_encode($response);
        exit;
    }
    
    $stmt->close();
}

$response['success'] = true;
$response['message'] = 'Fine has been issued successfully.';

$conn->close();

echo json_encode($response);
?>
