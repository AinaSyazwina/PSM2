<?php
include 'navigaStu.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Resources</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 80%;
            margin: 180px auto 0 auto; /* 200px gap from the top, center horizontally */
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Study Resources</h2>
        <table id="resourcesTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Year/Grade</th>
                    <th>Book Title</th>
                    <th>Type</th>
                    <th>Format</th>
                    <th>Download Link</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Sample file data
                $files = [
                    "TextBook/Copy of BAHASA MELAYU T1 SK.pdf",
                    "TextBook/Copy of BAHASA MELAYU T2 SK - JLD2.pdf",
                    "TextBook/Copy of MATEMATIK T1 SK - JLD2 (1).pdf",
                    "TextBook/Copy of MATEMATIK T1 SK - JLD2.pdf",
                    "TextBook/Copy of MATHEMATICS Y2 - PART 1.pdf",
                    "TextBook/Copy of MATHEMATICS Y1 - PART 2.pdf",
                    "TextBook/Copy of SAINS SK T2.pdf",
                    "TextBook/Copy of SAINS T1 SK.pdf",
                    "TextBook/Copy of SCIENCE Y1.pdf"
                ];

                // Function to extract year/grade from the title
                function extractYearGrade($title) {
                    if (preg_match('/T(\d+)/i', $title, $matches) || preg_match('/Y(\d+)/i', $title, $matches)) {
                        return 'Year ' . $matches[1];
                    }
                    return 'Unknown';
                }

                // Function to extract the first two words from the title and remove unwanted parts
                function extractFirstTwoWords($title) {
                    $title = preg_replace('/\b(T\d+|Y\d+|SK|pdf|[-.])\b/', '', $title);
                    $words = preg_split('/\s+/', trim($title));
                    return implode(' ', array_slice($words, 0, 2));
                }

                // Function to sort books by year/grade and title
                function sortBooks($a, $b) {
                    $yearGradeA = extractYearGrade($a);
                    $yearGradeB = extractYearGrade($b);

                    // Extract year number for comparison
                    preg_match('/\d+/', $yearGradeA, $matchesA);
                    preg_match('/\d+/', $yearGradeB, $matchesB);

                    $yearA = isset($matchesA[0]) ? (int)$matchesA[0] : 0;
                    $yearB = isset($matchesB[0]) ? (int)$matchesB[0] : 0;

                    if ($yearA === $yearB) {
                        return strcmp($a, $b);
                    }
                    return $yearA - $yearB;
                }

                // Sort the files array
                usort($files, 'sortBooks');

                // Generate table rows dynamically
                $no = 1;
                foreach ($files as $file) {
                    $parts = explode('/', $file);
                    $filename = end($parts);
                    $title = str_replace('Copy of ', '', $filename);
                    $shortTitle = extractFirstTwoWords($title);
                    $yearGrade = extractYearGrade($title);
                    $type = "SK"; // Static type as example
                    $format = pathinfo($filename, PATHINFO_EXTENSION);
                    echo "<tr>
                            <td>$no</td>
                            <td>$yearGrade</td>
                            <td>$shortTitle</td>
                            <td>$type</td>
                            <td>$format</td>
                            <td><a href='$file'>Download</a></td>
                          </tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
