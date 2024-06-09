<?php
include 'config.php';

$memberID = $_POST['memberID'] ?? null;
$BoxSerialNum = $_POST['BoxSerialNum'] ?? null;
$fineID = $_POST['fineID'] ?? null;
$total = $_POST['total'] ?? null;

$response = ['success' => false, 'message' => 'An error occurred.'];

$requiredAmounts = [
    "1" => 5.00,  // Return Late
    "3" => 15.00, // Missing
    "4" => 10.00  // Damage
];

if (!empty($memberID) && !empty($BoxSerialNum) && !empty($fineID) && !empty($total)) {
    if ($fineID == "5") { // Special case for missing_books
        $stmt = $conn->prepare("SELECT SUM(amount) as totalAmount FROM boxfines WHERE memberID = ? AND issueBoxID = (SELECT issueBoxID FROM issuebox WHERE BoxSerialNum = ? AND memberID = ?) AND fineID = ? AND isPaid = 0");
        $stmt->bind_param("sssi", $memberID, $BoxSerialNum, $memberID, $fineID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $calculatedTotal = $row['totalAmount'];

            if (floatval($total) != floatval($calculatedTotal)) {
                $response['message'] = 'Incorrect amount entered for the selected fine type. Expected: ' . $calculatedTotal;
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = 'Error fetching total amount for missing books.';
            echo json_encode($response);
            exit;
        }
    } else {
        if (floatval($total) != $requiredAmounts[$fineID]) {
            $response['message'] = 'Incorrect amount entered for the selected fine type. Expected: ' . $requiredAmounts[$fineID];
            echo json_encode($response);
            exit;
        }
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("SELECT issueBoxID FROM issuebox WHERE BoxSerialNum = ? AND memberID = ?");
        $stmt->bind_param("ss", $BoxSerialNum, $memberID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $issueBoxID = $row['issueBoxID'];

            if ($fineID == "5") { // Update all missing_books entries
                $updateStmt = $conn->prepare("UPDATE boxfines SET isPaid = 1, datePaid = NOW() WHERE memberID = ? AND issueBoxID = ? AND fineID = ? AND isPaid = 0");
                $updateStmt->bind_param("ssi", $memberID, $issueBoxID, $fineID);
            } else { // Update single entry
                $updateStmt = $conn->prepare("UPDATE boxfines SET isPaid = 1, datePaid = NOW(), amount = ? WHERE memberID = ? AND issueBoxID = ? AND fineID = ? AND isPaid = 0");
                $updateStmt->bind_param("disi", $total, $memberID, $issueBoxID, $fineID);
            }
            $updateStmt->execute();

            if ($updateStmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Payment processed successfully.';
                $conn->commit();  
            } else {
                $response['message'] = 'No update needed or fine already paid.';
                $conn->rollback();  
            }
        } else {
            $response['message'] = 'No matching record found.';
            $conn->rollback(); 
        }
    } catch (Exception $e) {
        $conn->rollback();  
        $response['message'] = 'Transaction failed: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'All fields are required.';
}

if (isset($stmt)) {
    $stmt->close();
}
if (isset($updateStmt)) {
    $updateStmt->close();
}

$conn->close();  

header('Content-Type: application/json');
echo json_encode($response);
?>
