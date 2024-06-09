<?php
header('Content-Type: application/json');
include 'config.php'; // Ensure your database credentials are correct

$queryType = $_GET['queryType'] ?? 'unknown';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

$sql = "";

switch ($queryType) {
    case 'most_popular_box':
        $sql = "SELECT b.BoxSerialNum, COUNT(ib.BoxSerialNum) AS Count 
                FROM issuebox ib
                JOIN boxs b ON ib.BoxSerialNum = b.BoxSerialNum
                GROUP BY ib.BoxSerialNum
                ORDER BY Count DESC
                LIMIT 1";
        break;

    case 'recently_added_box':
        $sql = "SELECT BoxSerialNum FROM boxs WHERE DateCreate >= NOW() - INTERVAL 90 DAY";
        break;

    case 'boxs_by_category':
        $sql = "SELECT BoxSerialNum FROM boxs WHERE category LIKE '%$category%'";
        break;

    case 'highest_boxquantity':
        $sql = "SELECT BoxSerialNum, BookQuantity
                FROM boxs
                ORDER BY BookQuantity DESC
                LIMIT 1";
        break;

    case 'lowest_boxquantity':
        $sql = "SELECT BoxSerialNum, BookQuantity
                FROM boxs
                ORDER BY BookQuantity ASC
                LIMIT 1";
        break;

    default:
        echo json_encode(['error' => 'Invalid query type']);
        exit;
}

$result = $conn->query($sql);

if ($result) {
    $boxes = [];
    while ($row = $result->fetch_assoc()) {
        $boxes[] = $row;
    }
    
    switch ($queryType) {
        case 'most_popular_box':
            $messages = [];
            foreach ($boxes as $box) {
                $messages[] = "Box {$box['BoxSerialNum']} with {$box['Count']} issues.";
            }
            $message = "The most popular boxes are: " . implode(', ', $messages);
            break;

        case 'recently_added_box':
            $boxSerialNums = array_column($boxes, 'BoxSerialNum');
            $message = "The recently added boxes are: " . implode(', ', $boxSerialNums);
            break;

        case 'boxs_by_category':
            $boxSerialNums = array_column($boxes, 'BoxSerialNum');
            $message = "The boxes for category '$category' are: " . implode(', ', $boxSerialNums);
            break;

        case 'highest_boxquantity':
        case 'lowest_boxquantity':
            $box = current($boxes);
            $message = "The " . ($queryType === 'highest_boxquantity' ? "highest" : "lowest") . 
                       " book quantity is Box {$box['BoxSerialNum']} with a total of {$box['BookQuantity']} books.";
            break;

        default:
            $message = "Invalid request type.";
            break;
    }

    echo json_encode(['message' => $message, 'data' => $boxes]);
} else {
    echo json_encode(['error' => 'SQL error: ' . $conn->error]);
}

$conn->close();
?>
