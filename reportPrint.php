<?php
require_once('fpdf/fpdf.php');
include 'config.php';

$session = $_POST['session'] ?? '';
$month = $_POST['month'] ?? '';
$reportType = $_POST['reportType'] ?? '';
$category = $_POST['category'] ?? '';

if (empty($session) || empty($reportType) || empty($category)) {
    exit('Required fields are missing');
}

$month_parameter = $month === 'all' ? '%' : date('m', strtotime("1 " . $month . " 2000"));
$sql_condition = $month === 'all' ? "LIKE ?" : "= ?";

if ($category == 'Book' && $reportType == 'Borrow Return') {
    $sql = "SELECT 
                ib.issueID, 
                r.fullname AS fullname,   
                ib.bookID, 
                b.Title, 
                b.ISBN,
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
} elseif ($category == 'Book' && $reportType == 'Fine') {
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
            WHERE YEAR(ib.IssueDate) = ? AND (MONTH(ib.IssueDate) " . $sql_condition . " OR MONTH(ib.ReturnDate) " . $sql_condition . ")
            AND (ib.ReturnDate > ib.DueDate OR fb.type IN ('damage', 'kept'))
            ORDER BY r.fullName";
} elseif ($category == 'Box' && $reportType == 'Fine') {
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
            WHERE YEAR(ib.IssueDate) = ? AND (MONTH(ib.IssueDate) " . $sql_condition . " OR MONTH(ib.ReturnDate) " . $sql_condition . ")
            AND (ib.ReturnDate > ib.DueDate OR fb.type IN ('damage', 'kept'))
            ORDER BY r.fullName";
} else {
    exit('Invalid category or report type');
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    exit('Prepare failed: ' . $conn->error);
}

if ($month === 'all') {
    $stmt->bind_param("sss", $session, $month_parameter, $month_parameter);
} else {
    $stmt->bind_param("ssi", $session, $month_parameter, $month_parameter);
}

if (!$stmt->execute()) {
    exit('Execute failed: ' . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    exit('Get result failed: ' . $stmt->error);
}

$data = $result->fetch_all(MYSQLI_ASSOC);
if (!$data) {
    exit('No data available for the selected criteria.');
}

$stmt->close();
$conn->close();

$columns = [
    'Borrow Return, Book' => ['No', 'Name', 'Book ID', 'Title', 'Borrow Date', 'Due Date', 'Return Date', 'Status'],
    'Borrow Return, Box' => ['No', 'Name', 'Box Serial Number', 'Category', 'Borrow Date', 'Due Date', 'Return Date', 'Status'],
    'Fine, Book' => ['No', 'Name', 'Book ID', 'Title', 'Due Date', 'Return Date', 'Fine Category', 'Total', 'Status'],
    'Fine, Box' => ['No', 'Name', 'Box Serial Number', 'Category', 'Due Date', 'Return Date', 'Fine Category', 'Total', 'Status']
];

$column_widths = [
    'No' => 10,
    'Name' => 30,
    'Book ID' => 35,
    'Box Serial Number' => 35,
    'Category' => 30,
    'Title' => 70,
    'Borrow Date' => 30,
    'Due Date' => 25,
    'Return Date' => 25,
    'Status' => 25,
    'Fine Category' => 35, 
    'Total' => 25,
];

$key = $reportType . ', ' . $category;
$header_titles = $columns[$key] ?? [];

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

$logoPath = 'pic/logo (2).png'; // Ensure the path is correct
$logoWidth = 30;
$margin = 10;

$pdf->Image($logoPath, $margin, $margin, $logoWidth);
$pdf->SetFont('Arial', '', 12);
$pdf->SetXY($margin + $logoWidth + 5, $margin); 
$companyName = "SK Kamunting Library";
$companyAddress = "No. 1, Jalan Sekolah Rendah 1, 34600 Kamunting, Perak";
$pdf->MultiCell(0, 5, $companyName . "\n" . $companyAddress);

$pdf->SetFont('Arial', 'B', 16);
$pdf->Ln(20);  
$pdf->Cell(0, 10, 'INVOICE', 0, 1, 'C');
$pdf->Ln(5);

// Calculate the total width of the table
$totalWidth = 0;
foreach ($header_titles as $header) {
    $totalWidth += $column_widths[$header];
}

// Calculate the starting X position to center the table
$startX = ($pdf->GetPageWidth() - $totalWidth) / 2;

// Print headers
$pdf->SetX($startX);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(54, 95, 145); 
$pdf->SetTextColor(255);
foreach ($header_titles as $header) {
    $pdf->Cell($column_widths[$header], 10, $header, 1, 0, 'C', true);
}
$pdf->Ln();

// Print data rows
$pdf->SetFont('Arial', '', 12);
$pdf->SetFillColor(255); 
$pdf->SetTextColor(0); 
$no = 0;
foreach ($data as $row) {
    $pdf->SetX($startX); // Ensure each row starts at the calculated X position
    $pdf->Cell($column_widths['No'], 10, ++$no, 1, 0, 'C', true); // No column
    foreach ($header_titles as $header) {
        if ($header != 'No') {
            $cellValue = ''; // Initialize cell value
           
            // Determine the content of the cell based on the header
            if ($header == 'Status') {
                $cellValue = $reportType == 'Borrow Return' ? ($row['Status'] ?? '') : ($row['FineStatus'] ?? '');
            } else {
                // Use a match expression to map header titles to database fields
                $fieldName = match($header) {
                    'Name' => 'fullname',
                    'Book ID' => 'ISBN',
                    'Box Serial Number' => 'BoxSerialNum',
                    'Category' => 'category',
                    'Title' => 'Title',
                    'Borrow Date' => 'IssueDate',
                    'Due Date' => 'DueDate',
                    'Return Date' => 'ReturnDate',
                    'Fine Category' => 'FineType',
                    'Total' => 'FineAmount',
                    default => $header
                };
                $cellValue = $row[$fieldName] ?? ''; // Fetch the value using the field name, use null coalescing for default
            }
            // Render the cell with the obtained value and set dimensions and alignment
            $pdf->Cell($column_widths[$header], 10, $cellValue, 1, 0, 'C', true);
        }
    }
    $pdf->Ln(); // Move to the next line after finishing one row
}

ob_end_clean();
$pdf->Output('I', 'report.pdf');

?>
