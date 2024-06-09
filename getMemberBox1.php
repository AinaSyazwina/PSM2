<?php
include 'config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$memberID = $input['memberID'] ?? null;

$response = [
    'success' => false,
    'message' => 'An error occurred.',
    'data' => []
];

if ($memberID) {
    try {
        // Updated SQL query to include bookISBN and copyCount for missing_books
        $stmt = $conn->prepare("SELECT bf.memberID, ib.BoxSerialNum, bf.amount, bf.datePaid, fb.type AS fineType,
                                       CASE WHEN fb.type = 'missing_books' THEN bf.bookISBN ELSE NULL END AS bookISBN,
                                       CASE WHEN fb.type = 'missing_books' THEN bf.copyCount ELSE NULL END AS copyCount
                                FROM boxfines bf
                                INNER JOIN issuebox ib ON bf.issueBoxID = ib.issueBoxID
                                LEFT JOIN finebox fb ON bf.fineID = fb.fineID
                                WHERE bf.memberID = ?");
        $stmt->bind_param("s", $memberID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $memberData = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($memberData as &$data) {
                $data['amount'] = number_format((float)$data['amount'], 2, '.', '');
            }
            $response['success'] = true;
            $response['message'] = 'Data found for this member ID.';
            $response['data'] = $memberData;
        } else {
            $response['message'] = 'No data found for this member ID.';
        }
        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Member ID is required.';
}

echo json_encode($response);
$conn->close();
?>
